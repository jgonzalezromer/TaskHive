<?php
require_once 'Database.php';

class ProjectModel extends Database {
    public function getProjectsByUserId($userId) {
        $sql = "SELECT * FROM projects WHERE user_id = :user_id";
        $stmt = $this->query($sql, [':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createProject($userId, $name, $description) {
        $sql = "INSERT INTO projects (user_id, name, description) VALUES (:user_id, :name, :description)";
        $this->query($sql, [
            ':user_id' => $userId,
            ':name' => $name,
            ':description' => $description
        ]);
    }
}
?>