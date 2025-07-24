<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];
$table_name = ($role == 'doctor') ? 'doctors' : 'patients';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fullname'], $_POST['mobile'])) {
    $fullname = $_POST['fullname'];
    $mobile = $_POST['mobile'];

    $sql = "UPDATE $table_name SET fullname = ?, mobile = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $fullname, $mobile, $user_id);
    
    if($stmt->execute()){
        $_SESSION['fullname'] = $fullname; // Update session
        header("Location: profile.php?success=1");
        exit;
    }
}

// Fetch user data
$sql_select = ($role == 'doctor') 
    ? "SELECT fullname, email, mobile FROM $table_name WHERE id = ?"
    : "SELECT fullname, email, mobile, profile_picture FROM $table_name WHERE id = ?";

$stmt = $conn->prepare($sql_select);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$display_profile_pic = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
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
        .navbar a.logo {
            font-weight: bold;
            font-size: 1.2rem;
            color: black !important; /* Force color to be black */
            text-decoration: none;
        }
        .dropdown-content a {
            color: black !important; /* Force color to be black */
            padding: 12px 16px;
            display: block;
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
        .dropdown-content a:hover { background-color: #f1f1f1; }
        .profile-menu:hover .dropdown-content { display: block; }
        .form-container {
            background-color: #fff;
            max-width: 600px;
            margin: 40px auto;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            color: black;
            margin-bottom: 25px;
        }
        .form-container label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            color: black;
            font-weight: bold;
        }
        .form-container input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .form-container input:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
        .form-container button {
            width: 100%;
            padding: 12px;
            margin-top: 25px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #F2F3E1;
            color: #333;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="logo" href="<?php echo $role == 'doctor' ? 'doctor_dashboard.php' : 'patient_dashboard.php'; ?>">Dashboard</a>
            <div class="navbar-links">
                <div class="profile-menu">
                    <img src="uploads/<?php echo $display_profile_pic; ?>" alt="Profile" class="profile-icon">
                    <div class="dropdown-content">
                        <a href="profile.php">Profile</a>
                        <a href="history.php">History</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="form-container">
        <h2>My Profile</h2>
        <?php if(isset($_GET['success'])) echo "<p class='message'>Profile updated successfully!</p>"; ?>
        <div style="text-align: center; margin-bottom: 2rem;">
            <img src="uploads/<?php echo $display_profile_pic; ?>" alt="Profile Picture" style="width:150px; height:150px; border-radius:50%; object-fit:cover;">
        </div>
        <form action="profile.php" method="post">
            <label>Full Name:</label>
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
            <label>Email Address (cannot be changed):</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            <label>Mobile Number:</label>
            <input type="text" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
            <button type="submit">Update Profile</button>
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