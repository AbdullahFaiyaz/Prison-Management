<?php
session_start();

// Redirect if not logged in as medical
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'medical') {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_pm";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_record'])) {
        // Add new medical record
        $stmt = $conn->prepare("INSERT INTO medical_record (Prisoner_ID, Record_Date, Diagnosis, Treatment, Prescription, Doctor, Next_Checkup, Urgent_Flag) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssi", $_POST['prisoner_id'], $_POST['record_date'], $_POST['diagnosis'], $_POST['treatment'], $_POST['prescription'], $_POST['doctor'], $_POST['next_checkup'], $_POST['urgent_flag']);
        $stmt->execute();
    } elseif (isset($_POST['edit_record'])) {
        // Update existing medical record
        $stmt = $conn->prepare("UPDATE medical_record SET Prisoner_ID=?, Record_Date=?, Diagnosis=?, Treatment=?, Prescription=?, Doctor=?, Next_Checkup=?, Urgent_Flag=? WHERE Medical_ID=?");
        $stmt->bind_param("issssssii", $_POST['prisoner_id'], $_POST['record_date'], $_POST['diagnosis'], $_POST['treatment'], $_POST['prescription'], $_POST['doctor'], $_POST['next_checkup'], $_POST['urgent_flag'], $_POST['medical_id']);
        $stmt->execute();
    }
}

// Fetch all prisoners for dropdown
$prisoners = $conn->query("SELECT Prisoner_ID, Name FROM prisoner");

