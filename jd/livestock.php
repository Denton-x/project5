<?php
session_start();

/* LOGIN PROTECTION */
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

/* LOGOUT LOGIC */
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Livestock Management System</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial;
}

body{
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:250px;
    height:100vh;
    background:#2c3e50;
    color:white;
    position:fixed;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}

.menu{
    padding-top:20px;
}

.sidebar h2{
    text-align:center;
    margin-bottom:20px;
}

.sidebar a{
    display:block;
    padding:15px;
    color:white;
    text-decoration:none;
    border-bottom:1px solid #34495e;
}

.sidebar a:hover{
    background:#34495e;
}

/* FOOTER */
.footer{
    text-align:center;
    padding:15px;
    border-top:1px solid #34495e;
}

/* CONTENT */
.content{
    margin-left:250px;
    width:100%;
    height:100vh;
    position:relative;
}

iframe{
    width:100%;
    height:100vh;
    border:none;
}

/* LOGOUT BUTTON */
.logout-btn{
    position:absolute;
    bottom:20px;
    right:20px;
    background:#e74c3c;
    color:white;
    padding:10px 18px;
    text-decoration:none;
    border-radius:5px;
    font-weight:bold;
    z-index:1000;
}

.logout-btn:hover{
    background:#c0392b;
}
</style>
</head>

<body>

<div class="sidebar">
    <div class="menu">
        <h2>Livestock</h2>

        <a href="livestock.php" target="_self">Home</a>
        <a href="animal_add.php" target="frame">Add Animal</a>
        <a href="select.php" target="frame">Animal List</a>
        <a href="insert_health.php" target="frame">Add Health</a>
        <a href="view_health.php" target="frame">View Health</a>
    </div>

    <div class="footer">
        <p>Made by : .Juvens_d & .Mukyo99_</p>
    </div>
</div>

<div class="content">

    <!-- Default Home Message -->
    <iframe name="frame" srcdoc='
        <html>
        <body style="font-family:Arial; text-align:center; padding-top:120px;">
            <h1 style="color:#2c3e50;">WELCOME TO LIVESTOCK MANAGEMENT SYSTEM</h1>
            <p style="font-size:18px; color:#555;">
                Manage animal registration, health records, and livestock efficiently.
            </p>
        </body>
        </html>
    '></iframe>

    <a href="livestock.php?logout=true" class="logout-btn">Logout</a>
</div>

</body>
</html>