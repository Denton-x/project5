<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body{
            text-align:center;
            color:black;
            background-color: green;
        }
    </style>
</head>
<body>
    <p><h1><u>WELL COME TO USER REGISTERATION FORM FOR ACCESSING MY SYSEM</u></h1></p>
    <form action="" method="post">
        <input type="text"  placeholder="Enter username" name="name"><br><br><br><br>
        <input type="password" placeholder="password" name="pass"><br><br><br><br><br>
        <button type="submit" name="add"> registernow</button><br>
        click <a href="login.php">here</a> to back to login form
    </form>
    <?php
    if(isset($_POST['add'])){
$conn=mysqli_connect("localhost","root","","live_stock");
$name=$_POST['name'];
$pas=$_POST['pass'];
$insert=mysqli_query($conn,"insert into users(username,password) values ('$name','$pas')");
if($insert){
    header('location:login.php');
}
else{
    echo"error in login";
}
    } 
    ?>
</body>
</html>