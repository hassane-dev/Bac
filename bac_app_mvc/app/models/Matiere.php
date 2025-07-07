<?php

class Matiere {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les matières.
     * @return array
     */
    public function getAll() {
        $this->db->query("SELECT * FROM matieres ORDER BY code ASC");
        return $this->db->resultSet();
    }

    /**
     * Récupère une matière par son ID.
     * @param int $id
     * @return object|false
     */
    public function getById($id) {
        $this->db->query("SELECT * FROM matieres WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    /**
     * Récupère une matière par son code.
     * @param string $code
     * @return object|false
     */
    public function getByCode($code) {
        $this->db->query("SELECT * FROM matieres WHERE code = :code");
        $this->db->bind(':code', $code);
        return $this->db->single();
    }

    /**
     * Ajoute une nouvelle matière.
     * @param array $data ['code', 'nom']
     * @return bool
     */
    public function add($data) {
        $this->db->query("INSERT INTO matieres (code, nom) VALUES (:code, :nom)");
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':nom', $data['nom']);
        return $this->db->execute();
    }

    /**
     * Modifie une matière existante.
     * @param int $id
     * @param array $data ['code', 'nom']
     * @return bool
     */
    public function update($id, $data) {
        $this->db->query("UPDATE matieres SET code = :code, nom = :nom WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':nom', $data['nom']);
        return $this->db->execute();
    }

    /**
     * Supprime une matière.
     * Avant de supprimer, vérifier si elle est utilisée dans series_matieres ou notes.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $id = (int)$id;
        // Vérifier si la matière est utilisée dans series_matieres
        $this->db->query("SELECT COUNT(*) as count FROM series_matieres WHERE matiere_id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) {
            $_SESSION['error_message'] = 'Cette matière est liée à des séries et ne peut être supprimée.';
            return false;
        }
        // Vérifier si la matière est utilisée dans notes
        $this->db->query("SELECT COUNT(*) as count FROM notes WHERE matiere_id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) {
            $_SESSION['error_message'] = 'Cette matière a des notes associées et ne peut être supprimée.';
            return false;
        }

        $this->db->query("DELETE FROM matieres WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Vérifie si un code de matière existe déjà.
     * @param string $code
     * @param int|null $currentId ID de la matière actuelle à exclure (pour la mise à jour)
     * @return bool
     */
    public function codeExists($code, $currentId = null) {
        $sql = "SELECT id FROM matieres WHERE code = :code";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':code', $code);
        if ($currentId !== null) {
            $this->db->bind(':current_id', (int)$currentId);
        }
        return $this->db->single() ? true : false;
    }
}
?>