// Fetch all medical records
$records = $conn->query("SELECT m.*, p.Name as Prisoner_Name FROM medical_record m JOIN prisoner p ON m.Prisoner_ID = p.Prisoner_ID");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Dashboard</title>
    <style>
        html, body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
        }
        
        body {
            background: url('https://www.elevatus.com/wp-content/uploads/2019/07/Central-Regional-2.jpg') no-repeat center center fixed;
            background-size: cover;
            background-attachment: fixed;
            color: #000;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 20px;
            text-align: center;
            width: 100%;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        .container {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.95);
            margin: 30px auto;
            max-width: 95%;
            border-radius: 10px;
            flex: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }

        table, th, td {
            border: 1px solid black;
        }

        th {
            background-color: #333;
            color: white;
            padding: 10px;
        }

        td {
            padding: 8px;
            text-align: center;
        }

        .logout-btn {
            background-color: #444;
            color: white;
            padding: 8px 16px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }

        .logout-btn:hover {
            background-color: #666;
        }

        .form-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 3px solid #f1f1f1;
            z-index: 9;
            background-color: white;
            padding: 20px;
            width: 50%;
            max-width: 600px;
        }

        .form-container {
            max-width: 100%;
        }

        .form-container input, .form-container select, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px 0;
            border: none;
            background: #f1f1f1;
        }

        .form-container .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            width: 100%;
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .form-container .cancel {
            background-color: #f44336;
        }

        .form-container .btn:hover {
            opacity: 1;
        }

        .action-btn {
            padding: 5px 10px;
            margin: 2px;
            border: none;
            cursor: pointer;
            color: white;
            border-radius: 3px;
        }

        .edit-btn {
            background-color: #2196F3;
        }

        .delete-btn {
            background-color: #f44336;
        }

        .add-btn {
            background-color: #4CAF50;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h2>Medical Staff Dashboard</h2>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="container">
    <button class="add-btn" onclick="openForm('add')">Add New Medical Record</button>

    <h3>Medical Records</h3>

    <?php if ($records && $records->num_rows > 0): ?>
        <table>
            <tr>
                <th>Medical ID</th>
                <th>Prisoner</th>
                <th>Record Date</th>
                <th>Diagnosis</th>
                <th>Treatment</th>
                <th>Doctor</th>
                <th>Next Checkup</th>
                <th>Urgent</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $records->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Medical_ID']) ?></td>
                    <td><?= htmlspecialchars($row['Prisoner_Name'] . " (#" . $row['Prisoner_ID'] . ")") ?></td>
                    <td><?= htmlspecialchars($row['Record_Date']) ?></td>
                    <td><?= htmlspecialchars($row['Diagnosis']) ?></td>
                    <td><?= htmlspecialchars($row['Treatment']) ?></td>
                    <td><?= htmlspecialchars($row['Doctor']) ?></td>
                    <td><?= $row['Next_Checkup'] ? htmlspecialchars($row['Next_Checkup']) : "N/A" ?></td>
                    <td><?= $row['Urgent_Flag'] == 1 ? "Yes" : "No" ?></td>
                    <td>
                        <button class="action-btn edit-btn" onclick="openForm('edit', <?= $row['Medical_ID'] ?>, '<?= $row['Prisoner_ID'] ?>', '<?= $row['Record_Date'] ?>', '<?= addslashes($row['Diagnosis']) ?>', '<?= addslashes($row['Treatment']) ?>', '<?= addslashes($row['Prescription']) ?>', '<?= $row['Doctor'] ?>', '<?= $row['Next_Checkup'] ?>', <?= $row['Urgent_Flag'] ?>)">Edit</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No medical records found.</p>
    <?php endif; ?>
</div>

<!-- Add/Edit Form -->
<div id="medicalForm" class="form-popup">
    <form class="form-container" method="POST">
        <h2 id="formTitle">Add Medical Record</h2>
        
        <input type="hidden" id="medical_id" name="medical_id">
        
        <label for="prisoner_id"><b>Prisoner</b></label>
        <select id="prisoner_id" name="prisoner_id" required>
            <?php while ($prisoner = $prisoners->fetch_assoc()): ?>
                <option value="<?= $prisoner['Prisoner_ID'] ?>"><?= htmlspecialchars($prisoner['Name'] . " (#" . $prisoner['Prisoner_ID'] . ")") ?></option>
            <?php endwhile; ?>
        </select>
        
        <label for="record_date"><b>Record Date</b></label>
        <input type="date" id="record_date" name="record_date" required>
        
        <label for="diagnosis"><b>Diagnosis</b></label>
        <textarea id="diagnosis" name="diagnosis" rows="3" required></textarea>
        
        <label for="treatment"><b>Treatment</b></label>
        <textarea id="treatment" name="treatment" rows="3" required></textarea>
        
        <label for="prescription"><b>Prescription</b></label>
        <textarea id="prescription" name="prescription" rows="2"></textarea>
        
        <label for="doctor"><b>Doctor</b></label>
        <input type="text" id="doctor" name="doctor" required>
        
        <label for="next_checkup"><b>Next Checkup Date</b></label>
        <input type="date" id="next_checkup" name="next_checkup">
        
        <label for="urgent_flag"><b>Urgent</b></label>
        <select id="urgent_flag" name="urgent_flag" required>
            <option value="0">No</option>
            <option value="1">Yes</option>
        </select>
        
        <button type="submit" class="btn" name="add_record" id="submitBtn">Add Record</button>
        <button type="button" class="btn cancel" onclick="closeForm()">Cancel</button>
    </form>
</div>

<script>
    function openForm(action, medical_id = null, prisoner_id = null, record_date = null, diagnosis = null, treatment = null, prescription = null, doctor = null, next_checkup = null, urgent_flag = 0) {
        const form = document.getElementById('medicalForm');
        const title = document.getElementById('formTitle');
        const submitBtn = document.getElementById('submitBtn');
        
        if (action === 'add') {
            title.textContent = 'Add Medical Record';
            submitBtn.name = 'add_record';
            submitBtn.textContent = 'Add Record';
            
            // Reset form
            document.getElementById('medical_id').value = '';
            document.getElementById('prisoner_id').value = prisoner_id || '';
            document.getElementById('record_date').value = '';
            document.getElementById('diagnosis').value = '';
            document.getElementById('treatment').value = '';
            document.getElementById('prescription').value = '';
            document.getElementById('doctor').value = '';
            document.getElementById('next_checkup').value = '';
            document.getElementById('urgent_flag').value = '0';
        } else {
            title.textContent = 'Edit Medical Record';
            submitBtn.name = 'edit_record';
            submitBtn.textContent = 'Update Record';
            
            // Fill form with existing data
            document.getElementById('medical_id').value = medical_id;
            document.getElementById('prisoner_id').value = prisoner_id;
            document.getElementById('record_date').value = record_date;
            document.getElementById('diagnosis').value = diagnosis;
            document.getElementById('treatment').value = treatment;
            document.getElementById('prescription').value = prescription;
            document.getElementById('doctor').value = doctor;
            document.getElementById('next_checkup').value = next_checkup;
            document.getElementById('urgent_flag').value = urgent_flag;
        }
        
        form.style.display = 'block';
    }

    function closeForm() {
        document.getElementById('medicalForm').style.display = 'none';
    }
</script>

</body>
</html>

<?php
$conn->close();
?>