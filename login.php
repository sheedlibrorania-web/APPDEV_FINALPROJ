<?php
session_start();

$conn = require("includes/db.php");

if(!$conn){
    die("Connection Failed");
}


if(isset($_POST["login"])){

    // Read form data
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Validation 1 - Empty fields
    if(empty($username) || empty($password)){
        $error = "Please fill in all fields.";
    }

    // Validation 2 - Check if username exists
    if(empty($error)){

        $sql = "SELECT *
                FROM users
                WHERE username = '$username'";

        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0){

            $user = mysqli_fetch_assoc($result);

            // Validation 3 - Check password
            if(password_verify($password, $user["password"])){

                // Save user data in session
                $_SESSION["userID"] = $user["userID"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["fullName"] = $user["fullName"];
                $_SESSION["role"] = $user["role"];

                // Redirect based on role
                if($user["role"] == "Admin"){
                    header("Location: admin/dashboard.php");
                    exit;
                }
                else{
                    header("Location: dashboard.php");
                    exit;
                }

            }
            else{
                $error = "Invalid password.";
            }

        }
        else{
            $error = "Username not found.";
        }

    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TRC | Login</title>
</head>
<body>
    <h1>Login</h1>

    <?php
    if(!empty($error)){
        echo "<p style='color:red;'>$error</p>";
    }
    ?>

    <form action="" method="POST">
        <label>Username</label><br>
        <input type="text" name="username"><br><br>

        <label>Password</label><br>
        <input type="password" name="password"><br><br>

        <button type="submit" name="login">Login</button>
    </form>

    <br>

    Don't have an account? <a href="register.php">Register</a>

</body>
</html>