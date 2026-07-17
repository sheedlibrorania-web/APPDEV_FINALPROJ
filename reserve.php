<?php

session_start();

$conn = require("includes/db.php");
require("includes/functions.php");

if(!$conn){
    die("Connection Failed");
}

$isLoggedIn = isset($_SESSION["userID"]);

$error = "";
$quantityValue = 1;
$selectedTicketID = 0;

// Load all ticket types
$tickets = [];

$sql = "SELECT *
        FROM tickets
        ORDER BY ticketType ASC";

$result = mysqli_query($conn, $sql);

if($result){
    while($row = mysqli_fetch_assoc($result)){
        $row["sold"] = getSoldTickets($conn, $row["ticketID"]);
        $row["remaining"] = $row["maxTickets"] - $row["sold"];
        $tickets[] = $row;
    }
}

// Reservation process
if(isset($_POST["reserve"])){

    $selectedTicketID = isset($_POST["ticketID"]) ? (int)$_POST["ticketID"] : 0;
    $quantityValue = isset($_POST["quantity"]) ? (int)$_POST["quantity"] : 1;

    // Validation 1 - Ticket must be selected
    if($selectedTicketID <= 0){
        $error = "Please select a ticket type.";
    }

    // Validation 2 - Quantity cannot be empty / invalid
    if(empty($error)){
        if($quantityValue < 1){
            $error = "Quantity must be at least 1.";
        }
    }

    // Validation 3 - Quantity cannot exceed 4
    if(empty($error)){
        if($quantityValue > 4){
            $error = "Quantity cannot exceed 4 tickets per reservation.";
        }
    }

    // Validation 4 - Check if selected ticket exists
    if(empty($error)){

        $sql = "SELECT *
                FROM tickets
                WHERE ticketID = '$selectedTicketID'";

        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0){
            $ticket = mysqli_fetch_assoc($result);
        }
        else{
            $error = "Invalid ticket selected.";
        }
    }

    // Validation 5 - Quantity cannot exceed remaining tickets
    if(empty($error)){

        $sold = getSoldTickets($conn, $ticket["ticketID"]);
        $remaining = $ticket["maxTickets"] - $sold;

        if($quantityValue > $remaining){
            $error = "Not enough remaining tickets.";
        }
    }

    // Insert reservation
    if(empty($error)){

        $ticketNumber = generateUniqueTicketNumber($conn);

        $userID = (int)$_SESSION["userID"];
        $ticketID = (int)$ticket["ticketID"];
        $quantity = (int)$quantityValue;

        $sql = "INSERT INTO reservations
                (userID, ticketID, ticketNumber, quantity, status, reservationDate)

                VALUES

                ($userID, $ticketID, '$ticketNumber', $quantity, 'Pending', NOW())";

        $result = mysqli_query($conn, $sql);

        if($result){
            header("Location: myticket.php");
            exit();
        }
        else{
            $error = "Reservation failed.";
        }
    }
}

// If no ticket selected yet, default to the first ticket for display
$currentTicket = null;

if(!empty($tickets)){
    if($selectedTicketID > 0){
        foreach($tickets as $ticket){
            if((int)$ticket["ticketID"] === $selectedTicketID){
                $currentTicket = $ticket;
                break;
            }
        }
    }

    if($currentTicket === null){
        $currentTicket = $tickets[0];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TRC | Reserve Ticket</title>
</head>
<body>
<?php include("includes/navbar.php"); ?>
<div class="reserve-container">

    <h1>🎵 Reserve Your Concert Ticket</h1>

    <hr>

    <p>
        Reserve your ticket for <strong>The Row3 Concert</strong>.
        You may reserve a maximum of <strong>4 tickets</strong> per reservation.
    </p>

    <hr>

<?php

// USER NOT LOGGED IN
if(!$isLoggedIn){

?>

    <h2>Login Required</h2>

    <p>
        Please log in or create an account before reserving tickets.
    </p>

    <a href="login.php">
        <button>Login</button>
    </a>

    &nbsp;

    <a href="register.php">
        <button>Register</button>
    </a>

<?php

}

// USER LOGGED IN
else{

?>

    <?php

    if(!empty($error)){
        echo "<p style='color:red;'><strong>$error</strong></p>";
    }

    ?>

    <form action="" method="POST">

        <fieldset>

            <legend><strong>Reservation Details</strong></legend>

            <p>

                <strong>Ticket Type</strong>

            </p>

            <select name="ticketID" required>

                <option value="">-- Select Ticket --</option>

                <?php foreach($tickets as $ticket): ?>

                    <option
                        value="<?php echo $ticket["ticketID"]; ?>"
                        <?php echo ($selectedTicketID == $ticket["ticketID"]) ? "selected" : ""; ?>>

                        <?php echo htmlspecialchars($ticket["ticketType"]); ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <br><br>

            <?php if($currentTicket): ?>

                <table border="1" cellpadding="8" cellspacing="0">

                    <tr>

                        <th align="left">Price</th>

                        <td>

                            ₱<?php echo number_format($currentTicket["price"],2); ?>

                        </td>

                    </tr>

                    <tr>

                        <th align="left">Remaining Tickets</th>

                        <td>

                            <?php echo $currentTicket["remaining"]; ?>

                        </td>

                    </tr>

                    <tr>

                        <th align="left">Maximum Allowed</th>

                        <td>4 Tickets</td>

                    </tr>

                </table>

            <?php endif; ?>

            <br>

            <label>

                <strong>Quantity</strong>

            </label>

            <br>

            <input
                type="number"
                name="quantity"
                min="1"
                max="4"
                value="<?php echo htmlspecialchars($quantityValue); ?>"
                required>

            <br><br>

            <?php if($currentTicket): ?>

                <p>

                    <strong>Estimated Total:</strong>

                    ₱<?php

                    echo number_format(

                        $currentTicket["price"] * $quantityValue,

                        2

                    );

                    ?>

                </p>

            <?php endif; ?>

            <button
                type="submit"
                name="reserve">

                Reserve Ticket

            </button>

        </fieldset>

    </form>

<?php

}

?>

    <hr>

    <a href="dashboard.php">

        ← Back to Dashboard

    </a>

</div>

</body>
</html>