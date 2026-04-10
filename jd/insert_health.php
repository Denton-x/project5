<?php
$conn = mysqli_connect("localhost", "root", "", "live_stock");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";

// Fetch tag IDs from animals table
$animalResult = mysqli_query($conn, "SELECT tagid FROM animals");

// Handle form submission
if (isset($_POST['submit'])) {

    $tagid = $_POST['tagid'] ?? null;
    $type = $_POST['type'] ?? null;
    $startdate = $_POST['startdate'] ?? null;
    $enddate = $_POST['enddate'] ?? null;
    $nexteventdate = $_POST['nexteventdate'] ?? null;
    $note = $_POST['note'] ?? '';
    $vetname = $_POST['vetname'] ?? '';
    $vetcontact = $_POST['vetcontact'] ?? '';

    $sql = "INSERT INTO health_records 
            (tagid, type, startdate, enddate, nexteventdate, note, vetname, vetcontact)
            VALUES ('$tagid', '$type', '$startdate', '$enddate', '$nexteventdate', '$note', '$vetname', '$vetcontact')";

    if (mysqli_query($conn, $sql)) {
        $message = "Record inserted successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Records Insertion</title>
    <link rel="stylesheet" href="insert.css">
</head>
<body>

<div class="container">
    <h1>HEALTH RECORD INSERTION SYSTEM</h1>

    <?php if($message != ""): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form id="animalForm" method="POST">

        <div class="form-field">
            <label for="tagid">Tag ID:</label>
            <select id="tagid" name="tagid" required>
                <option value="">-- Select Tag ID --</option>
                <?php while($row = mysqli_fetch_assoc($animalResult)): ?>
                    <option value="<?php echo $row['tagid']; ?>"><?php echo $row['tagid']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-field">
            <label for="type">Type:</label>
            <select id="type" name="type" required>
                <option value="">-- Select Type --</option>
                <option value="vaccination">Vaccination</option>
                <option value="disease">Disease</option>
                <option value="pregnancy">Pregnancy</option>
                <!-- Add more types here if needed -->
            </select>
        </div>

        <div class="form-field">
            <label for="startdate">Start Date:</label>
            <input type="date" id="startdate" name="startdate" required>
        </div>

        <div class="form-field">
            <label for="enddate">End Date:</label>
            <input type="date" id="enddate" name="enddate" required>
        </div>

        <div class="form-field">
            <label for="nexteventdate">Next Event:</label>
            <input type="datetime-local" id="nexteventdate" name="nexteventdate" required>
        </div>

        <div class="form-field">
            <label for="note">Note:</label>
            <input type="text" id="note" name="note">
        </div>

        <div class="form-field">
            <label for="vetname">Vet Name:</label>
            <input type="text" id="vetname" name="vetname">
        </div>

        <div class="form-field">
            <label for="vetcontact">Vet Contact:</label>
            <input type="text" id="vetcontact" name="vetcontact">
        </div>

        <div class="buttons">
            <button type="submit" name="submit">Save</button>
        </div>

    </form>
</div>

</body>
</html>