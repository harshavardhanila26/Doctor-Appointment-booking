<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'patient') {
    header('Location: login.php');
    exit;
}

$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
if ($doctor_id == 0) {
    header('Location: patient_dashboard.php');
    exit;
}

$stmt_doc = $conn->prepare("SELECT fullname, specialization, fee FROM doctors WHERE id = ?");
$stmt_doc->bind_param("i", $doctor_id);
$stmt_doc->execute();
$result_doc = $stmt_doc->get_result();
$doctor = $result_doc->fetch_assoc();

if (!$doctor) {
    echo "Error: Doctor not found.";
    exit;
}

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointment_date = $_POST['date'];
    $appointment_time = $_POST['time'];
    $_SESSION['temp_appointment'] = [
        'patient_id' => $_SESSION['id'],
        'doctor_id' => $doctor_id,
        'appointment_date' => $appointment_date,
        'appointment_time' => $appointment_time,
        'fee' => $doctor['fee']
    ];
    header("Location: process_payment.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #B6D1B6;
        }
        .navbar {
            background-color: #F2F3E1; 
            padding: 1rem;
            border-bottom: 1px solid #ddd;
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 950px;
            margin: 0 auto;
        }
        .navbar a {
            color: black;
            text-decoration: none;
        }
        .navbar .logo {
             font-size: 1.2rem;
             font-weight: bold;
             color: black; /* Set text color to black */
        }
        .form-container {
            background-color: #ffffff;
            max-width: 500px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-container button {
            background-color: #F2F3E1; 
            color: black; 
            border: 1px solid #ccc;
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
             <a class="logo" href="patient_dashboard.php">Doctor Appointment System</a>
             <div class="navbar-links">
                <a href="patient_dashboard.php">Back to Dashboard</a>
             </div>
        </div>
    </nav>
    <div class="form-container">
        <h2>Book with Dr. <?php echo htmlspecialchars($doctor['fullname']); ?></h2>
        <p style="text-align:center;">
            <strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?><br>
            <strong>Fee:</strong> $<?php echo htmlspecialchars(number_format($doctor['fee'], 2)); ?>
        </p>
        
        <form action="book_appointment.php?doctor_id=<?php echo $doctor_id; ?>" method="post">
            <label for="date">Select Date:</label>
            <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box;">
            
            <label for="time">Select Time:</label>
            <input type="time" id="time" name="time" required style="width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box;">
            
            <button type="submit">Proceed to Payment</button>
        </form>
    </div>
    <footer class="footer">
    <div class="footer-container">
        
        <p>Developed by Harsha Vardhan</p>
        <p>&copy; <?php echo date("Y"); ?> Doctor Appointment System. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>