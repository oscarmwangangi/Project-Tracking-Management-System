<?php
session_start();
require '../db.php';

if (!isset($_SESSION['role'])) {
    header("Location: index.php");
   
}
if (!isset($_SESSION['role'])) {
    header("Location: index.php");
   
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $directorate = $_POST['directorate'];

    // Check if the username already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $usernameExists = $stmt->fetchColumn();

    if ($usernameExists > 0) {
        echo "Username already exists. Please choose a different username.";
    } else {
        // Check if the selected directorate already has an admin
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin' AND directorate = ?");
        $stmt->execute([$directorate]);
        $adminCount = $stmt->fetchColumn();

        if ($adminCount < 7) {
            // Add the new admin to the database
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, directorate, email) VALUES (?, ?, 'admin', ?, ?)");
            $stmt->execute([$username, $password, $directorate, $email]);

            echo "Admin added successfully.";
        } else {
            echo "This directorate already has the maximum number of admins.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<style> 
.container{
    padding: 50px;
    padding-top: 0px;
    box-shadow: 0 0 10px rgba(9, 187, 247, 0.5);
    background-color: white;
}
body{
    background-image:url(https://th.bing.com/th/id/OIP.8rSPC3ovjMB09aG5yimZjQHaDf?w=306&h=164&c=7&r=0&o=5&pid=1.7);
    background-size: cover;
}
</style>
<body>

    <div class="container mt-5">
        <!-- <div class="d-flex flex-column justify-content-center align-items-center p-30 center-content">
            <img src="../image/court of arms.png" alt="Court of Arms" height="130px" max-width="130px">
            <div class="h1-wrapper">
                <span class="line1">Ministry of Information Communications and</span>
                <span class="line2">The Digital Economy</span>
            </div>
            <div class="h2-wrapper">
                <span class="line1">State Department For ICT and Digital Economy</span>
            </div>
            <div class="h3-wrapper">
                <span class="line1">Directorate of ICT Security and Audit Control</span>
            </div>
        </div> -->
        <h2>Add New Admin</h2>
        <form method="POST" action="add_admin.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="directorate">Directorate</label>
                <select name="directorate" id="directorate" class="form-control" required>
                    <option value="ICT Security">ICT Security</option>
                    <!-- <option value="E-Government">E-Government</option>
                    <option value="Infrastructure">Infrastructure</option> -->
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Admin</button>
        </form>
    </div>
</body>
</html>
