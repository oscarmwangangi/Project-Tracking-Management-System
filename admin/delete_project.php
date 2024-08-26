<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['project_id'])) {
    $project_id = $_POST['project_id'];

    // Prepare and execute the delete statement
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);

    // Redirect back to the projects page
    header('Location: addedproject.php');
    exit();
} else {
    // If accessed directly without POST, redirect to the projects page
    header('Location: addedproject.php');
    exit();
}
