<?php
/**
 * myticket.php - RASHEED
 * -----------------------------------------------------------------
 * Displays every reservation belonging to the currently logged-in
 * user, with a running total ticket count and a proof-of-payment
 * upload per reservation.
 *
 * DB note: this page assumes the `reservations` table also has:
 *   - proofOfPayment  VARCHAR(255) NULL
 *   - checkedInDate   DATETIME NULL
 * (see sql/schema_additions.sql — needed because admin/reservations.php
 * and this page both read/write proof of payment + check-in date,
 * but they weren't in the original table draft.)
 * -----------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

requireLogin();

$userID = $_SESSION['userID'];
$uploadError = "";
$uploadSuccess = "";

/**
 * Handle proof-of-payment upload.
 * A reservation only belongs to this page's user if we double-check
 * both reservationID AND userID in the UPDATE's WHERE clause.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_proof'])) {

    $reservationID = (int) ($_POST['reservationID'] ?? 0);

    if ($reservationID <= 0) {
        $uploadError = "Invalid reservation.";
    } elseif (!isset($_FILES['proofOfPayment']) || $_FILES['proofOfPayment']['error'] !== UPLOAD_ERR_OK) {
        $uploadError = "Please choose a file to upload.";
    } else {
        $file = $_FILES['proofOfPayment'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);

        if (!in_array($fileType, $allowedTypes, true)) {
            $uploadError = "Only image files (JPG, PNG, GIF, WEBP) are allowed.";
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $uploadError = "File is too large. Maximum size is 5MB.";
        } else {
            // Confirm the reservation actually belongs to this user first.
            $checkSql = "SELECT reservationID FROM reservations WHERE reservationID = ? AND userID = ?";
            $checkStmt = mysqli_prepare($conn, $checkSql);
            mysqli_stmt_bind_param($checkStmt, "ii", $reservationID, $userID);
            mysqli_stmt_execute($checkStmt);
            $ownsReservation = mysqli_stmt_get_result($checkStmt)->num_rows > 0;
            mysqli_stmt_close($checkStmt);

            if (!$ownsReservation) {
                $uploadError = "You do not have permission to update this reservation.";
            } else {
                $uploadDir = __DIR__ . "/uploads/proof_of_payment/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $newFileName = "proof_" . $reservationID . "_" . time() . "." . $ext;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $relativePath = "uploads/proof_of_payment/" . $newFileName;

                    $updateSql = "UPDATE reservations SET proofOfPayment = ? WHERE reservationID = ? AND userID = ?";
                    $updateStmt = mysqli_prepare($conn, $updateSql);
                    mysqli_stmt_bind_param($updateStmt, "sii", $relativePath, $reservationID, $userID);
                    mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);

                    $uploadSuccess = "Proof of payment uploaded successfully.";
                } else {
                    $uploadError = "Something went wrong while uploading the file. Please try again.";
                }
            }
        }
    }
}

/**
 * Fetch all reservations belonging ONLY to the logged-in user.
 * INNER JOIN with ticket_types so ticket type / price don't need
 * to be duplicated inside the reservations table.
 */
$sql = "SELECT r.reservationID, r.ticketNumber, r.quantity, r.status,
               r.reservationDate, r.proofOfPayment, r.checkedInDate,
               t.ticketType, t.price
        FROM reservations r
        INNER JOIN ticket_types t ON r.ticketTypeID = t.ticketTypeID
        WHERE r.userID = ?
        ORDER BY r.reservationDate DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$reservations = [];
$totalTickets = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $row['totalPrice'] = $row['price'] * $row['quantity']; // computed in PHP, never stored
    $reservations[] = $row;
    $totalTickets += (int) $row['quantity'];
}
mysqli_stmt_close($stmt);

// Design/header/footer are handled by Stephen — included here if present
// so this file still runs stand-alone while merging is in progress.
if (file_exists(__DIR__ . "/includes/header.php")) {
    include __DIR__ . "/includes/header.php";
}
?>

<main class="myticket-page">
    <h1>My Tickets</h1>
    <p class="ticket-total">Total number of tickets: <strong><?= (int) $totalTickets ?></strong></p>

    <?php if ($uploadError): ?>
        <div class="alert alert-error"><?= h($uploadError) ?></div>
    <?php endif; ?>
    <?php if ($uploadSuccess): ?>
        <div class="alert alert-success"><?= h($uploadSuccess) ?></div>
    <?php endif; ?>

    <?php if (empty($reservations)): ?>
        <div class="empty-state">
            <p>You have not reserved any tickets yet.</p>
            <a href="reserve.php" class="btn btn-primary">Reserve a Ticket</a>
        </div>
    <?php else: ?>
        <table class="ticket-table">
            <thead>
                <tr>
                    <th>Ticket Number</th>
                    <th>Ticket Type</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Reservation Status</th>
                    <th>Reservation Date</th>
                    <th>Proof of Payment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td><?= h($r['ticketNumber']) ?></td>
                        <td><?= h($r['ticketType']) ?></td>
                        <td><?= (int) $r['quantity'] ?></td>
                        <td><?= formatCurrency($r['totalPrice']) ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $r['status'])) ?>">
                                <?= h($r['status']) ?>
                            </span>
                            <?php if ($r['status'] === 'Checked In' && $r['checkedInDate']): ?>
                                <div class="checked-in-date">Checked in: <?= h(formatDateTime($r['checkedInDate'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= h(formatDateTime($r['reservationDate'])) ?></td>
                        <td>
                            <?php if (!empty($r['proofOfPayment'])): ?>
                                <a href="<?= h($r['proofOfPayment']) ?>" target="_blank" rel="noopener">View Uploaded Proof</a>
                            <?php endif; ?>
                            <form method="POST" enctype="multipart/form-data" class="proof-upload-form">
                                <input type="hidden" name="reservationID" value="<?= (int) $r['reservationID'] ?>">
                                <input type="file" name="proofOfPayment" accept="image/*" required>
                                <button type="submit" name="upload_proof" class="btn btn-small">
                                    <?= !empty($r['proofOfPayment']) ? 'Replace' : 'Upload' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php
if (file_exists(__DIR__ . "/includes/footer.php")) {
    include __DIR__ . "/includes/footer.php";
}
?>
