<?php

class Accreditation {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les accréditations.
     * @return array
     */
    public function getAll() {
        $this->db->query("SELECT * FROM accreditations ORDER BY libelle_action ASC");
        return $this->db->resultSet();
    }

    /**
     * Récupère une accréditation par son ID.
     * @param int $id
     * @return object|false
     */
    public function getById($id) {
        $this->db->query("SELECT * FROM accreditations WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Ajoute une nouvelle accréditation.
     * @param array $data ['libelle_action' => 'Libellé de l'action']
     * @return bool
     */
    public function add($data) {
        $this->db->query("INSERT INTO accreditations (libelle_action) VALUES (:libelle_action)");
        $this->db->bind(':libelle_action', $data['libelle_action']);

        return $this->db->execute();
    }

    /**
     * Modifie une accréditation existante.
     * @param int $id
     * @param array $data ['libelle_action' => 'Nouveau Libellé']
     * @return bool
     */
    public function update($id, $data) {
        $this->db->query("UPDATE accreditations SET libelle_action = :libelle_action WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':libelle_action', $data['libelle_action']);

        return $this->db->execute();
    }

    /**
     * Supprime une accréditation.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // Avant de supprimer, vérifier si l'accréditation est utilisée dans roles_accreditations
        $this->db->query("SELECT COUNT(*) as count FROM roles_accreditations WHERE accreditation_id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) {
            $_SESSION['error_message'] = 'Cette accréditation est utilisée par des rôles et ne peut être supprimée.'; // Message direct
            return false;
        }

        $this->db->query("DELETE FROM accreditations WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Vérifie si un libellé d'accréditation existe déjà.
     * @param string $libelle
     * @param int|null $currentId ID de l'accréditation actuelle à exclure (pour la mise à jour)
     * @return bool
     */
    public function libelleExists($libelle, $currentId = null) {
        $sql = "SELECT id FROM accreditations WHERE libelle_action = :libelle_action";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':libelle_action', $libelle);
        if ($currentId !== null) {
            $this->db->bind(':current_id', $currentId);
        }
        return $this->db->single() ? true : false;
    }
}
?>
