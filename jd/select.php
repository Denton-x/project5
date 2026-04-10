<?php
$conn = mysqli_connect("localhost", "root", "", "live_stock");

/* DELETE */
if($_SERVER['REQUEST_METHOD'] == "POST"){
    $data = json_decode(file_get_contents("php://input"), true);

    if($data['action'] == "delete"){
        $tagid = $data['tagid'];
        $sql = "DELETE FROM animals WHERE tagid='$tagid'";
        if(mysqli_query($conn,$sql)){
            echo "Deleted successfully";
        }else{
            echo "Error deleting";
        }
        exit;
    }
}

/* SELECT ALL */
$sql = "SELECT * FROM animals";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Animal's list</title>
    <link rel="stylesheet" href="insert.css">
</head>
<style>
    body { font-family: Arial; }
    table { width: 95%; margin: 20px auto; border-collapse: collapse; }
    th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: center; }
    th { background-color: #2c3e50; color: white; }
    tr:hover { background-color: #f2f2f2; }

    .action-btn {
        padding: 5px 10px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        color: white;
    }

    .edit-btn { background-color: #f39c12; }
    .delete-btn { background-color: #e74c3c; }

    .container { text-align: center; }
</style>
<body>

<h1>LIST OF ANIMALS</h1>

<!--  FIXED BACK BUTTON -->
<a href="animal_add.php"><button>Back</button></a>

<input type="text" id="search" placeholder="Search by name..." onkeyup="searchTable()">

<table id="animalTable" border="1">
<tr>
    <th>Tag ID</th>
    <th>Name</th>
    <th>Type</th>
    <th>Sex</th>
    <th>Breed</th>
    <th>Pregnant</th>
    <th>Sick</th>
    <th>Owner</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?php echo $row['tagid']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['animal_type']; ?></td>
    <td><?php echo $row['sex']; ?></td>
    <td><?php echo $row['breed']; ?></td>
    <td><?php echo $row['pregnancy'] ? "Yes" : "No"; ?></td>
    <td><?php echo $row['sickness'] ? "Yes" : "No"; ?></td>
    <td><?php echo $row['ownercontact']; ?></td>
    <td>
        <!--  FIXED: add quotes -->
        <button onclick="editAnimal('<?php echo $row['tagid']; ?>')">Edit</button>
        <button onclick="deleteAnimal('<?php echo $row['tagid']; ?>')">Delete</button>
    </td>
</tr>
<?php } ?>

</table>

<script>
function deleteAnimal(tagid){
    if(confirm("Are you sure you want to delete?")){
        fetch("select.php", {
            method:"POST",
            headers:{"Content-Type":"application/json"},
            body:JSON.stringify({action:"delete", tagid:tagid})
        })
        .then(res=>res.text())
        .then(res=>{
            alert(res);
            location.reload();
        });
    }
}

function editAnimal(tagid){
    window.location = "edit.php?tagid=" + tagid;
}

function searchTable() {
    let input = document.getElementById("search").value.toLowerCase();
    let rows = document.getElementById("animalTable").getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
        let name = rows[i].getElementsByTagName("td")[1];
        if (name) {
            let text = name.textContent.toLowerCase();
            rows[i].style.display = text.includes(input) ? "" : "none";
        }
    }
}
</script>

</body>
</html>