<?php
session_start();
include 'db_connect.php';

// Check if user is logged in and is a patient
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'patient') {
    header('Location: login.php');
    exit;
}

// Get appointment ID and doctor ID from the URL
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;

if ($appointment_id == 0 || $doctor_id == 0) {
    header('Location: history.php'); // Redirect if IDs are missing
    exit;
}

// Fetch doctor details
$stmt_doc = $conn->prepare("SELECT fullname, specialization FROM doctors WHERE id = ?");
$stmt_doc->bind_param("i", $doctor_id);
$stmt_doc->execute();
$result_doc = $stmt_doc->get_result();
$doctor = $result_doc->fetch_assoc();

if (!$doctor) {
    echo "Error: Doctor not found.";
    exit;
}

// Fetch current appointment details to pre-fill or validate
$stmt_appt = $conn->prepare("SELECT appointment_date, appointment_time FROM appointments WHERE id = ? AND patient_id = ? AND doctor_id = ? AND status = 'booked'");
$stmt_appt->bind_param("iii", $appointment_id, $_SESSION['id'], $doctor_id);
$stmt_appt->execute();
$current_appointment = $stmt_appt->get_result()->fetch_assoc();

if (!$current_appointment) {
    // If appointment not found, or not owned by patient, or not booked, redirect
    header('Location: history.php');
    exit;
}

$message = '';
$message_type = '';
// Handle form submission for rescheduling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_appointment_date = $_POST['date'];
    $new_appointment_time = $_POST['time'];
    $new_appointment_datetime = new DateTime("$new_appointment_date $new_appointment_time");
    $now = new DateTime();

    // Basic validation: Cannot reschedule to a past date/time
    if ($new_appointment_datetime < $now) {
        $message = "Error: You cannot reschedule to a past date or time.";
        $message_type = 'error';
    } else {
        // Update the appointment
        $stmt_update = $conn->prepare("UPDATE appointments SET appointment_date = ?, appointment_time = ? WHERE id = ? AND patient_id = ? AND doctor_id = ?");
        $stmt_update->bind_param("ssiii", $new_appointment_date, $new_appointment_time, $appointment_id, $_SESSION['id'], $doctor_id);
        
        if ($stmt_update->execute()) {
            $message = "Appointment rescheduled successfully!";
            $message_type = 'message';
            // Optionally, update the current_appointment details to reflect the change immediately
            $current_appointment['appointment_date'] = $new_appointment_date;
            $current_appointment['appointment_time'] = $new_appointment_time;
        } else {
            $message = "Error rescheduling appointment. Please try again.";
            $message_type = 'error';
        }
        $stmt_update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reschedule Appointment</title>
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
        .navbar a.logo,
        .navbar .navbar-links a {
            color: black;
            text-decoration: none;
        }
        .navbar a.logo {
            font-weight: bold;
            font-size: 1.2rem;
        }
        .form-container {
            background-color: #fff;
            max-width: 500px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            color: black;
        }
        .form-container input[type="date"],
        .form-container input[type="time"],
        .form-container button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 1rem;
        }
        .form-container button {
            background-color: #F2F3E1;
            color: #333;
            cursor: pointer;
            font-weight: bold;
        }
        .form-container button:hover {
            background-color: #e9ebd5;
        }
        .message, .error {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
             <a class="logo" href="patient_dashboard.php">Doctor Appointment System</a>
             <div class="navbar-links">
                <a href="history.php">Back to History</a>
             </div>
        </div>
    </nav>
    <div class="form-container">
        <h2>Reschedule Appointment with Dr. <?php echo htmlspecialchars($doctor['fullname']); ?></h2>
        <p style="text-align:center; margin-top:-1rem; margin-bottom: 2rem;">
            <strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?><br>
            Current Appointment: <?php echo htmlspecialchars(date("d M, Y", strtotime($current_appointment['appointment_date']))); ?> at <?php echo htmlspecialchars(date('h:i A', strtotime($current_appointment['appointment_time']))); ?>
        </p>
        
        <?php if($message): ?>
            <p class="<?php echo htmlspecialchars($message_type); ?>"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <form action="reschedule_appointment.php?id=<?php echo $appointment_id; ?>&doctor_id=<?php echo $doctor_id; ?>" method="post">
            <label for="date">Select New Date:</label>
            <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($current_appointment['appointment_date']); ?>">
            
            <label for="time">Select New Time:</label>
            <input type="time" id="time" name="time" required value="<?php echo htmlspecialchars($current_appointment['appointment_time']); ?>">
            
            <button type="submit">Reschedule Appointment</button>
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