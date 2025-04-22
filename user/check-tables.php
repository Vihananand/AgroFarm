<?php
// Script to check database tables and relationships
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Set content type for better readability
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Structure Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Database Structure Check</h1>
    
    <?php
    // Check database connection
    echo "<h2>Database Connection</h2>";
    if ($conn) {
        echo "<p class='success'>Database connection is working</p>";
    } else {
        echo "<p class='error'>Database connection failed</p>";
        exit;
    }
    
    // Get all tables
    try {
        $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "<h2>Tables in Database</h2>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Check required tables
        $required_tables = ['users', 'orders', 'order_items', 'products'];
        $missing_tables = array_diff($required_tables, $tables);
        
        if (!empty($missing_tables)) {
            echo "<p class='error'>Missing required tables: " . implode(", ", $missing_tables) . "</p>";
        } else {
            echo "<p class='success'>All required tables exist</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>Error listing tables: " . $e->getMessage() . "</p>";
        exit;
    }
    
    // Check each table structure
    $table_checks = [
        'users' => [
            'required_columns' => ['id', 'email', 'password', 'first_name', 'last_name', 'role'],
            'sample_query' => "SELECT id, email, first_name, last_name, role FROM users LIMIT 3"
        ],
        'orders' => [
            'required_columns' => ['id', 'user_id', 'status', 'total_amount', 'created_at'],
            'sample_query' => "SELECT * FROM orders LIMIT 3"
        ],
        'order_items' => [
            'required_columns' => ['id', 'order_id', 'product_id', 'quantity', 'price'],
            'sample_query' => "SELECT * FROM order_items LIMIT 3"
        ],
        'products' => [
            'required_columns' => ['id', 'name', 'price', 'stock'],
            'sample_query' => "SELECT id, name, price, stock FROM products LIMIT 3"
        ]
    ];
    
    foreach ($table_checks as $table => $check) {
        if (in_array($table, $tables)) {
            echo "<h2>Table: $table</h2>";
            
            // Check table structure
            try {
                $columns = $conn->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN);
                
                echo "<h3>Columns</h3>";
                echo "<ul>";
                foreach ($columns as $column) {
                    echo "<li>$column</li>";
                }
                echo "</ul>";
                
                // Check required columns
                $missing_columns = array_diff($check['required_columns'], $columns);
                if (!empty($missing_columns)) {
                    echo "<p class='error'>Missing required columns: " . implode(", ", $missing_columns) . "</p>";
                } else {
                    echo "<p class='success'>All required columns exist</p>";
                }
                
                // Show sample data
                echo "<h3>Sample Data</h3>";
                $data = $conn->query($check['sample_query'])->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($data)) {
                    echo "<p class='warning'>No data found in table</p>";
                } else {
                    echo "<table>";
                    // Table header
                    echo "<tr>";
                    foreach (array_keys($data[0]) as $header) {
                        echo "<th>" . htmlspecialchars($header) . "</th>";
                    }
                    echo "</tr>";
                    
                    // Table rows
                    foreach ($data as $row) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                
            } catch (PDOException $e) {
                echo "<p class='error'>Error checking table structure: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Test specific queries
    echo "<h2>Testing Relationships</h2>";
    
    // Test orders-users relationship
    try {
        $query = "
            SELECT o.id, o.user_id, u.email 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LIMIT 3
        ";
        echo "<h3>Orders-Users Relationship</h3>";
        echo "<p>Query: " . htmlspecialchars($query) . "</p>";
        
        $result = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($result)) {
            echo "<p class='warning'>No matching data found</p>";
        } else {
            echo "<table>";
            echo "<tr>";
            foreach (array_keys($result[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            foreach ($result as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Error testing orders-users relationship: " . $e->getMessage() . "</p>";
    }
    
    // Test order_items-orders relationship
    try {
        $query = "
            SELECT oi.id, oi.order_id, oi.product_id, o.user_id 
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            LIMIT 3
        ";
        echo "<h3>Order Items-Orders Relationship</h3>";
        echo "<p>Query: " . htmlspecialchars($query) . "</p>";
        
        $result = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($result)) {
            echo "<p class='warning'>No matching data found</p>";
        } else {
            echo "<table>";
            echo "<tr>";
            foreach (array_keys($result[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            foreach ($result as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Error testing order_items-orders relationship: " . $e->getMessage() . "</p>";
    }
    
    // Test complete data path
    try {
        $query = "
            SELECT o.id as order_id, o.status, o.total_amount, 
                   u.id as user_id, u.email,
                   p.id as product_id, p.name as product_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.id
            LIMIT 3
        ";
        echo "<h3>Complete Data Path</h3>";
        echo "<p>Query: " . htmlspecialchars($query) . "</p>";
        
        $result = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($result)) {
            echo "<p class='warning'>No complete data path found</p>";
        } else {
            echo "<table>";
            echo "<tr>";
            foreach (array_keys($result[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            foreach ($result as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Error testing complete data path: " . $e->getMessage() . "</p>";
    }
    ?>
    
</body>
</html> 