<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'doctor') {
    header('Location: login.php');
    exit;
}

$doctor_id = $_SESSION['id'];

// Handle appointment updates
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $appt_id = $_GET['id'];
    $status = ($action == 'complete') ? 'completed' : 'cancelled';

    $update_stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
    $update_stmt->bind_param("sii", $status, $appt_id, $doctor_id);
    $update_stmt->execute();
    header('Location: doctor_dashboard.php');
    exit;
}

// Fetch upcoming appointments by joining with the patients table
$sql = "SELECT a.id, p.fullname, p.mobile, a.appointment_date, a.appointment_time
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        WHERE a.doctor_id = ? AND a.status = 'booked'
        ORDER BY a.appointment_date, a.appointment_time";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #B6D1B6; 
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .container {
            background-color: #fff;
            margin: 20px auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 950px;
        }
        .navbar {
            background-color: #F2F3E1; 
            padding: 1rem;
            border-bottom: 1px solid #ddd;
        }
        .navbar .container {
            background-color: transparent;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0;
        }
        .navbar a {
            color: black; 
            text-decoration: none;
            margin-left: 15px;
        }
        .navbar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            margin-left: 0;
            color: black;
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
            color: black;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            color: black;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .action-icons a {
            color: black; 
            text-decoration: none;
            margin: 0 8px;
            font-size: 1.3rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="logo" href="#">Dr. <?php echo htmlspecialchars($_SESSION['fullname']); ?>'s Dashboard</a>
             <div class="navbar-links">
                <a href="profile.php">Profile</a>
                <a href="history.php">History</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <h3>Your Upcoming Appointments</h3>
        <table>
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Mobile</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appointments->num_rows > 0): ?>
                    <?php while($appt = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appt['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($appt['mobile']); ?></td>
                            <td><?php echo htmlspecialchars(date("d M, Y", strtotime($appt['appointment_date']))); ?></td>
                            <td><?php echo htmlspecialchars(date('h:i A', strtotime($appt['appointment_time']))); ?></td>
                            <td class="action-icons">
                                <a href="?action=complete&id=<?php echo $appt['id']; ?>" title="Mark as Completed">✔️</a>
                                <a href="?action=cancel&id=<?php echo $appt['id']; ?>" onclick="return confirm('Are you sure?');" title="Cancel Appointment">❌</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">No upcoming appointments.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <footer class="footer">
    <div class="footer-container">
        
        <p>Developed by Harsha Vardhan</p>
        <p>&copy; <?php echo date("Y"); ?> Doctor Appointment System. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>