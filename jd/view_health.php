<?php
$conn = mysqli_connect("localhost", "root", "", "live_stock");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* =========================
   DELETE RECORD (AJAX)
   ========================= */
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if(mysqli_query($conn, "DELETE FROM health_records WHERE id=$id")) {
        echo "Record deleted successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit();
}

/* =========================
   FETCH RECORDS (AJAX)
   ========================= */
if(isset($_GET['fetch']) && $_GET['fetch'] == 1) {

    $result = mysqli_query($conn, "SELECT * FROM health_records");

    if(!$result){
        echo json_encode(["error" => mysqli_error($conn)]);
        exit();
    }

    $data = array();
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Health Records</title>
<link rel="stylesheet" href="insert.css">
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
</head>

<body>

<div class="container">
    <h1>HEALTH RECORDS (AUTO AJAX)</h1>

    <a href="insert_health.php">
        <button>Add New Record</button>
    </a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tag ID</th>
                <th>Type</th>
                <th>Start</th>
                <th>End</th>
                <th>Next Event</th>
                <th>Note</th>
                <th>Vet</th>
                <th>Contact</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody id="healthBody">
            <!-- AJAX DATA -->
        </tbody>
    </table>
</div>

<script>

/* =========================
   FETCH DATA
   ========================= */
function fetchHealthRecords() {

    const xhr = new XMLHttpRequest();
    xhr.open("GET", "view_health.php?fetch=1", true);

    xhr.onload = function() {

        if (this.status === 200) {

            try {
                const data = JSON.parse(this.responseText);
                const tbody = document.getElementById("healthBody");

                tbody.innerHTML = "";

                data.forEach(row => {

                    const tr = document.createElement("tr");

                    tr.innerHTML = `
                        <td>${row.id}</td>
                        <td>${row.tagid ?? ''}</td>
                        <td>${row.type ?? ''}</td>
                        <td>${row.startdate ?? ''}</td>
                        <td>${row.enddate ?? ''}</td>
                        <td>${row.nexteventdate ?? ''}</td>
                        <td>${row.note ?? ''}</td>
                        <td>${row.vetname ?? ''}</td>
                        <td>${row.vetcontact ?? ''}</td>
                        <td>
                            <a class="action-btn edit-btn" href="update_health.php?id=${row.id}">Edit</a>
                            <a class="action-btn delete-btn" href="#" onclick="deleteRecord(${row.id})">Delete</a>
                        </td>
                    `;

                    tbody.appendChild(tr);
                });

            } catch (e) {
                console.error("JSON ERROR:", e);
                console.log(this.responseText);
            }
        }
    };

    xhr.send();
}

/* =========================
   DELETE RECORD
   ========================= */
function deleteRecord(id) {

    if (confirm("Are you sure you want to delete this record?")) {

        const xhr = new XMLHttpRequest();
        xhr.open("GET", "view_health.php?delete=" + id, true);

        xhr.onload = function() {
            if (this.status === 200) {
                alert(this.responseText);
                fetchHealthRecords(); // instant refresh
            }
        };

        xhr.send();
    }
}

/* =========================
   AUTO LOAD + AUTO REFRESH
   ========================= */
fetchHealthRecords();              // first load
setInterval(fetchHealthRecords, 5000); // every 5 seconds

</script>

</body>
</html>