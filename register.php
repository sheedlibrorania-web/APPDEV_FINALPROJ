<?php

$conn = require("includes/db.php");

if(!$conn){
    die("Connection Failed");
}
// ----------------------------------------
if(isset($_POST["register"])){

    $username = trim($_POST["username"]); // trim removes white spaces
    $fullName = trim($_POST["fullName"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];
    
    
    // Validation 1 - empty fields
    if( 
        empty($username) ||
        empty($fullName) ||
        empty($email) ||
        empty($password) ||
        empty($confirmPassword)
    ){
        $error = "Please fill in all fields.";
    }

    // Validation 2 - password must match
    if(empty($error)){ 
        if($password != $confirmPassword){
            $error = "Passwords do not match.";
        }
    }

    // Validation 3 - password must be at least 8 characters long
    if(empty($error)){
        if(strlen($password) < 8){
            $error = "Password must be at least 8 characters long.";
        }
    }

    // Validation 4 - duplicate username
    if(empty($error)){
        $sql = "SELECT *
                FROM users
                WHERE username = '$username'";

        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0){
            $error = "Username already exists.";
        }
    }

    // Validation 5 - duplicate email
    if(empty($error)){
        $sql = "SELECT *
                FROM users
                WHERE email = '$email'";

        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0){
            $error = "Email already exists.";
        }
    }   


    // error message
    if(!empty($error)){
    echo "<p style='color:red;'>$error</p>";
    }
    

    // Insert User
    if(empty($error)){

        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users
                (username, fullName, email, password)
                VALUES
                ('$username','$fullName','$email','$password')";

        $result = mysqli_query($conn, $sql);

        if($result){

            header("Location: login.php");
            exit;

        }
        else{

            $error = "Registration Failed.";

        }

    }
}

?>





<!-- --------------------------------------------------------- -->
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>TRC | Register</title>

</head>

<body>
    <h1>Create Account</h1>

    <form action="" method="POST">

        <label>Username</label><br>
        <input type="text" name="username"><br><br>

        <label>Full Name</label><br>
        <input type="text" name="fullName"><br><br>

        <label>Email</label><br>
        <input type="email" name="email"><br><br>

        <label>Password</label><br>
        <input type="password" name="password"><br><br>

        <label>Confirm Password</label><br>
        <input type="password" name="confirmPassword"><br><br>

        <button type="submit" name="register">
            Register
        </button>

    </form>

</body>

</html>