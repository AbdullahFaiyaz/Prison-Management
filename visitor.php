<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'visitor') {
    header("Location: index.php");
    exit();
}

require 'db.php';

$stmt = $pdo->query("SELECT * FROM visitor");
$visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Visitor Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h2, h3 {
            margin-bottom: 10px;
        }
        .section {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #aaa;
        }
        th, td {
            padding: 8px;
        }
        th {
            background-color: #f0f0f0;
        }
        .logout {
            color: red;
            text-decoration: none;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<h2>Visitor Dashboard</h2>
<a class="logout" href="logout.php">Logout</a>

<div class="section">
    <h3>Visitor Table</h3>
    <?php if (!$visitors): ?>
        <p><em>No data found in Visitor table.</em></p>
    <?php else: ?>
        <table>
            <tr>
                <th>Visitor_ID</th>
                <th>Prisoner_ID</th>
                <th>Visitor_Name</th>
                <th>Relationship</th>
                <th>Visit_Date</th>
                <th>Visit_Time</th>
                <th>Duration(mins)</th>
                <th>Status</th>
                <th>Notes</th>
            </tr>
            <?php foreach ($visitors as $visitor): ?>
                <tr>
                    <td><?= htmlspecialchars($visitor['Visitor_ID']) ?></td>
                    <td><?= htmlspecialchars($visitor['Prisoner_ID']) ?></td>
                    <td><?= htmlspecialchars($visitor['Visitor_Name']) ?></td>
                    <td><?= htmlspecialchars($visitor['Relationship']) ?></td>
                    <td><?= htmlspecialchars($visitor['Visit_Date']) ?></td>
                    <td><?= htmlspecialchars($visitor['Visit_Time']) ?></td>
                    <td><?= htmlspecialchars($visitor['Duration(mins)']) ?></td>
                    <td><?= htmlspecialchars($visitor['Status']) ?></td>
                    <td><?= htmlspecialchars($visitor['Notes']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>