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
$showConfirmation = false;
$paymentMethod = "";

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
if(isset($_POST["continue"])){
    $selectedTicketID = isset($_POST["ticketID"]) ? (int)$_POST["ticketID"] : 0;
    $quantityValue = isset($_POST["quantity"]) ? (int)$_POST["quantity"] : 1;
    $paymentMethod = isset($_POST["paymentMethod"]) ? trim($_POST["paymentMethod"]) : "";

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

    // Validation 6 - Payment method is required
    if(empty($error)){

        if(empty($paymentMethod)){
            $error = "Please select a payment method.";
        }

    }
    // Show confirmation page
    if(empty($error)){
        $showConfirmation = true;
    }
}

if(isset($_POST["confirm"])){

        $selectedTicketID = (int)$_POST["ticketID"];
        $quantityValue = (int)$_POST["quantity"];
        $paymentMethod = $_POST["paymentMethod"];

        // Get the selected ticket again
        $sql = "SELECT *
                FROM tickets
                WHERE ticketID = '$selectedTicketID'";

        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0){

            $ticket = mysqli_fetch_assoc($result);

            $ticketNumber = generateUniqueTicketNumber($conn);

            $userID = (int)$_SESSION["userID"];
            $ticketID = (int)$ticket["ticketID"];
            $quantity = (int)$quantityValue;

            $sql = "INSERT INTO reservations
                    (userID,
                    ticketID,
                    ticketNumber,
                    quantity,
                    paymentMethod,
                    status,
                    reservationDate)

                    VALUES

                    ($userID,
                    $ticketID,
                    '$ticketNumber',
                    $quantity,
                    '$paymentMethod',
                    'Unpaid',
                    NOW())";

            $result = mysqli_query($conn,$sql);

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

    <!-- SHOWS FORM PAGE -->
    <?php
    if(!$showConfirmation){
    ?>
    <form action="" method="POST" enctype="multipart/form-data">

        <fieldset>

            <legend><strong>Reservation Details</strong></legend>

            <p>

                <strong>Ticket Type</strong>

            </p>

            <select name="ticketID" required>

                <option value="">-- Select Ticket --</option>

                <?php foreach($tickets as $ticketRow): ?>

                    <option
                        value="<?php echo $ticketRow["ticketID"]; ?>"
                        <?php echo ($selectedTicketID == $ticketRow["ticketID"]) ? "selected" : ""; ?>>

                        <?php echo htmlspecialchars($ticketRow["ticketType"]); ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <br><br>

            <?php if($currentTicket): ?>

                <table border="1" cellpadding="8" cellspacing="0">

                    <tr>

                        <th>Ticket Type</th>

                        <th>Price</th>

                        <th>Remaining</th>

                    </tr>

                    <?php foreach($tickets as $ticketRow): ?>

                    <tr>

                        <td>

                            <?php echo htmlspecialchars($ticketRow["ticketType"]); ?>

                        </td>

                        <td>

                            ₱<?php echo number_format($ticketRow["price"],2); ?>

                        </td>

                        <td>

                            <?php echo $ticketRow["remaining"]; ?>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                </table>

            <?php endif; ?>

            <br>

            <label>

                <strong>Quantity</strong>

            </label>
                <input
                    type="number"
                    name="quantity"
                    min="1"
                    max="4"
                    value="<?php echo htmlspecialchars($quantityValue); ?>"
                required>
            <br>
            <br>

                <label>

                    <strong>Payment Method</strong>

                </label>

                <br>

                <select name="paymentMethod" required>

                    <option value="">-- Select Payment Method --</option>

                    <option value="GCash">GCash</option>

                    <option value="Maya">Maya</option>

                    <option value="Bank Transfer">Bank Transfer</option>

                    <option value="Cash">Cash</option>

                </select>
            

            <br><br>
            <br>

            <label>

                <strong>Proof of Payment</strong>

            </label>

            <br>

            <input
                type="file"
                name="proofOfPayment"
                accept=".jpg,.jpeg,.png,.webp">

            <p>

                <small>
                Optional.
                Cash payments do not require proof.
                Online payments may also upload proof later in "My Ticket."
                </small>

            </p>

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
                name="continue">

                Review Reservation

            </button>

        </fieldset>

    </form>

    <?php
    }
    else{
    ?>
       <h2>🎵 Confirm Your Reservation</h2>

        <hr>

        <p>
        Please review your reservation before confirming.
        </p>

        <br>

        <p>

        <strong>Ticket Type:</strong>

        <?php echo htmlspecialchars($ticket["ticketType"]); ?>

        </p>

        <p>

        <strong>Quantity:</strong>

        <?php echo $quantityValue; ?>

        </p>

        <p>

        <strong>Price Per Ticket:</strong>

        ₱<?php echo number_format($ticket["price"],2); ?>

        </p>

        <p>

        <strong>Total Amount:</strong>

        ₱<?php echo number_format($ticket["price"] * $quantityValue,2); ?>

        </p>

        <p>

        <strong>Payment Method:</strong>

        <?php echo htmlspecialchars($paymentMethod); ?>

        </p>

        <?php

        if(!empty($_FILES["proofOfPayment"]["name"])){

        ?>

        <p>

        <strong>Proof of Payment:</strong>

        <?php echo $_FILES["proofOfPayment"]["name"]; ?>

        </p>

        <?php

        }
        else{

        ?>

        <p>

        <strong>Proof of Payment:</strong>

        None Uploaded

        </p>

        <?php

        }

        ?>

        <hr>

        <p>

        Once you confirm, your reservation will be saved and its status will be set to
        <strong>UNPAID</strong> until an administrator verifies your payment.

        </p>

        <br>

        <form action="" method="POST">

            <input
                type="hidden"
                name="ticketID"
                value="<?php echo $selectedTicketID; ?>">

            <input
                type="hidden"
                name="quantity"
                value="<?php echo $quantityValue; ?>">

            <input
                type="hidden"
                name="paymentMethod"
                value="<?php echo $paymentMethod; ?>">

            <button
                type="submit"
                name="confirm">

                Confirm Reservation

            </button>

            &nbsp;

            <button
                type="submit"
                name="cancel">

                Cancel

            </button>

        </form> 
    <?php
    }
    ?>


    <?php

}
if(isset($_POST["cancel"])){

    $showConfirmation = false;

}
?>


    <hr>

    <a href="dashboard.php">

        ← Back to Dashboard

    </a>

</div>

</body>
</html>