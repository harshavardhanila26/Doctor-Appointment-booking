<?php
session_start();
include 'db_connect.php';

// Redirect if not a logged-in patient or if no appointment details are in session
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'patient' || !isset($_SESSION['temp_appointment'])) {
    header('Location: patient_dashboard.php');
    exit;
}

$temp_appointment = $_SESSION['temp_appointment'];

// Fetch doctor's full name for display
$stmt_doc = $conn->prepare("SELECT fullname FROM doctors WHERE id = ?");
$stmt_doc->bind_param("i", $temp_appointment['doctor_id']);
$stmt_doc->execute();
$result_doc = $stmt_doc->get_result();
$doctor_info = $result_doc->fetch_assoc();
$doctor_name = $doctor_info ? $doctor_info['fullname'] : 'Unknown Doctor';


$payment_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = $temp_appointment['patient_id'];
    $doctor_id = $temp_appointment['doctor_id'];
    $appointment_date = $temp_appointment['appointment_date'];
    $appointment_time = $temp_appointment['appointment_time'];

    $stmt_book = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)");
    $stmt_book->bind_param("iiss", $patient_id, $doctor_id, $appointment_date, $appointment_time);
    
    if ($stmt_book->execute()) {
        $payment_message = "Payment successful! Your appointment has been booked.";
        unset($_SESSION['temp_appointment']);
    } else {
        $payment_message = "Payment processed, but there was an error booking your appointment. Please contact support.";
    }
    $stmt_book->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Process Payment</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #B6D1B6;
            font-family: Arial, sans-serif;
            margin: 0;
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
            font-weight: bold;
            color: black; /* Set text color to black */
        }
        .form-container {
            background-color: #fff;
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .form-container h2 {
            color: black;
        }
        .form-container button {
            background-color: #F2F3E1;
            color: #333;
            border: 1px solid #ccc;
            padding: 12px 20px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
        
        <?php if(!empty($payment_message)): ?>
            <div class="message">
                <p><?php echo $payment_message; ?></p>
            </div>
            <?php if (strpos($payment_message, 'successful') !== false): ?>
                <p><a href="history.php" class="btn">View My Appointments</a></p>
            <?php endif; ?>
        <?php else: ?>
            <h2>Confirm Payment</h2>
            <p style="text-align:center; margin-bottom: 2rem;">
                You are about to book an appointment with <strong>Dr. <?php echo htmlspecialchars($doctor_name); ?></strong><br>
                on <strong><?php echo htmlspecialchars(date("d M, Y", strtotime($temp_appointment['appointment_date']))); ?></strong> at <strong><?php echo htmlspecialchars(date('h:i A', strtotime($temp_appointment['appointment_time']))); ?></strong>.
            </p>
            <p style="text-align:center; font-size: 1.2rem; font-weight: bold; margin-bottom: 2rem;">
                Total Fee: $<?php echo htmlspecialchars(number_format($temp_appointment['fee'], 2)); ?>
            </p>
            
            <form action="process_payment.php" method="post">
                <p style="text-align: center; color: #555;">(This is a simulated payment. No real transaction will occur.)</p>
                <button type="submit">Complete Payment</button>
            </form>
        <?php endif; ?>
    </div>
    <footer class="footer">
    <div class="footer-container">
        
        <p>Developed by Harsha Vardhan</p>
        <p>&copy; <?php echo date("Y"); ?> Doctor Appointment System. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>