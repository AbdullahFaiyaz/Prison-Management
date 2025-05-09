<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'officer') {
    header("Location: index.php");
    exit();
}
require 'db.php';

$tables = ['assignment', 'behavior_report', 'cell', 'complaint', 'medical_record', 'prisoner', 'visitor'];
$currentTable = $_GET['table'] ?? 'prisoner';
$addError = $addSuccess = $removeError = $removeSuccess = $editError = $editSuccess = '';

// Get primary key column for a table
function getPrimaryKey($pdo, $table) {
    $stmt = $pdo->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
    return $stmt->fetch(PDO::FETCH_ASSOC)['Column_name'] ?? 'id';
}

// Get next available ID for a table
function getNextId($pdo, $table, $primaryKey) {
    $stmt = $pdo->query("SELECT MAX(`$primaryKey`) as max_id FROM `$table`");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($result['max_id'] ?? 0) + 1;
}

// Get table columns
function getTableColumns($pdo, $table) {
    $stmt = $pdo->query("DESCRIBE `$table`");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle exceptional behavior to update parole eligibility
function updateParoleEligibility($pdo, $prisoner_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as exceptional_count FROM behavior_report 
                          WHERE Prisoner_ID = ? AND Behavior_Type = 'Exceptional'");
    $stmt->execute([$prisoner_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['exceptional_count'] > 0) {
        $pdo->prepare("UPDATE prisoner SET Parole_Eligibility = 1 WHERE Prisoner_ID = ?")
            ->execute([$prisoner_id]);
    }
}

// Handle form submission for adding records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_record'])) {
    $primaryKey = getPrimaryKey($pdo, $currentTable);
    $columns = getTableColumns($pdo, $currentTable);
    
    $params = [];
    $values = [];
    $placeholders = [];
    
    foreach ($columns as $column) {
        $field = $column['Field'];
        if ($field === $primaryKey && $column['Extra'] === 'auto_increment') {
            continue;
        }
        
        if ($currentTable === 'prisoner' && $field === 'Photo') {
            continue;
        }
        
        if (isset($_POST[$field])) {
            $value = $_POST[$field];
            if (strpos($column['Type'], 'int') !== false) {
                $value = intval($value);
            } elseif (strpos($column['Type'], 'float') !== false || 
                     strpos($column['Type'], 'decimal') !== false) {
                $value = floatval($value);
            } elseif (strpos($column['Type'], 'date') !== false || 
                      strpos($column['Type'], 'time') !== false) {
                $value = $value ?: null;
            } else {
                $value = trim($value);
            }
            
            $params[$field] = $value;
        } elseif ($column['Null'] === 'YES') {
            $params[$field] = null;
        }
    }
    
    try {
        $columnsStr = implode(', ', array_map(function($col) { return "`$col`"; }, array_keys($params)));
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $values = array_values($params);
        
        $stmt = $pdo->prepare("INSERT INTO `$currentTable` ($columnsStr) VALUES ($placeholders)");
        if ($stmt->execute($values)) {
            $addSuccess = "Record added successfully to $currentTable table.";
            
            if ($currentTable === 'behavior_report' && isset($params['Behavior_Type']) && 
                $params['Behavior_Type'] === 'Exceptional' && isset($params['Prisoner_ID'])) {
                updateParoleEligibility($pdo, $params['Prisoner_ID']);
            }
        } else {
            $addError = "Failed to add record to $currentTable table.";
        }
    } catch (PDOException $e) {
        $addError = "Error: " . $e->getMessage();
    }
}

// Handle record removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_record'])) {
    $primaryKey = getPrimaryKey($pdo, $currentTable);
    $id = $_POST['record_id'] ?? null;
    
    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM `$currentTable` WHERE `$primaryKey` = ?");
            if ($stmt->execute([$id])) {
                $removeSuccess = "Record with $primaryKey $id removed successfully from $currentTable table.";
            } else {
                $removeError = "Failed to remove record from $currentTable table.";
            }
        } catch (PDOException $e) {
            $removeError = "Error: " . $e->getMessage();
        }
    } else {
        $removeError = "Record ID is required.";
    }
}

