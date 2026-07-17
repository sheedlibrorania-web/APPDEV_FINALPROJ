<?php

session_start();

$form = "login";

if(isset($_GET["form"])){

    $form = $_GET["form"];

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>The Row3 Concert</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="container">

    <div class="left">

        <h1>The Row3 Concert</h1>

        <p class="description">

            Experience an unforgettable night of music with your favorite artists.
            Reserve tickets online, manage your reservations,
            and enjoy a fast and hassle-free admission process.

        </p>

        <h3>Concert Information</h3>

        <ul>

            <li>🎵 Live Performances</li>

            <li>⭐ VIP & Regular Tickets</li>

            <li>📅 October 25, 2026</li>

            <li>📍 SMX Convention Center</li>

        </ul>

    </div>





    <div class="right">

<?php

if($form == "login"){

?>

        <h2>Login</h2>

        <form action="login.php" method="POST">

            <input type="text"
                   name="username"
                   placeholder="Username"
                   required>

            <input type="password"
                   name="password"
                   placeholder="Password"
                   required>

            <button type="submit"
                    name="login">

                Login

            </button>

        </form>

        <p>

            Don't have an account?

            <a href="index.php?form=register">

                Register

            </a>

        </p>

<?php

}

else{

?>

        <h2>Create Account</h2>

        <form action="register.php" method="POST">

            <input type="text"
                   name="fullname"
                   placeholder="Full Name"
                   required>

            <input type="text"
                   name="username"
                   placeholder="Username"
                   required>

            <input type="email"
                   name="email"
                   placeholder="Email"
                   required>

            <input type="password"
                   name="password"
                   placeholder="Password"
                   required>

            <input type="password"
                   name="confirmPassword"
                   placeholder="Confirm Password"
                   required>

            <button type="submit"
                    name="register">

                Register

            </button>

        </form>

        <p>

            Already have an account?

            <a href="index.php?form=login">

                Login

            </a>

        </p>

<?php

}

?>

    </div>

</div>

</body>

</html>