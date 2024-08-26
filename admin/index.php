<?php
// Start session
session_start();

// Include configuration file
require_once '../db.php';

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password, role, directorate FROM users WHERE username = :username";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if username exists, if yes then verify password
                if ($stmt->rowCount() == 1) {
                    // Bind result variables
                    if ($row = $stmt->fetch()) {
                        $id = $row['id'];
                        $username = $row['username'];
                        $hashed_password = $row['password'];
                        $role = $row['role'];
                        $directorate = $row['directorate'];
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;
                            $_SESSION["directorate"] = $directorate;

                            // Redirect user to the appropriate page
                            if ($role == 'admin') {
                                header("location: addedproject.php");
                            } else {
                                header("location: ../user.php");
                            }
                        } else {
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist, display a generic error message
                    $login_err = "Invalid username or password.";
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
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            /* background-color: #f8f9fa; */
            background-image: url("paper.gif");
            background-color: #cccccc;
            }
        .wrapper {
            width: 360px;
            padding: 40px;
            padding-top: 13px;
            padding-bottom: 13px;
            margin: auto;
            margin-top: 12%;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(9, 187, 247, 0.5);
            border-radius: 8px;
            
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .invalid-feedback {
            display: block;
        }
        .links {
            text-align: center;
        }
    </style>
</head>
<body> 
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
                    <span class="line1">Directorate of ICT Seccurity and Audit Control</span>
                    </div>
            </div> -->
        <!-- <h2 class="text-center">Login</h2> -->
    <div class="wrapper">
       
        <p class="text-center">Please fill in your credentials to login.</p>

        <?php
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary btn-block" value="Login">
            </div>
            <div class="links">
                <a href="forgot.php" disabled="true">Forgot Password?</a> | <a href="register.php"
                 >Register Now</a>
            </div>
        </form>
    </div>
</body>
</html>
