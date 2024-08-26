<?php
// Include configuration file
require_once '../db.php';

// Initialize variables
$email = "";
$email_err = $success_msg = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate email
    if (empty($email_err)) {
        // Prepare a select statement to check if the email exists
        $sql = "SELECT id FROM users WHERE email = :email";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    // Generate a unique reset token
                    $token = bin2hex(random_bytes(50));
                    $expiry_time = date("Y-m-d H:i:s", strtotime('+1 hour'));

                    // Store the token and expiry in the database (implement this as per your database structure)
                    $sql = "UPDATE users SET reset_token = :token, token_expiry = :expiry WHERE email = :email";
                    if ($stmt = $pdo->prepare($sql)) {
                        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
                        $stmt->bindParam(':expiry', $expiry_time, PDO::PARAM_STR);
                        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                        $stmt->execute();
                    }

                    // Send reset link to the user's email (implement your email sending logic here)
                    $reset_link = "http://yourdomain.com/reset_password.php?token=" . $token;
                    $subject = "Password Reset Request";
                    $message = "Please click the link to reset your password: $reset_link";
                    // mail($email, $subject, $message);

                    $success_msg = "A password reset link has been sent to your email.";
                } else {
                    $email_err = "This email is not associated with any account.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }

    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="wrapper">
        <h2 class="text-center">Forgot Password</h2>
        <p class="text-center">Please enter your email to reset your password.</p>
        
        <?php
        if (!empty($email_err)) {
            echo '<div class="alert alert-danger">' . $email_err . '</div>';
        }
        if (!empty($success_msg)) {
            echo '<div class="alert alert-success">' . $success_msg . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $email; ?>">
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary btn-block" value="Submit" onclick=(header(login.php))>
            </div>
        </form>
    </div>
</body>
</html>