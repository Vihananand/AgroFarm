<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Custom Request Test";
$page_description = "Testing custom requests functionality";

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if table needs to be dropped and recreated
if (isset($_GET['recreate']) && $_GET['recreate'] === 'yes') {
    try {
        // Drop existing table
        $conn->exec("DROP TABLE IF EXISTS custom_requests");
        echo "<div style='color: green; font-weight: bold;'>Existing custom_requests table dropped.</div>";
        
        // Create the custom_requests table
        $sql = "CREATE TABLE `custom_requests` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NULL DEFAULT NULL,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(50) NULL DEFAULT NULL,
            `request_details` TEXT NOT NULL,
            `status` ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
        echo "<div style='color: green; font-weight: bold;'>Custom requests table created successfully</div>";
        
        // Insert a test record
        $stmt = $conn->prepare("INSERT INTO custom_requests (name, email, phone, request_details, status) 
                              VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute([
            'Test User',
            'test@example.com',
            '555-123-4567',
            'This is a test request for organic fertilizer. Please help me find a supplier for my farm.',
            'pending'
        ]);
        
        echo "<div style='color: green; font-weight: bold;'>Test record inserted.</div>";
    } catch (PDOException $e) {
        echo "<div style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</div>";
    }
}

// Check table existence and display table information
try {
    $tableExists = $conn->query("SHOW TABLES LIKE 'custom_requests'")->rowCount() > 0;
    
    if ($tableExists) {
        echo "<h2>Table Structure</h2>";
        $columns = $conn->query("SHOW COLUMNS FROM custom_requests")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . ($value === null ? 'NULL' : htmlspecialchars($value)) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h2>Table Records</h2>";
        $records = $conn->query("SELECT * FROM custom_requests")->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($records) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr>";
            foreach (array_keys($records[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            foreach ($records as $record) {
                echo "<tr>";
                foreach ($record as $value) {
                    echo "<td>" . ($value === null ? 'NULL' : htmlspecialchars($value)) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div style='color: orange; font-weight: bold;'>No records found in the custom_requests table.</div>";
        }
    } else {
        echo "<div style='color: red; font-weight: bold;'>The custom_requests table does not exist!</div>";
    }
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</div>";
}

// Test simple query that matches the history page
echo "<h2>Testing Query from History Page</h2>";
try {
    $offset = 0;
    $records_per_page = 10;
    $sql = "SELECT * FROM custom_requests ORDER BY created_at DESC LIMIT $offset, $records_per_page";
    $result = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='color: green; font-weight: bold;'>Query executed successfully. Found " . count($result) . " records.</div>";
    
    if (count($result) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>";
        foreach (array_keys($result[0]) as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr>";
        
        foreach ($result as $record) {
            echo "<tr>";
            foreach ($record as $value) {
                echo "<td>" . ($value === null ? 'NULL' : htmlspecialchars($value)) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>Error running test query: " . $e->getMessage() . "</div>";
}
?>

<h2>Actions</h2>
<ul>
    <li><a href="?recreate=yes">Drop and recreate the custom_requests table with test data</a></li>
    <li><a href="custom-request-history.php">Go to Custom Request History page</a></li>
    <li><a href="custom-request.php">Go to Custom Request Form</a></li>
</ul> 