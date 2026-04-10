<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "live_stock");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['username'] = $username;
        header("Location: livestock.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="insert.css">
    <style>
        body{
            font-family: Arial;
            text-align:center;
            margin-top:100px;
        }
        input, button{
            padding:10px;
            width:250px;
            margin:10px;
        }
        .error{
            color:red;
        }
    </style>
</head>
<body>

    <h1><b><i>LOG INTO LIVESTOCK MANAGEMENT SYSTEM</i></b></h1>

    <?php if($error != "") echo "<p class='error'>$error</p>"; ?>

    <form method="POST" action="">
        <p>
            <input type="text" name="username" placeholder="Enter Username" required>
        </p>
        <p>
            <input type="password" name="password" placeholder="Enter Password" required>
        </p>
        <p>
            <button type="submit">Login</button>
        </p>
    </form>

</body>
</html>