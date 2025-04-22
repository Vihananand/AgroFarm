<?php
/**
 * Database Connection Class
 * This class handles the connection to the database
 */

class Database {
    private $host = 'localhost';
    private $port = '3307';
    private $db_name = 'agrofarm';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Connect to the database
     * @return PDO connection object
     */
    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}

/**
 * Get database connection
 * @return PDO connection object
 */
function getDBConnection() {
    $database = new Database();
    return $database->connect();
}

// Create a global connection variable
$conn = getDBConnection();
?> 