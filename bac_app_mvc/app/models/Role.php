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
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        if ($id == 1) { // Supposition: ID 1 est Administrateur et ne peut être supprimé
            $_SESSION['error_message'] = 'Le rôle Administrateur ne peut pas être supprimé.'; // Message direct pour l'exemple
            return false;
        }
        // TODO: Vérifier si le rôle est utilisé par des utilisateurs avant suppression.
        // $this->db->query("SELECT COUNT(*) as count FROM users WHERE role_id = :id");
        // $this->db->bind(':id', $id);
        // if ($this->db->single()->count > 0) {
        //     $_SESSION['error_message'] = 'Ce rôle est assigné à des utilisateurs et ne peut être supprimé.';
        //     return false;
        // }

        $this->db->query("DELETE FROM roles WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Récupère les accréditations associées à un rôle.
     * @param int $role_id
     * @return array
     */
    public function getAccreditations($role_id) {
        $this->db->query("SELECT a.* FROM accreditations a
                          INNER JOIN roles_accreditations ra ON a.id = ra.accreditation_id
                          WHERE ra.role_id = :role_id ORDER BY a.libelle_action ASC");
        $this->db->bind(':role_id', $role_id);
        return $this->db->resultSet();
    }

    /**
     * Met à jour les accréditations pour un rôle.
     * @param int $role_id
     * @param array $accreditation_ids Tableau des ID d'accréditations
     * @return bool
     */
    public function updateAccreditations($role_id, $accreditation_ids) {
        // Utiliser une transaction pour assurer la cohérence
        try {
            $this->db->beginTransaction();

            $this->db->query("DELETE FROM roles_accreditations WHERE role_id = :role_id");
            $this->db->bind(':role_id', $role_id);
            $this->db->execute();

            if (!empty($accreditation_ids)) {
                // Préparer une seule fois pour plusieurs insertions
                $this->db->query("INSERT INTO roles_accreditations (role_id, accreditation_id) VALUES (:role_id, :accreditation_id)");
                foreach ($accreditation_ids as $accreditation_id) {
                    $this->db->bind(':role_id', $role_id);
                    $this->db->bind(':accreditation_id', (int)$accreditation_id); // S'assurer que c'est un entier
                    $this->db->execute();
                }
            }
            return $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de la mise à jour des accréditations du rôle $role_id: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retourne l'instance PDO de la base de données (utile pour les transactions dans le contrôleur).
     * @return PDO
     */
    public function getDbInstance() {
        return $this->db; // En supposant que $this->db est l'objet Database qui a une méthode pour retourner PDO ou gérer les transactions
    }
}
?>
