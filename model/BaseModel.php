<?php
// filepath: c:\xampp\htdocs\MyLibrary\model\BaseModel.php
abstract class BaseModel {
    protected $conn;
    protected $table;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Prevent SQL Injection with prepared statements
    protected function executeQuery($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            return false;
        }
    }

    // Sanitize input to prevent XSS
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    // Validate email
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Validate integer
    protected function validateInt($value) {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    // Validate float
    protected function validateFloat($value) {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    // Abstract method - must be implemented by child classes
    abstract public function validate($data);
}
?>