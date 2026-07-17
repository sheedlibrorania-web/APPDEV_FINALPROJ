<?php

function getSoldTickets($conn, $ticketID){

    $sql = "SELECT SUM(quantity) AS sold
            FROM reservations
            WHERE ticketID = '$ticketID'";

    $result = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($result);

    $sold = $row["sold"];

    if($sold == NULL){
        $sold = 0;
    }

    return $sold;

}


function generateUniqueTicketNumber($conn){

    do{
        $ticketNumber = "TRC" . random_int(1000, 9999);

        $sql = "SELECT ticketNumber
                FROM reservations
                WHERE ticketNumber = '$ticketNumber'";

        $result = mysqli_query($conn, $sql);

    }while($result && mysqli_num_rows($result) > 0);

    return $ticketNumber;
}

?>

