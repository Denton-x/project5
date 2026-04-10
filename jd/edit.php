<?php
$conn = mysqli_connect("localhost", "root", "", "live_stock");

$tagid = $_GET['tagid'];

// Fetch animal data
$sql = "SELECT * FROM animals WHERE tagid='$tagid'";
$result = mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($result);


// Handle POST update
if($_SERVER['REQUEST_METHOD']=="POST"){
    $data = json_decode(file_get_contents("php://input"), true);

    if(!$data){
        echo "No data received!";
        exit;
    }

    $sql = "UPDATE animals SET
        name='".$data['name']."',
        animal_type='".$data['animal_type']."',
        sex='".$data['sex']."',
        breed='".$data['breed']."',
        birthdate='".$data['birthdate']."',
        pregnancy='".$data['pregnancy']."',
        sickness='".$data['sickness']."',
        ownercontact='".$data['ownercontact']."'
        WHERE tagid='".$data['tagid']."'";

    if(mysqli_query($conn,$sql)){
        header("Location: select.php");
    }else{
        echo "Error: ".mysqli_error($conn);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Animal</title>
    <link rel="stylesheet" href="insert.css">
</head>
<body>

<h1>Edit Animal</h1>

<form id="animalForm" onsubmit="return false;">
    <!-- Hidden Tag ID -->
    <input type="hidden" id="tagid" value="<?php echo $row['tagid']; ?>">

    <div class="form-field">
        <label>Name:</label>
        <input type="text" id="name" value="<?php echo $row['name']; ?>">
    </div>

    <div class="form-field">
        <label>Animal Type:</label>
        <select id="animal_type" onchange="updateBreed()">
            <option value="">--Select--</option>
            <option value="Cow" <?php if($row['animal_type']=='Cow') echo 'selected'; ?>>Cow</option>
            <option value="Goat" <?php if($row['animal_type']=='Goat') echo 'selected'; ?>>Goat</option>
            <option value="Sheep" <?php if($row['animal_type']=='Sheep') echo 'selected'; ?>>Sheep</option>
            <option value="Other" <?php if($row['animal_type']=='Other') echo 'selected'; ?>>Other</option>
        </select>
    </div>

    <div class="form-field">
        <label>Breed:</label>
        <select id="breed">
            <option value="">--Select--</option>
            <!-- Cow breeds -->
            <option value="Inyambo" <?php if($row['breed']=='Inyambo') echo 'selected'; ?>>Inyambo</option>
            <option value="Flizon" <?php if($row['breed']=='Flizon') echo 'selected'; ?>>Flizon</option>
            <!-- Goat/Sheep breeds -->
            <option value="Local" <?php if($row['breed']=='Local') echo 'selected'; ?>>Local</option>
        </select>
    </div>

    <div class="form-field">
        <label>Sex:</label>
        <select id="sex">
            <option value="Male" <?php if($row['sex']=='Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if($row['sex']=='Female') echo 'selected'; ?>>Female</option>
        </select>
    </div>

    <div class="form-field">
        <label>Birthdate:</label>
        <input type="date" id="birthdate" value="<?php echo $row['birthdate']; ?>">
    </div>

    <div class="form-field">
        <label>Pregnancy:</label>
        <select id="pregnancy">
            <option value="0" <?php if($row['pregnancy']==0) echo "selected"; ?>>No</option>
            <option value="1" <?php if($row['pregnancy']==1) echo "selected"; ?>>Yes</option>
        </select>
    </div>

    <div class="form-field">
        <label>Sickness:</label>
        <select id="sickness">
            <option value="0" <?php if($row['sickness']==0) echo "selected"; ?>>No</option>
            <option value="1" <?php if($row['sickness']==1) echo "selected"; ?>>Yes</option>
        </select>
    </div>

    <div class="form-field">
        <label>Owner Contact:</label>
        <input type="text" id="ownercontact" value="<?php echo $row['ownercontact']; ?>">
    </div>

    <div class="buttons">
        <button type="button" name="save" onclick="updateAnimal()">Update</button>
    </div>
</form>

<script>
// Optional: dynamic breed based on animal type
function updateBreed() {
    const animalType = document.getElementById('animal_type').value;
    const breed = document.getElementById('breed');

    breed.innerHTML = '<option value="">--Select--</option>'; // reset

    if(animalType=='Cow'){
        breed.innerHTML += '<option value="Inyambo">Inyambo</option>';
        breed.innerHTML += '<option value="Flizon">Flizon</option>';
    } else if(animalType=='Goat' || animalType=='Sheep'){
        breed.innerHTML += '<option value="Local">Local</option>';
    } else {
        breed.innerHTML += '<option value="Other">Other</option>';
    }
}

// Update animal via POST fetch
function updateAnimal(){
    let data = {
        tagid: document.getElementById("tagid").value,
        name: document.getElementById("name").value,
        animal_type: document.getElementById("animal_type").value,
        sex: document.getElementById("sex").value,
        breed: document.getElementById("breed").value,
        birthdate: document.getElementById("birthdate").value,
        pregnancy: document.getElementById("pregnancy").value,
        sickness: document.getElementById("sickness").value,
        ownercontact: document.getElementById("ownercontact").value
    };

    fetch("edit.php", {
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body:JSON.stringify(data)
    })
    .then(res => res.text())
    .then(res => {
        alert(res);
        window.location="select.php";
    });
}
</script>

</body>
</html>