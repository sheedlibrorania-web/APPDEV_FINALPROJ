<?php

session_start();

$conn = require("includes/db.php");
require("includes/functions.php");

if(!$conn){
    die("Connection Failed");
}

/* --------------------------
   REGULAR TICKET
---------------------------*/

$sql = "SELECT *
        FROM tickets
        WHERE ticketType = 'Regular'";

$result = mysqli_query($conn, $sql);

$regular = mysqli_fetch_assoc($result);

$regularSold = getSoldTickets($conn, $regular["ticketID"]);

$regularRemaining = $regular["maxTickets"] - $regularSold;


/* --------------------------
   VIP TICKET
---------------------------*/

$sql = "SELECT *
        FROM tickets
        WHERE ticketType = 'VIP'";

$result = mysqli_query($conn, $sql);

$vip = mysqli_fetch_assoc($result);

$vipSold = getSoldTickets($conn, $vip["ticketID"]);

$vipRemaining = $vip["maxTickets"] - $vipSold;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <title>The Row3 Concert</title>

</head>

<body>

    <h1>The Row3 Concert</h1>

    <h3>October 18, 2026</h3>

    <h3>FEU TECH</h3>

    <hr>

    <h2>Regular Ticket</h2>

    <p>
        Price:
        ₱<?php echo $regular["price"]; ?>
    </p>

    <p>
        Remaining Tickets:
        <?php echo $regularRemaining; ?>
        /
        <?php echo $regular["maxTickets"]; ?>
    </p>

    <hr>

    <h2>VIP Ticket</h2>

    <p>
        Price:
        ₱<?php echo $vip["price"]; ?>
    </p>

    <p>
        Remaining Tickets:
        <?php echo $vipRemaining; ?>
        /
        <?php echo $vip["maxTickets"]; ?>
    </p>

    <hr>

    <p>
        Experience an unforgettable night of music,
        lights, and performances at
        <strong>The Row3 Concert.</strong>
    </p>

    <hr>

    <?php

    if(isset($_SESSION["userID"])){

        echo "<h3>Welcome back, " . $_SESSION["fullName"] . "!</h3>";

        echo '<a href="dashboard.php">
                <button>Go to Dashboard</button>
              </a>';

    }
    else{

        echo '<a href="login.php">
                <button>Login</button>
              </a>';

        echo "&nbsp;";

        echo '<a href="register.php">
                <button>Register</button>
              </a>';

    }

    ?>

</body>

</html>