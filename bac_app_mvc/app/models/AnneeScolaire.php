<?php

class AnneeScolaire {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $this->db->query("SELECT * FROM annees_scolaires ORDER BY libelle DESC");
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM annees_scolaires WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    public function getActiveYear() {
        $this->db->query("SELECT * FROM annees_scolaires WHERE est_active = TRUE LIMIT 1");
        return $this->db->single();
    }

    public function add($data) {
        try {
            if (isset($data['est_active']) && $data['est_active'] == 1) {
                $this->db->beginTransaction();
                $this->deactivateAllOthers(null);
            }

            $this->db->query("INSERT INTO annees_scolaires (libelle, date_debut, date_fin, est_active)
                              VALUES (:libelle, :date_debut, :date_fin, :est_active)");

            $this->db->bind(':libelle', $data['libelle']);
            $this->db->bind(':date_debut', !empty($data['date_debut']) ? $data['date_debut'] : null);
            $this->db->bind(':date_fin', !empty($data['date_fin']) ? $data['date_fin'] : null);
            $this->db->bind(':est_active', isset($data['est_active']) ? (int)$data['est_active'] : 0, PDO::PARAM_INT);

            $success = $this->db->execute();

            if (isset($data['est_active']) && $data['est_active'] == 1 && $success) {
                $this->db->commit();
            } elseif (isset($data['est_active']) && $data['est_active'] == 1 && !$success) {
                $this->db->rollBack();
            }
            return $success;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erreur AnneeScolaireModel::add : " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
         try {
            if (isset($data['est_active']) && $data['est_active'] == 1) {
                $this->db->beginTransaction();
                $this->deactivateAllOthers((int)$id);
            }

            $this->db->query("UPDATE annees_scolaires SET
                                libelle = :libelle,
                                date_debut = :date_debut,
                                date_fin = :date_fin,
                                est_active = :est_active
                              WHERE id = :id");

            $this->db->bind(':id', (int)$id);
            $this->db->bind(':libelle', $data['libelle']);
            $this->db->bind(':date_debut', !empty($data['date_debut']) ? $data['date_debut'] : null);
            $this->db->bind(':date_fin', !empty($data['date_fin']) ? $data['date_fin'] : null);
            $this->db->bind(':est_active', isset($data['est_active']) ? (int)$data['est_active'] : 0, PDO::PARAM_INT);

            $success = $this->db->execute();

            if (isset($data['est_active']) && $data['est_active'] == 1 && $success) {
                 $this->db->commit();
            } elseif (isset($data['est_active']) && $data['est_active'] == 1 && !$success) {
                $this->db->rollBack();
            }
            return $success;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erreur AnneeScolaireModel::update : " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        $id = (int)$id;
        // Vérifier si l'année est active
        $annee = $this->getById($id);
        if ($annee && $annee->est_active) {
            $_SESSION['error_message'] = 'Impossible de supprimer une année scolaire active.'; // Ce message devrait être une traduction
            return false;
        }
        // TODO: Vérifier si l'année scolaire est utilisée (ex: dans configurations_pedagogiques) avant de supprimer.
        // Si oui, retourner false avec un message d'erreur.

        $this->db->query("DELETE FROM annees_scolaires WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function setActive($id) {
        $id = (int)$id;
         try {
            $this->db->beginTransaction();
            $this->deactivateAllOthers($id); // Désactive toutes les autres SAUF celle-ci

            $this->db->query("UPDATE annees_scolaires SET est_active = TRUE WHERE id = :id");
            $this->db->bind(':id', $id);
            $success = $this->db->execute();

            if ($success) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erreur AnneeScolaireModel::setActive : " . $e->getMessage());
            return false;
        }
    }

    private function deactivateAllOthers($excludeId = null) {
        $sql = "UPDATE annees_scolaires SET est_active = FALSE WHERE 1=1"; // WHERE 1=1 pour faciliter l'ajout de condition
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $this->db->query($sql);
        if ($excludeId !== null) {
            $this->db->bind(':exclude_id', (int)$excludeId);
        }
        return $this->db->execute(); // Retourne le résultat de l'exécution
    }

    public function libelleExists($libelle, $currentId = null) {
        $sql = "SELECT id FROM annees_scolaires WHERE libelle = :libelle";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':libelle', $libelle);
        if ($currentId !== null) {
            $this->db->bind(':current_id', (int)$currentId);
        }
        return $this->db->single() ? true : false;
    }
     public function isUsed($id) {
        $this->db->query("SELECT COUNT(*) as count FROM configurations_pedagogiques WHERE annee_scolaire_id = :id");
        $this->db->bind(':id', (int)$id);
        if ($this->db->single()->count > 0) {
            return true;
        }
        // Ajouter d'autres vérifications si nécessaire (ex: élèves liés à une année active, etc.)
        return false;
    }
}
?>
