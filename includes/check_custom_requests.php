<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Check the structure of the custom_requests table
    echo "Table Structure:<br>";
    $columns = $conn->query("SHOW COLUMNS FROM custom_requests")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'] . "<br>";
    }
    
    echo "<br>Records in the table:<br>";
    $records = $conn->query("SELECT * FROM custom_requests")->fetchAll(PDO::FETCH_ASSOC);
    echo "Total records: " . count($records) . "<br><br>";
    
    foreach ($records as $record) {
        echo "ID: " . $record['id'] . "<br>";
        echo "User ID: " . ($record['user_id'] ?? 'NULL') . "<br>";
        echo "Name: " . $record['name'] . "<br>";
        echo "Email: " . $record['email'] . "<br>";
        echo "Request Details: " . substr($record['request_details'], 0, 50) . "...<br>";
        echo "Status: " . $record['status'] . "<br>";
        echo "Created At: " . $record['created_at'] . "<br><br>";
    }
    
} catch (PDOException $e) {
    echo "Error checking custom requests: " . $e->getMessage();
}
?> 