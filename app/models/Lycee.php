<?php

class Lycee {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $this->db->query("SELECT * FROM lycees ORDER BY nom_lycee ASC");
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM lycees WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    public function add($data) {
        $this->db->query("INSERT INTO lycees (nom_lycee, description) VALUES (:nom_lycee, :description)");
        $this->db->bind(':nom_lycee', $data['nom_lycee']);
        $this->db->bind(':description', $data['description'] ?? null);
        return $this->db->execute();
    }

    public function update($id, $data) {
        $this->db->query("UPDATE lycees SET nom_lycee = :nom_lycee, description = :description WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        $this->db->bind(':nom_lycee', $data['nom_lycee']);
        $this->db->bind(':description', $data['description'] ?? null);
        return $this->db->execute();
    }

    public function delete($id) {
        $id = (int)$id;
        // Vérifier si le lycée est utilisé par des élèves
        $this->db->query("SELECT COUNT(*) as count FROM eleves WHERE lycee_id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) {
            // Il serait préférable d'utiliser une clé de traduction ici
            $_SESSION['error_message'] = 'Ce lycée est associé à des élèves et ne peut être supprimé.';
            return false;
        }

        // Vérifier si le lycée est utilisé dans les assignations de centres
        $this->db->query("SELECT COUNT(*) as count FROM centres_assignations WHERE lycee_id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) {
            $_SESSION['error_message'] = 'Ce lycée est assigné à des centres d\'examen et ne peut être supprimé. Veuillez d\'abord retirer ses assignations.';
            return false;
        }

        $this->db->query("DELETE FROM lycees WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Vérifie si un nom de lycée existe déjà.
     * @param string $nomLycee
     * @param int|null $currentId ID du lycée actuel à exclure (pour la mise à jour)
     * @return bool
     */
    public function nomExists($nomLycee, $currentId = null) {
        $sql = "SELECT id FROM lycees WHERE nom_lycee = :nom_lycee";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':nom_lycee', $nomLycee);
        if ($currentId !== null) {
            $this->db->bind(':current_id', (int)$currentId);
        }
        return $this->db->single() ? true : false;
    }
}
?>
