<?php
require '../db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $description = $_POST['description'];
    $craftsman_id = $_SESSION['user_id'];

    // التأكد من أن العنصر ينتمي للحرفي
    $stmt = $conn->prepare("UPDATE craftsman_portfolio SET description = ? WHERE portfolio_id = ? AND craftsman_id = ?");
    $stmt->bind_param("sii", $description, $item_id, $craftsman_id);

    if ($stmt->execute()) {
        header("Location: portfolio.php?updated=1");
    } else {
        header("Location: portfolio.php?error=update_failed");
    }
    $stmt->close();
}
?>