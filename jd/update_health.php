<?php
$conn = mysqli_connect("localhost", "root", "", "live_stock");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";
$record = null;

/* =========================
   FETCH RECORD USING GET
   ========================= */
if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $result = mysqli_query($conn, "SELECT * FROM health_records WHERE id=$id");

    if ($result && mysqli_num_rows($result) > 0) {
        $record = mysqli_fetch_assoc($result);
    } else {
        die("Record not found");
    }
}

/* =========================
   UPDATE RECORD USING GET
   ========================= */
if (isset($_GET['update'])) {

    $id = intval($_GET['id']);

    // Prevent undefined index errors
    $tagid = $_GET['tagid'] ?? '';
    $type = $_GET['type'] ?? '';
    $startdate = $_GET['startdate'] ?? '';
    $enddate = $_GET['enddate'] ?? '';
    $nexteventdate = $_GET['nexteventdate'] ?? '';
    $note = $_GET['note'] ?? '';
    $vetname = $_GET['vetname'] ?? '';
    $vetcontact = $_GET['vetcontact'] ?? '';

    $sql = "UPDATE health_records SET
                tagid='$tagid',
                type='$type',
                startdate='$startdate',
                enddate='$enddate',
                nexteventdate='$nexteventdate',
                note='$note',
                vetname='$vetname',
                vetcontact='$vetcontact'
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: view_health.php");
        exit();

        // Reload updated data
        $result = mysqli_query($conn, "SELECT * FROM health_records WHERE id=$id");
        $record = mysqli_fetch_assoc($result);
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

/* =========================
   FETCH TAG IDs
   ========================= */
$animals = mysqli_query($conn, "SELECT tagid FROM animals");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Health Record</title>
    <link rel="stylesheet" href="insert.css">

</head>
<body>

<div class="container">
    <h2>Update Health Record</h2>

    <?php if($message != ""): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if($record): ?>
    <form method="GET">

        <!-- REQUIRED HIDDEN FIELDS -->
        <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
        <input type="hidden" name="update" value="1">

        <!-- Tag ID -->
        <div class="form-field">
            <label>Tag ID:</label>
            <select name="tagid" required>
                <?php while($row = mysqli_fetch_assoc($animals)): ?>
                    <option value="<?php echo $row['tagid']; ?>"
                        <?php if($row['tagid'] == $record['tagid']) echo "selected"; ?>>
                        <?php echo $row['tagid']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Type -->
        <div class="form-field">
            <label>Type:</label>
            <select name="type" required>
                <option value="vaccination" <?php if($record['type']=="vaccination") echo "selected"; ?>>Vaccination</option>
                <option value="disease" <?php if($record['type']=="disease") echo "selected"; ?>>Disease</option>
                <option value="pregnancy" <?php if($record['type']=="pregnancy") echo "selected"; ?>>Pregnancy</option>
            </select>
        </div>

        <!-- Dates -->
        <div class="form-field">
            <label>Start Date:</label>
            <input type="date" name="startdate" value="<?php echo $record['startdate']; ?>" required>
        </div>

        <div class="form-field">
            <label>End Date:</label>
            <input type="date" name="enddate" value="<?php echo $record['enddate']; ?>" required>
        </div>

        <div class="form-field">
            <label>Next Event:</label>
            <input type="datetime-local" name="nexteventdate"
                value="<?php echo date('Y-m-d\TH:i', strtotime($record['nexteventdate'])); ?>" required>
        </div>

        <!-- Other fields -->
        <div class="form-field">
            <label>Note:</label>
            <input type="text" name="note" value="<?php echo $record['note']; ?>">
        </div>

        <div class="form-field">
            <label>Vet Name:</label>
            <input type="text" name="vetname" value="<?php echo $record['vetname']; ?>">
        </div>

        <div class="form-field">
            <label>Vet Contact:</label>
            <input type="text" name="vetcontact" value="<?php echo $record['vetcontact']; ?>">
        </div>

        <!-- Submit -->
        <div class="form-field">
            <button type="submit">Update</button>
        </div>

    </form>
    <?php else: ?>
        <p>No record found.</p>
    <?php endif; ?>

</div>

</body>
</html>