// Handle record editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_record'])) {
    $primaryKey = getPrimaryKey($pdo, $currentTable);
    $id = $_POST['record_id'] ?? null;
    
    if ($id) {
        $columns = getTableColumns($pdo, $currentTable);
        $params = [];
        $setParts = [];
        
        foreach ($columns as $column) {
            $field = $column['Field'];
            if ($field === $primaryKey) {
                continue;
            }
            
            if ($currentTable === 'prisoner' && $field === 'Photo') {
                continue;
            }
            
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                if (strpos($column['Type'], 'int') !== false) {
                    $value = intval($value);
                } elseif (strpos($column['Type'], 'float') !== false || 
                         strpos($column['Type'], 'decimal') !== false) {
                    $value = floatval($value);
                } elseif (strpos($column['Type'], 'date') !== false || 
                          strpos($column['Type'], 'time') !== false) {
                    $value = $value ?: null;
                } else {
                    $value = trim($value);
                }
                
                $params[$field] = $value;
                $setParts[] = "`$field` = ?";
            }
        }
        
        try {
            $params[] = $id;
            $setClause = implode(', ', $setParts);
            $stmt = $pdo->prepare("UPDATE `$currentTable` SET $setClause WHERE `$primaryKey` = ?");
            
            if ($stmt->execute(array_values($params))) {
                $editSuccess = "Record updated successfully in $currentTable table.";
                
                if ($currentTable === 'behavior_report' && isset($params['Behavior_Type']) && 
                    $params['Behavior_Type'] === 'Exceptional' && isset($params['Prisoner_ID'])) {
                    updateParoleEligibility($pdo, $params['Prisoner_ID']);
                }
            } else {
                $editError = "Failed to update record in $currentTable table.";
            }
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            error_log("SQL Query: UPDATE `$currentTable` SET $setClause WHERE `$primaryKey` = ?");
            error_log("Parameters: " . print_r($params, true));
            $editError = "Error: " . $e->getMessage();
        }
    } else {
        $editError = "Record ID is required.";
    }
}

