<?php
$conn = mysqli_connect("localhost", "root", "", "live_stock");

// get JSON data from ESP32
$data = json_decode(file_get_contents("php://input"), true);

$tagid = $data['tagid'];

$sql = "SELECT * FROM animals WHERE tagid='$tagid'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){

    $animal = mysqli_fetch_assoc($result);

    // latest health record
    $health_sql = "SELECT * FROM health_records 
                   WHERE tagid='$tagid' 
                   ORDER BY id DESC LIMIT 1";

    $health_result = mysqli_query($conn, $health_sql);
    $health = mysqli_fetch_assoc($health_result);

    $response = array(
        "name" => $animal['name'],
        "animal_type" => $animal['animal_type'],
        "pregnancy" => $animal['pregnancy'],
        "sickness" => $animal['sickness'],
        "ownercontact" => $animal['ownercontact'],
        "health_type" => $health ? $health['type'] : "none"
    );

    echo json_encode($response);

}else{
    echo json_encode(array("error" => "Tag not found"));
}
?>