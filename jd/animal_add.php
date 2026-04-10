<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ==========================
// DATABASE
// ==========================
$conn = new mysqli("localhost", "root", "", "live_stock");
if ($conn->connect_error) {
    die(json_encode(["status"=>"error","message"=>"DB connection failed"]));
}

// ==========================
// FILE PATH
// ==========================
$file = __DIR__ . "/latest_tag.txt";

// ==========================
// BREEDS
// ==========================
$animal_breeds = [
    "Cow" => ["Inyambo", "Friesian", "Holstein", "Local"],
    "Goat" => ["Boer", "Saanen", "Local"],
    "Sheep" => ["Dorper", "Merino", "Local"],
    "Chicken" => ["Broiler", "Layer", "Local"],
    "Pig" => ["Large White", "Landrace", "Local"]
];

// ==========================
// GET (AJAX)
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax'])) {

    header("Content-Type: application/json");

    if (isset($_GET['get_latest'])) {
        $tag = file_exists($file) ? trim(file_get_contents($file)) : "";
        echo json_encode(["tagId" => $tag]);
        exit;
    }

    if (isset($_GET['tagId'])) {
        $stmt = $conn->prepare("SELECT * FROM animals WHERE tagid = ?");
        $stmt->bind_param("s", $_GET['tagId']);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            echo json_encode($row);
        } else {
            echo json_encode(["status"=>"not_found"]);
        }
        exit;
    }
}

// ==========================
// POST
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header("Content-Type: application/json");

    $data = json_decode(file_get_contents("php://input"), true);

    // TAG CAPTURE
    if (isset($data['tagId']) && !isset($data['action'])) {
        file_put_contents($file, trim($data['tagId']));
        echo json_encode(["status"=>"Tag Captured"]);
        exit;
    }

    // REGISTER ANIMAL
    if (isset($data['action']) && $data['action'] === "register_animal") {

        $tagId = trim($data['tagId']);

        // CHECK DUPLICATE
        $check = $conn->prepare("SELECT tagid FROM animals WHERE tagid=?");
        $check->bind_param("s",$tagId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo json_encode(["status"=>"error","message"=>"Tag exists"]);
            exit;
        }

        // ==========================
        // 🔥 FIX 1: VALIDATION (IMPORTANT)
        // ==========================
        $name = trim($data['name'] ?? "");
        $animalType = trim($data['animalType'] ?? "");  // Form sends camelCase
        $sex = trim($data['sex'] ?? "");
        $breed = trim($data['breed'] ?? "");
        $birthdate = trim($data['birthdate'] ?? "");
        $ownerContact = trim($data['ownerContact'] ?? "");

        if (
            $tagId == "" || $name == "" || $animalType == "" ||
            $sex == "" || $breed == "" || $birthdate == "" ||
            $ownerContact == ""
        ) {
            echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
            exit;
        }

        $pregnancy = isset($data['pregnancy']) ? (int)$data['pregnancy'] : 0;
        $sickness = isset($data['sickness']) ? (int)$data['sickness'] : 0;

        // ==========================
        // INSERT (MATCHES YOUR TABLE)
        // ==========================
        $stmt = $conn->prepare("
            INSERT INTO animals 
            (tagid,name,animal_type,sex,breed,birthdate,pregnancy,sickness,ownercontact)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "sssssssii",
            $tagId,
            $name,
            $animalType,
            $sex,
            $breed,
            $birthdate,
            $pregnancy,
            $sickness,
            $ownerContact
        );

        if ($stmt->execute()) {
            file_put_contents($file, "");
            echo json_encode(["status"=>"success"]);
        } else {
            echo json_encode([
                "status"=>"error",
                "message"=>$stmt->error
            ]);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Animal Registration</title>

    <link rel="stylesheet" href="insert.css">

    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
        }
        form {
            width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
        }
        .form-field {
            margin-bottom: 10px;
        }
        label { display:block; }
        input, select {
            width:100%;
            padding:6px;
        }
        button {
            padding:10px;
            width:100%;
            background: green;
            color:white;
            border:none;
        }
    </style>
</head>

<body>

<h2 style="text-align:center;">Animal Registration</h2>

<form id="animalForm">

    <div class="form-field">
        <label>Tag ID</label>
        <input type="text" id="tagid" readonly>
    </div>

    <div class="form-field">
        <label>Name</label>
        <input type="text" id="name">
    </div>

    <div class="form-field">
        <label>Type</label>
        <select id="animal_type" onchange="updateBreed()">
            <option value="">Select</option>
            <?php foreach($animal_breeds as $type=>$b): ?>
                <option value="<?= $type ?>"><?= $type ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-field">
        <label>Breed</label>
        <select id="breed"></select>
    </div>

    <div class="form-field">
        <label>Sex</label>
        <select id="sex">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
    </div>

    <div class="form-field">
        <label>Birthdate</label>
        <input type="date" id="birthdate">
    </div>

    <div class="form-field">
        <label>Pregnancy</label>
        <select id="pregnancy">
            <option value="0">No</option>
            <option value="1">Yes</option>
        </select>
    </div>

    <div class="form-field">
        <label>Sickness</label>
        <select id="sickness">
            <option value="0">No</option>
            <option value="1">Yes</option>
        </select>
    </div>

    <div class="form-field">
        <label>Contact</label>
        <input type="tel" id="ownerContact">
    </div>

    <button type="submit">Save</button>

</form>

<script>
const animalBreeds = <?php echo json_encode($animal_breeds); ?>;

function updateBreed(){
    const type = document.getElementById('animal_type').value;
    const breed = document.getElementById('breed');
    breed.innerHTML = "";

    if(animalBreeds[type]){
        animalBreeds[type].forEach(b=>{
            let opt = document.createElement("option");
            opt.value = b;
            opt.textContent = b;
            breed.appendChild(opt);
        });
    }
}

setInterval(()=>{
    fetch('animal_add.php?get_latest=1&ajax=1&t=' + Date.now())
    .then(res=>res.json())
    .then(data=>{
        let input = document.getElementById("tagid");

        if(data.tagId && input.value !== data.tagId){
            input.value = data.tagId;
        }
    });
},1000);

document.getElementById("animalForm").addEventListener("submit", function(e){
    e.preventDefault();

    fetch('animal_add.php', {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            action: "register_animal",
            tagId: document.getElementById("tagid").value,
            name: document.getElementById("name").value,
            animalType: document.getElementById("animal_type").value,
            sex: document.getElementById("sex").value,
            breed: document.getElementById("breed").value,
            birthdate: document.getElementById("birthdate").value,
            ownerContact: document.getElementById("ownerContact").value,
            pregnancy: document.getElementById("pregnancy").value,
            sickness: document.getElementById("sickness").value
        })
    })
    .then(res=>res.json())
    .then(res=>{
        alert(res.status + (res.message ? " - " + res.message : ""));
    });
});
</script>

</body>
</html>