#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <MFRC522.h>
#include <SPI.h>

// -------------------- RFID PINS --------------------
#define SS_PIN 21
#define RST_PIN 22

// -------------------- OLED PINS --------------------
#define OLED_SDA 4
#define OLED_SCL 15

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1

// -------------------- BUTTON + OUTPUT --------------------
#define BUTTON_PIN 33
#define GREEN_LED 25
#define RED_LED 27
#define BUZZER_PIN 17

// -------------------- WIFI --------------------
const char* ssid ="ft";
const char* password ="1234567890";

// -------------------- SERVER --------------------
const char* serverUrl = "http://192.168.137.1/jd/animal_add.php";

// -------------------- DEVICES --------------------
MFRC522 rfid(SS_PIN, RST_PIN);
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// -------------------- MODE --------------------
enum DeviceMode { MODE_NORMAL, MODE_REGISTER };
DeviceMode currentMode = MODE_NORMAL;

// -------------------- TIMERS --------------------
unsigned long lastScanMs = 0;
unsigned long lastButtonMs = 0;
const unsigned long scanDebounceMs = 1500;
const unsigned long buttonDebounceMs = 250;

// ===================== SETUP =====================
void setup() {
    Serial.begin(115200);

    pinMode(BUTTON_PIN, INPUT_PULLUP);
    pinMode(GREEN_LED, OUTPUT);
    pinMode(RED_LED, OUTPUT);
    pinMode(BUZZER_PIN, OUTPUT);

    digitalWrite(GREEN_LED, LOW);
    digitalWrite(RED_LED, LOW);
    digitalWrite(BUZZER_PIN, LOW);

    // ---------------- OLED INIT ----------------
    Wire.begin(OLED_SDA, OLED_SCL);   // IMPORTANT FIX

    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println("OLED FAILED");
        while (true);
    }

    display.clearDisplay();
    display.setTextSize(1);
    display.setTextColor(WHITE);
    display.setCursor(0, 0);
    display.println("ESP32 RFID System");
    display.println("Starting...");
    display.display();
    delay(1500);

    // ---------------- RFID INIT ----------------
    SPI.begin();
    rfid.PCD_Init();
    Serial.println("RFID READY");

    // ---------------- WIFI ----------------
    connectWiFi();

    showMode();
}

// ===================== LOOP =====================
void loop() {
    handleButton();
    handleScan();
}

// ===================== WIFI =====================
void connectWiFi() {
    displayMessage("WiFi", "Connecting...", "", "");
    WiFi.begin(ssid, password);

    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) {
        delay(500);
        attempts++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        displayMessage("WiFi Connected", WiFi.localIP().toString(), "", "");
    } else {
        displayMessage("WiFi Failed", "Offline Mode", "", "");
    }

    delay(1200);
}

// ===================== BUTTON =====================
void handleButton() {
    if (millis() - lastButtonMs < buttonDebounceMs) return;

    if (digitalRead(BUTTON_PIN) == LOW) {
        lastButtonMs = millis();

        currentMode = (currentMode == MODE_NORMAL) ? MODE_REGISTER : MODE_NORMAL;

        beepShort();
        showMode();
        delay(300);
    }
}

// ===================== RFID SCAN =====================
void handleScan() {
    if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) return;

    if (millis() - lastScanMs < scanDebounceMs) {
        rfid.PICC_HaltA();
        return;
    }

    lastScanMs = millis();

    String tagId = readTagId();
    Serial.println("TAG: " + tagId);

    if (currentMode == MODE_REGISTER) {
        registerTag(tagId);
    } else {
        queryAnimal(tagId);
    }

    rfid.PICC_HaltA();
}

// ===================== READ TAG =====================
String readTagId() {
    String id = "";

    for (byte i = 0; i < rfid.uid.size; i++) {
        if (rfid.uid.uidByte[i] < 0x10) id += "0";
        id += String(rfid.uid.uidByte[i], HEX);
    }

    id.toUpperCase();
    return id;
}

// ===================== QUERY =====================
void queryAnimal(String tagId) {
    displayMessage("Scanning", tagId, "", "");

    if (WiFi.status() != WL_CONNECTED) {
        displayMessage("No WiFi", tagId, "", "");
        beepError();
        return;
    }

    HTTPClient http;
    String url = String(serverUrl) + "?ajax=1&tagId=" + tagId;

    http.begin(url);
    int code = http.GET();

    if (code > 0) {
        String res = http.getString();
        processAnimalResponse(res, tagId);
    } else {
        displayMessage("Server Error", "", "", "");
        beepError();
    }

    http.end();
}

// ===================== RESPONSE =====================
void processAnimalResponse(String payload, String tagId) {
    DynamicJsonDocument doc(1024);

    if (deserializeJson(doc, payload)) {
        displayMessage("JSON Error", tagId, "", "");
        beepError();
        return;
    }

    if (doc["status"] == "not_found") {
        displayMessage("NOT FOUND", tagId, "Register?", "");
        flashLed(RED_LED, 3, 150);
        beepError();
        return;
    }

    String name = doc["name"] | "Unknown";
    String owner = doc["ownercontact"] | "No contact";
    int sick = doc["sickness"] | 0;
    int preg = doc["pregnancy"] | 0;

    if (sick == 1) {
        displayMessage("SICK ANIMAL", name, owner, "");
        flashLed(RED_LED, 3, 120);
        beepError();
    } else {
        String extra = (preg == 1) ? "Pregnant" : "Healthy";
        displayMessage("OK", name, extra, owner);
        flashLed(GREEN_LED, 2, 120);
        beepShort();
    }
}

// ===================== REGISTER =====================
void registerTag(String tagId) {
    displayMessage("REGISTER", tagId, "Sending...", "");

    if (WiFi.status() != WL_CONNECTED) {
        displayMessage("No WiFi", "", "", "");
        beepError();
        return;
    }

    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");

    DynamicJsonDocument doc(128);
    doc["tagId"] = tagId;

    String body;
    serializeJson(doc, body);

    int code = http.POST(body);
    String res = http.getString();

    http.end();

    if (code > 0) {
        displayMessage("Sent", tagId, res, "");
        flashLed(GREEN_LED, 3, 100);
        beepSuccess();
    } else {
        displayMessage("FAILED", "", "", "");
        beepError();
    }
}

// ===================== DISPLAY =====================
void displayMessage(String a, String b, String c, String d) {
    display.clearDisplay();
    display.setCursor(0, 0);
    display.setTextSize(1);

    display.println(a);
    if (b != "") display.println(b);
    if (c != "") display.println(c);
    if (d != "") display.println(d);

    display.display();
}

void showMode() {
    String mode = (currentMode == MODE_REGISTER) ? "REGISTER MODE" : "NORMAL MODE";
    displayMessage(mode, "Scan RFID tag", "", "");
}

// ===================== OUTPUTS =====================
void flashLed(int pin, int times, int d) {
    for (int i = 0; i < times; i++) {
        digitalWrite(pin, HIGH);
        delay(d);
        digitalWrite(pin, LOW);
        delay(d);
    }
}

void beepShort() {
    tone(BUZZER_PIN, 2000, 100);
    delay(120);
}

void beepSuccess() {
    tone(BUZZER_PIN, 1500, 120);
    delay(150);
    tone(BUZZER_PIN, 2200, 120);
    delay(150);
}

void beepError() {
    tone(BUZZER_PIN, 1000, 120);
    delay(150);
    tone(BUZZER_PIN, 700, 120);
    delay(150);
}
