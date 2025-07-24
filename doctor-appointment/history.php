<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];
$dashboard_link = ($role == 'doctor') ? 'doctor_dashboard.php' : 'patient_dashboard.php';

if ($role == 'doctor') {
    // Join appointments with the patients table
    $sql = "SELECT a.id, p.fullname AS patient_name, a.appointment_date, a.appointment_time, a.status
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.doctor_id = ? ORDER BY a.appointment_date DESC, a.appointment_time DESC";
} else {
    // Join appointments with the doctors table
    $sql = "SELECT a.id, d.fullname AS doctor_name, d.specialization, a.appointment_date, a.appointment_time, a.status, a.doctor_id
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.patient_id = ? ORDER BY a.appointment_date DESC, a.appointment_time DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result();

$navbar_profile_pic = 'default-avatar.png';
if ($role == 'patient' && isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture'])) {
    $navbar_profile_pic = $_SESSION['profile_picture'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment History</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: transparent;
            padding: 0;
            margin: 0 auto;
            box-shadow: none;
        }
        .navbar a.logo {
            font-weight: bold;
            font-size: 1.2rem;
            color: black;
            text-decoration: none;
        }
        .profile-menu {
            position: relative;
            display: inline-block;
        }
        .profile-icon {
            height: 40px;
            width: 40px;
            border-radius: 50%;
            cursor: pointer;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0;
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {background-color: #f1f1f1;}
        .profile-menu:hover .dropdown-content {display: block;}
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
            color: black;
        }
        th {
            background-color: #f8f9fa;
        }
        .btn-small {
            background-color: #6c757d;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn-small:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="logo" href="<?php echo $dashboard_link; ?>">Dashboard</a>
            <div class="navbar-links">
                <div class="profile-menu">
                    <img src="uploads/<?php echo htmlspecialchars($navbar_profile_pic); ?>" alt="Profile" class="profile-icon">
                    <div class="dropdown-content">
                        <a href="profile.php">Profile</a>
                        <a href="history.php">History</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="container">
        <h3>Appointment History</h3>
        <table>
            <thead>
                <?php if($role == 'patient'): ?>
                <tr>
                    <th>Doctor Name</th><th>Specialization</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th>
                </tr>
                <?php else: ?>
                <tr>
                    <th>Patient Name</th><th>Date</th><th>Time</th><th>Status</th>
                </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php if ($history->num_rows > 0): ?>
                    <?php while($item = $history->fetch_assoc()): ?>
                        <tr>
                            <?php if($role == 'patient'): ?>
                                <td>Dr. <?php echo htmlspecialchars($item['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['specialization']); ?></td>
                            <?php else: ?>
                                <td><?php echo htmlspecialchars($item['patient_name']); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars(date("d M, Y", strtotime($item['appointment_date']))); ?></td>
                            <td><?php echo htmlspecialchars(date('h:i A', strtotime($item['appointment_time']))); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($item['status'])); ?></td>
                            <?php if($role == 'patient'): ?>
                                <td>
                                    <?php if ($item['status'] == 'booked'): ?>
                                        <a href="reschedule_appointment.php?id=<?php echo $item['id']; ?>&doctor_id=<?php echo $item['doctor_id']; ?>" class="btn-small">Reschedule</a>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="<?php echo ($role == 'patient' ? 6 : 5); ?>" style="text-align:center;">No history found.</td></tr>
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