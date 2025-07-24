<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'patient') {
    header('Location: login.php');
    exit;
}

// Initialize search and filter variables
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$specialization_filter = isset($_GET['specialization_filter']) ? $_GET['specialization_filter'] : '';

// Fetch distinct specializations for the filter dropdown
$specializations_sql = "SELECT DISTINCT specialization FROM doctors ORDER BY specialization ASC";
$specializations_result = $conn->query($specializations_sql);
$specializations = [];
while ($row = $specializations_result->fetch_assoc()) {
    $specializations[] = $row['specialization'];
}

// Build the SQL query for doctors
$sql = "SELECT id, fullname, specialization, fee FROM doctors WHERE 1=1"; 

$params = [];
$types = '';

if (!empty($search_query)) {
    $sql .= " AND (fullname LIKE ? OR specialization LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $types .= 'ss';
}

if (!empty($specialization_filter)) {
    $sql .= " AND specialization = ?";
    $params[] = $specialization_filter;
    $types .= 's';
}

$sql .= " ORDER BY fullname ASC"; 

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$doctors = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
    <style>
        body {
            background-image: url('doctor_patientdashboard.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.92);
            padding: 25px;
            border-radius: 10px;
        }
        .search-filter-form {
            text-align: center;
            margin: 25px 0;
        }
        .search-filter-form input[type="text"],
        .search-filter-form select {
            font-size: 1rem;
            padding: 12px 15px;
            height: 50px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin: 5px;
            vertical-align: middle;
        }
        .search-filter-form button,
        .doctor-card .btn {
            display: inline-block;
            font-size: 1rem;
            padding: 12px 20px;
            min-height: 50px;
            box-sizing: border-box;
            color: white;
            background-color: #6A7E4D; 
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            vertical-align: middle;
            transition: background-color 0.2s;
        }
        .search-filter-form button:hover,
        .doctor-card .btn:hover {
            background-color: #556B2F; 
        }
        .doctor-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .doctor-card {
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .doctor-card h4 {
            margin-top: 0;
            color: black; /* Changed from blue to black */
        }
        .doctor-card p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    
    <div class="container" style="margin-top: 20px;">
        <h3>Available Doctors</h3>

        <form action="patient_dashboard.php" method="get" class="search-filter-form">
            <input type="text" name="search" placeholder="Search by name or specialization..." value="<?php echo htmlspecialchars($search_query); ?>" style="width: 35%;">
            <select name="specialization_filter" style="width: 30%;">
                <option value="">All Specializations</option>
                <?php foreach ($specializations as $spec): ?>
                    <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo ($specialization_filter == $spec) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($spec); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Search</button>
            <?php if (!empty($search_query) || !empty($specialization_filter)): ?>
                <a href="patient_dashboard.php" class="clear-filter-btn">Clear</a>
            <?php endif; ?>
        </form>

        <div class="doctor-list">
            <?php if ($doctors->num_rows > 0): ?>
                <?php while($doc = $doctors->fetch_assoc()): ?>
                    <div class="doctor-card">
                        <h4>Dr. <?php echo htmlspecialchars($doc['fullname']); ?></h4>
                        <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doc['specialization']); ?></p>
                        <p><strong>Fee:</strong> $<?php echo htmlspecialchars(number_format($doc['fee'], 2)); ?></p>
                        <a href="book_appointment.php?doctor_id=<?php echo $doc['id']; ?>" class="btn">Book Appointment</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%;">No doctors found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('sw.js');
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