<?php
/**
 * User Model
 * Handles all database operations related to the users table.
 */
if (!defined('SECURITY_CHECK')) { die('Direct access not allowed.'); }

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds a user by their unique ID.
     */
    public function findById(int $userId): ?array
    {
        try {
            $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("UserModel Error (findById): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Finds a user by their unique username.
     */
    public function findByUsername(string $username): ?array
    {
        try {
            $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':username' => $username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("UserModel Error (findByUsername): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Checks if a user has a specific role.
     */
    public function userHasRole(int $userId, string $roleName): bool
    {
        try {
            $query = "
                SELECT 1 FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :userId AND r.role_name = :roleName
                LIMIT 1
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':userId' => $userId, ':roleName' => $roleName]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            error_log("UserModel Error (userHasRole): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves all users.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, username, email, first_name, last_name, role, is_active, last_login FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new user.
     */
    public function create(array $data): int
    {
        $hashedPassword = PasswordHelper::hashPassword($data['password']);
        $sql = "INSERT INTO users (first_name, last_name, username, email, password_hash, role) 
                VALUES (:first_name, :last_name, :username, :email, :password_hash, :role)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':username'   => $data['username'],
            ':email'      => $data['email'],
            ':password_hash' => $hashedPassword,
            ':role'       => $data['role']
        ]);
        
        return (int)$this->db->lastInsertId();
    }
}