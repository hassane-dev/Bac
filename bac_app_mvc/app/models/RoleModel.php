<?php

class RoleModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Crée un nouveau rôle.
     * @param string $nom_role Le nom du rôle.
     * @return int|false L'ID du rôle créé ou false en cas d'échec.
     */
    public function create($nom_role) {
        if ($this->findByNomRole($nom_role)) {
            return false; // Le rôle existe déjà
        }
        $this->db->query('INSERT INTO roles (nom_role) VALUES (:nom_role)');
        $this->db->bind(':nom_role', $nom_role);
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Récupère tous les rôles.
     * @return array La liste des rôles.
     */
    public function getAll() {
        $this->db->query('SELECT * FROM roles ORDER BY nom_role ASC');
        return $this->db->resultSet();
    }

    /**
     * Récupère un rôle par son ID.
     * @param int $id L'ID du rôle.
     * @return object|false L'objet rôle ou false si non trouvé.
     */
    public function getById($id) {
        $this->db->query('SELECT * FROM roles WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Met à jour un rôle existant.
     * @param int $id L'ID du rôle à mettre à jour.
     * @param string $nom_role Le nouveau nom du rôle.
     * @return bool True si succès, false sinon.
     */
    public function update($id, $nom_role) {
        $existing = $this->findByNomRole($nom_role);
        if ($existing && $existing->id != $id) {
            return false; // Conflit de nom unique
        }
        $this->db->query('UPDATE roles SET nom_role = :nom_role WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':nom_role', $nom_role);
        return $this->db->execute();
    }

    /**
     * Supprime un rôle.
     * @param int $id L'ID du rôle à supprimer.
     * @return bool True si succès, false sinon.
     */
    public function delete($id) {
        $this->db->query('SELECT COUNT(*) as count FROM users WHERE role_id = :role_id');
        $this->db->bind(':role_id', $id);
        $userCount = $this->db->single()->count;

        if ($userCount > 0) {
            error_log("Tentative de suppression du rôle ID $id qui est toujours assigné à $userCount utilisateur(s).");
            return false;
        }

        $this->db->query('DELETE FROM roles WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute(); // roles_accreditations sera nettoyé par ON DELETE CASCADE
    }

    /**
     * Trouve un rôle par son nom.
     * @param string $nom_role Le nom du rôle à rechercher.
     * @return object|false L'objet rôle ou false si non trouvé.
     */
    public function findByNomRole($nom_role) {
        $this->db->query('SELECT * FROM roles WHERE nom_role = :nom_role');
        $this->db->bind(':nom_role', $nom_role);
        return $this->db->single();
    }

    /**
     * Récupère les accréditations assignées à un rôle.
     * @param int $role_id L'ID du rôle.
     * @return array La liste des objets accréditation.
     */
    public function getAccreditations($role_id) {
        $this->db->query('SELECT a.* FROM accreditations a
                          JOIN roles_accreditations ra ON a.id = ra.accreditation_id
                          WHERE ra.role_id = :role_id
                          ORDER BY a.libelle_action ASC');
        $this->db->bind(':role_id', $role_id);
        return $this->db->resultSet();
    }

    /**
     * Récupère les IDs des accréditations assignées à un rôle.
     * @param int $role_id L'ID du rôle.
     * @return array La liste des IDs d'accréditation.
     */
    public function getAccreditationIds($role_id) {
        $accreditations = $this->getAccreditations($role_id);
        return array_map(function($acc) { return $acc->id; }, $accreditations);
    }


    /**
     * Met à jour toutes les accréditations pour un rôle donné.
     * Supprime les anciennes, insère les nouvelles.
     * @param int $role_id L'ID du rôle.
     * @param array $accreditation_ids Tableau des IDs d'accréditations à assigner.
     * @return bool True si succès global, false sinon.
     */
    public function updateAccreditations($role_id, $accreditation_ids = []) {
        if (!$this->db->beginTransaction()) return false;

        $this->db->query('DELETE FROM roles_accreditations WHERE role_id = :role_id');
        $this->db->bind(':role_id', $role_id);
        if (!$this->db->execute()) {
            $this->db->rollBack();
            return false;
        }

        if (!empty($accreditation_ids)) {
            $accreditation_ids = array_map('intval', $accreditation_ids);

            $this->db->query('INSERT INTO roles_accreditations (role_id, accreditation_id) VALUES (:role_id, :accreditation_id)');
            foreach ($accreditation_ids as $acc_id) {
                if ($acc_id > 0) {
                    $this->db->bind(':role_id', $role_id);
                    $this->db->bind(':accreditation_id', $acc_id);
                    if (!$this->db->execute()) {
                        $this->db->rollBack();
                        return false;
                    }
                }
            }
        }
        return $this->db->commit();
    }
}
?>