// Get record for editing
$editRecord = null;
if (isset($_GET['edit'])) {
    $primaryKey = getPrimaryKey($pdo, $currentTable);
    $id = $_GET['edit'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM `$currentTable` WHERE `$primaryKey` = ?");
        $stmt->execute([$id]);
        $editRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $editError = "Error fetching record: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Officer Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }
        .header {
            background: #333;
            color: white;
            padding: 15px 20px;
            text-align: center;
        }
        .logout {
            color: white;
            text-decoration: none;
            float: right;
        }
        .container {
            padding: 20px;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
        }
        .tab {
            padding: 10px 20px;
            background: #ddd;
            margin-right: 5px;
            cursor: pointer;
            border-radius: 5px 5px 0 0;
        }
        .tab.active {
            background: #fff;
            border: 1px solid #ccc;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }
        .tab-content {
            display: none;
            background: white;
            padding: 20px;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .tab-content.active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .action-link {
            color: #0066cc;
            text-decoration: none;
            margin-right: 10px;
        }
        .action-link:hover {
            text-decoration: underline;
        }
        .form-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-danger {
            background: #f44336;
        }
        .btn-info {
            background: #2196F3;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .add-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        .unit-label {
            font-size: 0.8em;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>Officer Dashboard</h2>
    <a class="logout" href="logout.php">Logout</a>
</div>

<div class="container">
    <div class="tabs">
        <?php foreach ($tables as $table): ?>
            <div class="tab <?= $currentTable === $table ? 'active' : '' ?>" 
                 onclick="window.location.href='officer.php?table=<?= $table ?>'">
                <?= ucfirst(str_replace('_', ' ', $table)) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="tab-content active">
        <?php if ($addSuccess): ?><p class="success"><?= htmlspecialchars($addSuccess) ?></p><?php endif; ?>
        <?php if ($addError): ?><p class="error"><?= htmlspecialchars($addError) ?></p><?php endif; ?>
        <?php if ($removeSuccess): ?><p class="success"><?= htmlspecialchars($removeSuccess) ?></p><?php endif; ?>
        <?php if ($removeError): ?><p class="error"><?= htmlspecialchars($removeError) ?></p><?php endif; ?>
        <?php if ($editSuccess): ?><p class="success"><?= htmlspecialchars($editSuccess) ?></p><?php endif; ?>
        <?php if ($editError): ?><p class="error"><?= htmlspecialchars($editError) ?></p><?php endif; ?>

        <!-- Add/Edit Record Form -->
        <div class="form-section">
            <h3><?= isset($editRecord) ? 'Edit' : 'Add New' ?> Record to <?= ucfirst(str_replace('_', ' ', $currentTable)) ?></h3>
            <form method="post" class="add-form">
                <?php if (isset($editRecord)): ?>
                    <input type="hidden" name="record_id" value="<?= $editRecord[getPrimaryKey($pdo, $currentTable)] ?>">
                <?php endif; ?>
                
                <?php
                $primaryKey = getPrimaryKey($pdo, $currentTable);
                $columns = getTableColumns($pdo, $currentTable);
                
                foreach ($columns as $column): 
                    if ($column['Field'] === $primaryKey && strpos($column['Extra'], 'auto_increment') !== false) {
                        continue;
                    }
                    
                    if ($currentTable === 'prisoner' && $column['Field'] === 'Photo') {
                        continue;
                    }
                    
                    $required = $column['Null'] === 'NO' && $column['Default'] === null && 
                               strpos($column['Extra'], 'auto_increment') === false;
                    
                    $value = isset($editRecord) ? $editRecord[$column['Field']] : '';
                    
                    // Special handling for prisoner table fields
                    if ($currentTable === 'prisoner'):
                ?>
                    <div class="form-group">
                        <?php if ($column['Field'] === 'Gender'): ?>
                            <label>Gender: <span style="color:red">*</span></label>
                            <select name="Gender" required>
                                <option value="">-- Select --</option>
                                <option value="Male" <?= $value === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $value === 'Female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        
                        <?php elseif ($column['Field'] === 'Age'): ?>
                            <label>Age: <span style="color:red">*</span></label>
                            <input type="number" name="Age" min="18" max="100" value="<?= $value ?>" required>
                            <span class="unit-label">(years)</span>
                        
                        <?php elseif ($column['Field'] === 'Weight_kg'): ?>
                            <label>Weight: <?= $required ? '<span style="color:red">*</span>' : '' ?></label>
                            <input type="number" step="0.1" name="Weight_kg" min="30" max="300" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                            <span class="unit-label">(kg)</span>
                        
                        <?php elseif ($column['Field'] === 'Height_cm'): ?>
                            <label>Height: <?= $required ? '<span style="color:red">*</span>' : '' ?></label>
                            <input type="number" step="0.1" name="Height_cm" min="100" max="250" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                            <span class="unit-label">(cm)</span>
                        
                        <?php elseif ($column['Field'] === 'Sentence_Duration'): ?>
                            <label>Sentence Duration: <span style="color:red">*</span></label>
                            <input type="number" step="0.1" name="Sentence_Duration" min="0.1" max="100" value="<?= $value ?>" required>
                            <span class="unit-label">(years)</span>
                        
                        <?php elseif ($column['Field'] === 'Cell_ID'): ?>
                            <label>Cell ID: <span style="color:red">*</span></label>
                            <select name="Cell_ID" required>
                                <option value="">-- Select Cell --</option>
                                <?php
                                $cells = $pdo->query("SELECT Cell_ID FROM cell")->fetchAll(PDO::FETCH_COLUMN);
                                foreach ($cells as $cell_id):
                                ?>
                                    <option value="<?= $cell_id ?>" <?= $value == $cell_id ? 'selected' : '' ?>><?= $cell_id ?></option>
                                <?php endforeach; ?>
                            </select>
                        
                        <?php else: ?>
                            <label><?= ucfirst(str_replace('_', ' ', $column['Field'])) ?>: 
                                <?php if ($required): ?><span style="color:red">*</span><?php endif; ?>
                            </label>
                            
                            <?php if (strpos($column['Type'], 'enum') !== false): 
                                $options = explode("','", substr($column['Type'], 6, -2)); ?>
                                <select name="<?= $column['Field'] ?>" <?= $required ? 'required' : '' ?>>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($options as $option): ?>
                                        <option value="<?= $option ?>" <?= $value === $option ? 'selected' : '' ?>><?= $option ?></option>
                                    <?php endforeach; ?>
                                </select>
                            
                            <?php elseif (strpos($column['Type'], 'int') !== false): ?>
                                <input type="number" name="<?= $column['Field'] ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                            
                            <?php elseif (strpos($column['Type'], 'float') !== false || 
                                         strpos($column['Type'], 'decimal') !== false): ?>
                                <input type="number" step="0.01" name="<?= $column['Field'] ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                            
                            <?php elseif (strpos($column['Type'], 'date') !== false): ?>
                                <input type="date" name="<?= $column['Field'] ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                            
                            <?php elseif (strpos($column['Type'], 'time') !== false): ?>
                                <input type="time" name="<?= $column['Field'] ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                            
                            <?php elseif (strpos($column['Type'], 'text') !== false): ?>
                                <textarea name="<?= $column['Field'] ?>" rows="3" <?= $required ? 'required' : '' ?>><?= $value ?></textarea>
                            
                            <?php else: ?>
                                <input type="text" name="<?= $column['Field'] ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Default form fields for other tables -->
                    <div class="form-group">
                        <label><?= ucfirst(str_replace('_', ' ', $column['Field'])) ?>: 
                            <?php if ($required): ?><span style="color:red">*</span><?php endif; ?>
                        </label>
                        
                        <?php if (strpos($column['Type'], 'enum') !== false): 
                            $options = explode("','", substr($column['Type'], 6, -2)); ?>
                            <select name="<?= $column['Field'] ?>" <?= $required ? 'required' : '' ?>>
                                <option value="">-- Select --</option>
                                <?php foreach ($options as $option): ?>
                                    <option value="<?= $option ?>" <?= $value === $option ? 'selected' : '' ?>><?= $option ?></option>
                                <?php endforeach; ?>
                            </select>
                        
                        <?php elseif (strpos($column['Type'], 'int') !== false): ?>
                            <input type="number" name="<?= $column['Field'] ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                        
                        <?php elseif (strpos($column['Type'], 'float') !== false || 
                                     strpos($column['Type'], 'decimal') !== false): ?>
                            <input type="number" step="0.01" name="<?= $column['Field'] ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                        
                        <?php elseif (strpos($column['Type'], 'date') !== false): ?>
                            <input type="date" name="<?= $column['Field'] ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                        
                        <?php elseif (strpos($column['Type'], 'time') !== false): ?>
                            <input type="time" name="<?= $column['Field'] ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                        
                        <?php elseif (strpos($column['Type'], 'text') !== false): ?>
                            <textarea name="<?= $column['Field'] ?>" rows="3" <?= $required ? 'required' : '' ?>><?= $value ?></textarea>
                        
                        <?php else: ?>
                            <input type="text" name="<?= $column['Field'] ?>" value="<?= $value ?>" <?= $required ? 'required' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php endforeach; ?>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <?php if (isset($editRecord)): ?>
                        <button type="submit" name="edit_record" class="btn btn-info">Update Record</button>
                        <a href="officer.php?table=<?= $currentTable ?>" class="btn">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_record" class="btn">Add Record</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Remove Record Form -->
        <div class="form-section">
            <h3>Remove Record from <?= ucfirst(str_replace('_', ' ', $currentTable)) ?></h3>
            <form method="post">
                <div class="form-group">
                    <label><?= ucfirst(str_replace('_', ' ', $primaryKey)) ?>:</label>
                    <input type="text" name="record_id" required>
                </div>
                <button type="submit" name="remove_record" class="btn btn-danger">Remove Record</button>
            </form>
        </div>

        <!-- Table Display Section -->
        <?php
        $stmt = $pdo->query("SELECT * FROM `$currentTable`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            echo "<p>No data found in $currentTable table.</p>";
        } else {
            // Filter out the 'Photo' column if displaying the prisoner table
            $columnsToDisplay = array_keys($rows[0]);
            if ($currentTable === 'prisoner') {
                $columnsToDisplay = array_filter($columnsToDisplay, function($col) {
                    return $col !== 'Photo';
                });
            }
            
            echo "<table><tr>";
            // Table headers
            foreach ($columnsToDisplay as $colName) {
                echo "<th>" . htmlspecialchars($colName) . "</th>";
            }
            echo "<th>Actions</th></tr>";
            
            // Table rows
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $col => $val) {
                    if ($currentTable === 'prisoner' && $col === 'Photo') {
                        continue;
                    }
                    if (in_array($col, $columnsToDisplay)) {
                        echo "<td>" . htmlspecialchars($val) . "</td>";
                    }
                }
                echo "<td>
                        <a href='officer.php?table=$currentTable&edit=" . $row[getPrimaryKey($pdo, $currentTable)] . "' class='action-link'>Edit</a>
                      </td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </div>
</div>

</body>
</html>