<?php
session_start();
include 'db_connect.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "(SELECT id, fullname, password, profile_picture, 'patient' as role FROM patients WHERE email = ?)
            UNION
            (SELECT id, fullname, password, profile_picture, 'doctor' as role FROM doctors WHERE email = ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_picture'] = $user['profile_picture'] ?? 'default-avatar.png';


            if ($user['role'] == 'doctor') {
                header("location: doctor_dashboard.php");
            } else {
                header("location: patient_dashboard.php");
            }
            exit;
        } else {
            $error = 'The password you entered was not valid.';
        }
    } else {
        $error = 'No account found with that email.';
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #B6D1B6;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .form-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 450px;
            margin: 8% auto;
        }
        .form-container h2 {
            color: black; /* Changed heading color to black */
            text-align: center;
            margin-bottom: 25px;
        }
        .form-container input[type="email"],
        .form-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .form-container button {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #F2F3E1;
            color: #333;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        p {
            text-align: center;
            margin-top: 20px;
        }
        a {
            color: black;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Doctor Appointment System Login</h2>
        <?php if($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <form action="login.php" method="post">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
    <footer class="footer">
    <div class="footer-container">
        
        <p>Developed by Harsha Vardhan</p>
        <p>&copy; <?php echo date("Y"); ?> Doctor Appointment System. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>