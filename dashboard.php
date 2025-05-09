<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}
require 'db.php';

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Handle prisoner search
$searchResults = [];
if (isset($_GET['search_query']) && trim($_GET['search_query']) !== '') {
    $query = trim($_GET['search_query']);
    $stmt = $pdo->prepare("SELECT * FROM prisoner WHERE Prisoner_ID = ?");
    $stmt->execute([$query]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle add row request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_row'])) {
    $table = $_POST['table'];
    if ($table !== 'user') {
        try {
            // Get column information
            $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table`");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $data = [];
            foreach ($columns as $col) {
                $field = $col['Field'];
                if ($col['Key'] !== 'PRI' && isset($_POST[$field]) && $field !== 'photo') {
                    $data[$field] = $_POST[$field];
                }
            }
            
            if (!empty($data)) {
                $columns = implode(", ", array_map(function($col) {
                    return "`$col`";
                }, array_keys($data)));
                
                $values = ":" . implode(", :", array_keys($data));
                
                $stmt = $pdo->prepare("INSERT INTO `$table` ($columns) VALUES ($values)");
                $stmt->execute($data);
                header("Location: dashboard.php?success=Row added successfully");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error adding row: " . $e->getMessage();
        }
    } else {
        $error = "Cannot add rows to the user table";
    }
}

// Handle delete row request
if (isset($_POST['delete_row'])) {
    $table = $_POST['delete_table'];
    $id = $_POST['delete_id'];
    
    if ($table !== 'user') {
        try {
            // Get primary key column name
            $stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
            $primaryKeyInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            $primaryKey = $primaryKeyInfo ? $primaryKeyInfo['Column_name'] : 'id';
            
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$primaryKey` = ?");
            $stmt->execute([$id]);
            header("Location: dashboard.php?success=Row deleted successfully");
            exit();
        } catch (PDOException $e) {
            $error = "Error deleting row: " . $e->getMessage();
        }
    } else {
        $error = "Cannot delete rows from the user table";
    }
}

// Handle inline edit request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_row'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];
    
    try {
        // Get primary key column name
        $stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
        $primaryKeyInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        $primaryKey = $primaryKeyInfo ? $primaryKeyInfo['Column_name'] : 'id';
        
        // Get column information
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table`");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [];
        $setParts = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($col['Key'] !== 'PRI' && isset($_POST[$field]) && $field !== 'photo') {
                $data[$field] = $_POST[$field];
                $setParts[] = "`$field` = :$field";
            }
        }
        
        if (!empty($data)) {
            $data[$primaryKey] = $id;
            $setClause = implode(", ", $setParts);
            
            $stmt = $pdo->prepare("UPDATE `$table` SET $setClause WHERE `$primaryKey` = :$primaryKey");
            $stmt->execute($data);
            header("Location: dashboard.php?success=Row updated successfully");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Error updating row: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= ucfirst($_SESSION['role']) ?> Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://media.istockphoto.com/id/1617311621/photo/sun-and-shadows-form-on-the-bars-of-a-prison.jpg?s=612x612&w=0&k=20&c=ZV4_YjGudzZfK13sSIZ7vLLxtQK4Muwmk0TbiHh-Jmk=') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        .overlay {
            background: rgba(255, 255, 255, 0.97);
            padding: 30px;
            min-height: 100vh;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
            border-radius: 10px;
            max-width: 1200px;
            margin: 0 auto;
        }
        h2, h3, h4 {
            margin-bottom: 10px;
        }
        a.logout {
            display: inline-block;
            margin-bottom: 20px;
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
        .section {
            margin-bottom: 40px;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        .table-button {
            background-color: #007bff;
            color: white;
            padding: 10px 18px;
            margin: 5px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }
        .table-button:hover {
            background-color: #0056b3;
        }
        .table-container {
            display: none;
            margin-top: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: auto;
        }
        table, th, td {
            border: 1px solid #aaa;
        }
        th, td {
            padding: 10px;
            text-align: left;
            white-space: nowrap;
        }
        th {
            background-color: #f0f0f0;
        }
        .empty-msg {
            color: #777;
            font-style: italic;
        }
        .add-form, .delete-form, .edit-form {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .add-form input, .add-form select, .delete-form input, .edit-form input, .edit-form select {
            padding: 8px;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .add-form button, .delete-form button, .edit-form button {
            padding: 8px 15px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .add-form button {
            background-color: #28a745;
        }
        .delete-form button {
            background-color: #dc3545;
        }
        .edit-form button {
            background-color: #17a2b8;
        }
        .error {
            color: red;
            margin: 10px 0;
        }
        .success {
            color: green;
            margin: 10px 0;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .form-row label {
            width: 150px;
            margin-right: 10px;
        }
        .form-row input, .form-row select {
            flex: 1;
            min-width: 200px;
        }
        .edit-btn {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .edit-btn:hover {
            background-color: #138496;
        }
    </style>
    <script>
        function toggleTable(id) {
            const containers = document.querySelectorAll('.table-container');
            containers.forEach(c => {
                if (c.id !== id) c.style.display = 'none';
            });
            const section = document.getElementById(id);
            section.style.display = section.style.display === 'block' ? 'none' : 'block';
        }
        
        function showAddForm(table) {
            document.getElementById('add-form-' + table).style.display = 'block';
            document.getElementById('edit-form-' + table).style.display = 'none';
        }
        
        function showDeleteForm(table) {
            document.getElementById('delete-form-' + table).style.display = 'block';
        }
        
        function showEditForm(table, id) {
            // Hide all other forms
            document.getElementById('add-form-' + table).style.display = 'none';
            document.getElementById('delete-form-' + table).style.display = 'none';
            
            // Show edit form
            const editForm = document.getElementById('edit-form-' + table);
            editForm.style.display = 'block';
            
            // Scroll to the form
            editForm.scrollIntoView({ behavior: 'smooth' });
            
            // Set the ID in the form
            document.querySelector('#edit-form-' + table + ' input[name="id"]').value = id;
        }
    </script>
</head>
<body>
<div class="overlay">
    <h2><?= ucfirst($_SESSION['role']) ?> Dashboard</h2>
    <a class="logout" href="logout.php">Logout</a>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <!-- Search Section -->
    <div class="section">
        <form method="GET" action="">
            <label for="search_query"><strong>Search Prisoners (by ID only):</strong></label>
            <input type="text" id="search_query" name="search_query" value="<?= isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : '' ?>" placeholder="Enter prisoner ID..." style="padding: 8px; width: 300px; margin-right: 10px;">
            <button type="submit" style="padding: 8px 15px; background-color: #28a745; color: white; border: none; border-radius: 5px;">Search</button>
        </form>
    </div>

    <!-- Search Results Display -->
    <?php if (!empty($searchResults)): ?>
        <div class="section">
            <h3>Search Results (Prisoners)</h3>
            <table>
                <thead>
                    <tr>
                        <?php foreach (array_keys($searchResults[0]) as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchResults as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars($value) ?></td>
                            <?php endforeach; ?>
                            <td>
                                <button class="edit-btn" onclick="showEditForm('prisoner', <?= htmlspecialchars($row['Prisoner_ID']) ?>)">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif (isset($_GET['search_query'])): ?>
        <div class="section">
            <p class="empty-msg">No prisoners found with ID "<strong><?= htmlspecialchars($_GET['search_query']) ?></strong>".</p>
        </div>
    <?php endif; ?>

    <!-- Available Tables Section -->
    <div class="section">
        <h3>Available Tables</h3>
        <?php foreach ($tables as $index => $table): ?>
            <button class="table-button" onclick="toggleTable('table_<?= $index ?>')">
                <?= htmlspecialchars($table) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($tables as $index => $table): ?>
    <div class="section table-container" id="table_<?= $index ?>">
        <h3>Table: <?= htmlspecialchars($table) ?></h3>
        
        <?php if ($table !== 'user'): ?>
            <button onclick="showAddForm('<?= $index ?>')" style="margin-bottom: 15px; margin-right: 10px;">Add New Row</button>
            <button onclick="showDeleteForm('<?= $index ?>')" style="margin-bottom: 15px; background-color: #dc3545; margin-right: 10px;">Delete Row</button>
            
            <!-- Add Form -->
            <div id="add-form-<?= $index ?>" class="add-form" style="display: none;">
                <h4>Add New Row to <?= htmlspecialchars($table) ?></h4>
                <form method="POST" action="">
                    <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                    <?php
                    // Get column information
                    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table`");
                    $stmt->execute();
                    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($columns as $col) {
                        if ($col['Key'] !== 'PRI' && $col['Field'] !== 'photo') { // Skip primary key and photo
                            echo '<div class="form-row">';
                            echo '<label>' . htmlspecialchars($col['Field']) . ':</label>';
                            
                            // Special handling for gender field
                            if (strtolower($col['Field']) === 'gender') {
                                echo '<select name="' . htmlspecialchars($col['Field']) . '"';
                                if ($col['Null'] === 'NO' && $col['Default'] === null) {
                                    echo ' required';
                                }
                                echo '>';
                                echo '<option value="Male">Male</option>';
                                echo '<option value="Female">Female</option>';
                                echo '</select>';
                            }
                            // Handle enum fields
                            elseif (strpos($col['Type'], 'enum') !== false) {
                                preg_match("/enum\('(.+)'\)/", $col['Type'], $matches);
                                $options = explode("','", $matches[1]);
                                echo '<select name="' . htmlspecialchars($col['Field']) . '"';
                                if ($col['Null'] === 'NO' && $col['Default'] === null) {
                                    echo ' required';
                                }
                                echo '>';
                                foreach ($options as $option) {
                                    echo '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>';
                                }
                                echo '</select>';
                            }
                            // Handle date fields
                            elseif (strpos($col['Type'], 'date') !== false) {
                                echo '<input type="date" name="' . htmlspecialchars($col['Field']) . '"';
                                if ($col['Null'] === 'NO' && $col['Default'] === null) {
                                    echo ' required';
                                }
                                echo '>';
                            }
                            // Handle numeric fields
                            elseif (strpos($col['Type'], 'int') !== false || strpos($col['Type'], 'decimal') !== false) {
                                echo '<input type="number" name="' . htmlspecialchars($col['Field']) . '"';
                                if ($col['Null'] === 'NO' && $col['Default'] === null) {
                                    echo ' required';
                                }
                                echo '>';
                            }
                            // Default to text input
                            else {
                                echo '<input type="text" name="' . htmlspecialchars($col['Field']) . '"';
                                if ($col['Null'] === 'NO' && $col['Default'] === null) {
                                    echo ' required';
                                }
                                echo '>';
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                    <button type="submit" name="add_row">Add Row</button>
                </form>
            </div>
            
            <!-- Delete Form -->
            <div id="delete-form-<?= $index ?>" class="delete-form" style="display: none;">
                <h4>Delete Row from <?= htmlspecialchars($table) ?></h4>
                <form method="POST" action="">
                    <input type="hidden" name="delete_table" value="<?= htmlspecialchars($table) ?>">
                    <div class="form-row">
                        <label>Primary Key ID:</label>
                        <input type="text" name="delete_id" required>
                    </div>
                    <button type="submit" name="delete_row">Delete Row</button>
                </form>
            </div>
            
            <!-- Edit Form -->
            <div id="edit-form-<?= $index ?>" class="edit-form" style="display: none;">
                <h4>Edit Row in <?= htmlspecialchars($table) ?></h4>
                <form method="POST" action="">
                    <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                    <input type="hidden" name="id" id="edit-id-<?= $index ?>">
                    <input type="hidden" name="edit_row" value="1">
                    <?php
                    // Get column information
                    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table`");
                    $stmt->execute();
                    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($columns as $col) {
                        if ($col['Field'] !== 'photo') { // Skip photo field
                            echo '<div class="form-row">';
                            echo '<label>' . htmlspecialchars($col['Field']) . ':</label>';
                            
                            // Special handling for gender field
                            if (strtolower($col['Field']) === 'gender') {
                                echo '<select name="' . htmlspecialchars($col['Field']) . '"';
                                if ($col['Null'] === 'NO' && $col['Default'] === null) {
                                    echo ' required';
                                }
                                echo '>';
                                echo '<option value="Male">Male</option>';
                                echo '<option value="Female">Female</option>';
                                echo '</select>';
                            }
                            // Handle enum fields
                            elseif (strpos($col['Type'], 'enum') !== false) {
                                preg_match("/enum\('(.+)'\)/", $col['Type'], $matches);
                                $options = explode("','", $matches[1]);
                                echo '<select name="' . htmlspecialchars($col['Field']) . '"';
                                if ($col['Null'] === 'NO' && $col['Default'] === null) {
                                    echo ' required';
                                }
                                echo '>';
                                foreach ($options as $option) {
                                    echo '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>';
                                }
                                echo '</select>';
                            }
                            // Handle date fields
                            elseif (strpos($col['Type'], 'date') !== false) {
                                echo '<input type="date" name="' . htmlspecialchars($col['Field']) . '"';
                                if ($col['Null'] === 'NO' && $col['Default'] === null) {
                                    echo ' required';
                                }
                                echo '>';
                            }
                            // Handle numeric fields
                            elseif (strpos($col['Type'], 'int') !== false || strpos($col['Type'], 'decimal') !== false) {
                                echo '<input type="number" name="' . htmlspecialchars($col['Field']) . '"';
                                if ($col['Null'] === 'NO' && $col['Default'] === null) {
                                    echo ' required';
                                }
                                echo '>';
                            }
                            // Default to text input
                            else {
                                echo '<input type="text" name="' . htmlspecialchars($col['Field']) . '"';
                                if ($col['Null'] === 'NO' && $col['Default'] === null) {
                                    echo ' required';
                                }
                                echo '>';
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                    <button type="submit">Update Row</button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get the primary key column name for this table
        $primaryKey = 'id'; // default fallback
        $stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
        $primaryKeyInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($primaryKeyInfo) {
            $primaryKey = $primaryKeyInfo['Column_name'];
        }

        if (empty($rows)) {
            echo "<p class='empty-msg'>This table has no data.</p>";
        } else {
            echo "<table><tr>";
            // Display all columns except 'photo'
            foreach (array_keys($rows[0]) as $colName) {
                if ($colName !== 'photo') {
                    echo "<th>" . htmlspecialchars($colName) . "</th>";
                }
            }
            echo "<th>Actions</th></tr>";

            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $colName => $value) {
                    if ($colName !== 'photo') {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                }
                $id = $row[$primaryKey]; // Now using the correct primary key
                echo "<td>
                        <button class=\"edit-btn\" onclick=\"showEditForm('$index', " . htmlspecialchars($id) . ")\">Edit</button>
                      </td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </div>
    <?php endforeach; ?>
</div>
</body>
</html>