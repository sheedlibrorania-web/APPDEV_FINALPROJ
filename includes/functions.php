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

?>