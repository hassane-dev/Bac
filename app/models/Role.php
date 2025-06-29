<?php

class Role {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère tous les rôles.
     * @return array
     */
    public function getAll() {
        $this->db->query("SELECT * FROM roles ORDER BY nom_role ASC");
        return $this->db->resultSet();
    }

    /**
     * Récupère un rôle par son ID.
     * @param int $id
     * @return object|false
     */
    public function getById($id) {
        $this->db->query("SELECT * FROM roles WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Ajoute un nouveau rôle.
     * @param array $data ['nom_role' => 'Nom du Rôle']
     * @return bool
     */
    public function add($data) {
        $this->db->query("INSERT INTO roles (nom_role) VALUES (:nom_role)");
        $this->db->bind(':nom_role', $data['nom_role']);

        return $this->db->execute();
    }

    /**
     * Modifie un rôle existant.
     * @param int $id
     * @param array $data ['nom_role' => 'Nouveau Nom']
     * @return bool
     */
    public function update($id, $data) {
        $this->db->query("UPDATE roles SET nom_role = :nom_role WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':nom_role', $data['nom_role']);

        return $this->db->execute();
    }

    /**
     * Supprime un rôle.
     * Avant de supprimer un rôle, il faudrait idéalement vérifier s'il est utilisé par des utilisateurs
     * et gérer cela (par exemple, interdire la suppression ou réassigner les utilisateurs).
     * Pour l'instant, suppression simple.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // Vérification basique : ne pas supprimer le rôle Administrateur (supposons ID 1)
        // Une meilleure approche serait de rendre cela configurable ou d'avoir un flag "system_role"
        if ($id == 1) {
            // Vous pourriez retourner false ou lancer une exception pour indiquer que ce rôle ne peut être supprimé.
            // Pour l'instant, nous allons juste empêcher la requête.
            // error_log("Tentative de suppression du rôle Administrateur (ID: 1) bloquée.");
            return false;
        }

        $this->db->query("DELETE FROM roles WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Récupère les accréditations associées à un rôle.
     * (Sera implémenté dans l'étape 3)
     * @param int $role_id
     * @return array
     */
    public function getAccreditations($role_id) {
        $this->db->query("SELECT a.* FROM accreditations a
                          INNER JOIN roles_accreditations ra ON a.id = ra.accreditation_id
                          WHERE ra.role_id = :role_id");
        $this->db->bind(':role_id', $role_id);
        return $this->db->resultSet();
    }

    /**
     * Met à jour les accréditations pour un rôle.
     * Supprime les anciennes et insère les nouvelles.
     * (Sera implémenté dans l'étape 3)
     * @param int $role_id
     * @param array $accreditation_ids Tableau des ID d'accréditations
     * @return bool
     */
    public function updateAccreditations($role_id, $accreditation_ids) {
        // 1. Supprimer les accréditations existantes pour ce rôle
        $this->db->query("DELETE FROM roles_accreditations WHERE role_id = :role_id");
        $this->db->bind(':role_id', $role_id);
        if (!$this->db->execute()) {
            return false;
        }

        // 2. Insérer les nouvelles accréditations
        if (!empty($accreditation_ids)) {
            $this->db->query("INSERT INTO roles_accreditations (role_id, accreditation_id) VALUES (:role_id, :accreditation_id)");
            foreach ($accreditation_ids as $accreditation_id) {
                $this->db->bind(':role_id', $role_id);
                $this->db->bind(':accreditation_id', $accreditation_id);
                if (!$this->db->execute()) {
                    // Gérer l'erreur, peut-être annuler la transaction si elle est utilisée
                    return false;
                }
            }
        }
        return true;
    }
}
?>
