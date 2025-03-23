<?php
require_once 'Database.php';

class ProjectModel extends Database {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    /**
     * Obtiene los proyectos asociados a un usuario.
     */
    public function getProjectsByUserId($userId) {
        $sql = "SELECT * FROM projects WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo proyecto.
     */
    public function createProject($userId, $name, $description) {
        $sql = "INSERT INTO projects (user_id, name, description) VALUES (:user_id, :name, :description)";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);

        return $stmt->execute();
    }

    /**
     * Elimina un proyecto y sus tareas asociadas.
     */
    public function deleteProject($projectId) {
        // Primero eliminamos las tareas asociadas al proyecto
        $sql = "DELETE FROM tasks WHERE project_id = :project_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':project_id', $projectId);
        $stmt->execute();

        // Luego eliminamos el proyecto
        $sql = "DELETE FROM projects WHERE id = :project_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':project_id', $projectId);

        return $stmt->execute();
    }
}
?>