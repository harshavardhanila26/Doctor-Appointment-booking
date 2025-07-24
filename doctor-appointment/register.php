<?php
session_start();
include 'db_connect.php';
$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Common fields
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Password validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if ($role == 'patient') {
            $sql = "INSERT INTO patients (fullname, email, mobile, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $fullname, $email, $mobile, $hashed_password);
        } elseif ($role == 'doctor') {
            $specialization = $_POST['specialization'];
            $fee = $_POST['fee'];
            $sql = "INSERT INTO doctors (fullname, email, mobile, password, specialization, fee) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssd", $fullname, $email, $mobile, $hashed_password, $specialization, $fee);
        }

        try {
            if (isset($stmt) && $stmt->execute()) {
                $message = "Registration successful! You can now <a href='login.php'>login</a>.";
            }
        } catch (mysqli_sql_exception $e) {
            if ($conn->errno == 1062) { // 1062 is the error code for duplicate entry
                $error = "An account with this email already exists.";
            } else {
                $error = "An error occurred. Please try again later.";
            }
        }
        if (isset($stmt)) $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
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
            margin: 3% auto;
        }
        .form-container h2 {
            color: black; /* Changed heading color to black */
            text-align: center;
            margin-bottom: 25px;
        }
        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="tel"],
        .form-container input[type="password"],
        .form-container input[type="number"] {
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
        .role-selector {
            text-align: center;
            margin-bottom: 20px;
        }
        .role-selector label {
            margin: 0 15px;
        }
        p {
            text-align: center;
        }
        a {
            color: black;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Register New Account</h2>
        <?php if($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
        <?php if($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <form action="register.php" method="post" id="registrationForm">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="tel" name="mobile" placeholder="Mobile Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>

            <div class="role-selector">
                <label><input type="radio" name="role" value="patient" checked onchange="toggleDoctorFields()"> Patient</label>
                <label><input type="radio" name="role" value="doctor" onchange="toggleDoctorFields()"> Doctor</label>
            </div>

            <div id="doctor-fields" style="display: none;">
                <input type="text" name="specialization" placeholder="Specialization (e.g., Cardiology)">
                <input type="number" step="0.01" name="fee" placeholder="Consultation Fee ($)">
            </div>

            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <script>
        function toggleDoctorFields() {
            const doctorFields = document.getElementById('doctor-fields');
            const doctorRadio = document.querySelector('input[name="role"][value="doctor"]');
            const specializationInput = document.querySelector('input[name="specialization"]');
            const feeInput = document.querySelector('input[name="fee"]');

            if (doctorRadio.checked) {
                doctorFields.style.display = 'block';
                specializationInput.required = true;
                feeInput.required = true;
            } else {
                doctorFields.style.display = 'none';
                specializationInput.required = false;
                feeInput.required = false;
            }
        }
    </script>
    <footer class="footer">
    <div class="footer-container">
        
        <p>Developed by Harsha Vardhan</p>
        <p>&copy; <?php echo date("Y"); ?> Doctor Appointment System. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>