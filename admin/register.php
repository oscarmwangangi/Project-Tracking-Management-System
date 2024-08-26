<?php
// Include configuration file
require_once '../db.php';

// Initialize variables
$username = $email = $password = $confirm_password = $role = $directorate ="";
$username_err = $email_err = $password_err = $confirm_password_err  = $role_err = $directorate_err = $success_msg = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $trimmed_username = trim($_POST["username"]); // Store trimmed username in a variable


        // Check if username already exists
        $sql = "SELECT id FROM users WHERE username = :username";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':username', $trimmed_username, PDO::PARAM_STR);
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = $trimmed_username;
                }
            }
        }
        unset($stmt);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $trimmed_email = trim($_POST["email"]); // Store trimmed email in a variable


        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = :email";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':email', $trimmed_email, PDO::PARAM_STR);
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = $trimmed_email;
                }
            }
        }
        unset($stmt);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm your password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        } 
    }
    // validate role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
    }

    // validate directorate
    if (empty(trim($_POST["directorate"]))) {
        $directorate_err = "Please select a directorate.";
    } else {
        $directorate = trim($_POST["directorate"]);
    }

     // Check if admin role is already taken for the selected directorate
    //  if ($role == 'admin') {
    //     $sql = "SELECT id FROM users WHERE role = 'admin' AND directorate = :directorate";
    //     if ($stmt = $pdo->prepare($sql)) {
    //         $stmt->bindParam(':directorate', $directorate, PDO::PARAM_STR);
    //         if ($stmt->execute()) {
    //             if ($stmt->rowCount() > 0) {
    //                 $directorate_err = "An admin already exists for the selected directorate.";
    //             }
    //         }
    //     }
    //     unset($stmt);
    // }
//checks for the total number of admins 
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $totalAdmins = $stmt->fetchColumn();

        //executes
        if ($totalAdmins >= 7) {
            echo "<script>alert('The system has reached the maximum number of admins. Please update the system.');</script>";
            echo "<script>window.location.href='./index.php';</script>";
            exit(); // Stop further execution
        }


    

    // Check for errors before inserting in database
    if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($role_err) && empty($directorate_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, email, password, role, directorate) VALUES (:username, :email, :password, :role, :directorate)";
        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->bindParam(':directorate', $directorate, PDO::PARAM_STR);
            // Hash the password before saving in the database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $success_msg = "Registration successful! You can now log in.";
                header("location: index.php");
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        unset($stmt);
    }

    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<style>
    .wrapper {
            width: 360px;
            padding: 40px;
            margin: auto;
            margin-top: 100px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(9, 187, 247, 0.5);
            border-radius: 8px;
        }
</style>
<body>
    <div class="wrapper">
        <h2 class="text-center">Register</h2>
        <p class="text-center">Please fill in this form to create an account.</p>
        
        <?php
        if (!empty($username_err)) {
            echo '<div class="alert alert-danger">' . $username_err . '</div>';
        }
        if (!empty($email_err)) {
            echo '<div class="alert alert-danger">' . $email_err . '</div>';
        }
        if (!empty($password_err)) {
            echo '<div class="alert alert-danger">' . $password_err . '</div>';
        }
        if (!empty($confirm_password_err)) {
            echo '<div class="alert alert-danger">' . $confirm_password_err . '</div>';
        }
        if (!empty($success_msg)) {
            echo '<div class="alert alert-success">' . $success_msg . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $email; ?>">
            </div>
            <div class="form-group">
        <label>Role</label>
        <select name="role" class="form-control">
            <!-- <option value="user">User</option> -->
            <option value="admin">Admin</option>
        </select>
    </div>
    <div class="form-group">
        <label>Directorate</label>
        <select name="directorate" class="form-control">
            <option value="ICT Security">ICT Security</option>
            <!-- <option value="E-Government">E-Government</option>
            <option value="Infrastructure">Infrastructure</option> -->
        </select>
    </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary btn-block" value="Register">
            </div>
        </form>
    </div>
</body>
</html>
