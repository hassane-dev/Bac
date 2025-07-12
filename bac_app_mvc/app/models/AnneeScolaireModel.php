<?php

class AnneeScolaireModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Crée une nouvelle année scolaire.
     * @param string $libelle Libellé de l'année (ex: 2023-2024)
     * @param string $date_debut Date de début (YYYY-MM-DD)
     * @param string $date_fin Date de fin (YYYY-MM-DD)
     * @param bool $est_active Indique si cette année doit être active (désactivera les autres)
     * @return int|false L'ID de l'année créée ou false en cas d'échec.
     */
    public function create($libelle, $date_debut, $date_fin, $est_active = false) {
        if ($this->findByLibelle($libelle)) {
            return false; // Le libellé doit être unique
        }

        try {
            $this->db->beginTransaction();

            if ($est_active) {
                // Désactiver toutes les autres années scolaires
                $this->db->query('UPDATE annees_scolaires SET est_active = FALSE WHERE est_active = TRUE');
                if (!$this->db->execute()) {
                    $this->db->rollBack();
                    return false;
                }
            }

            $this->db->query('INSERT INTO annees_scolaires (libelle, date_debut, date_fin, est_active)
                              VALUES (:libelle, :date_debut, :date_fin, :est_active)');
            $this->db->bind(':libelle', $libelle);
            $this->db->bind(':date_debut', $date_debut);
            $this->db->bind(':date_fin', $date_fin);
            $this->db->bind(':est_active', $est_active, PDO::PARAM_BOOL);

            if ($this->db->execute()) {
                $lastId = $this->db->lastInsertId();
                $this->db->commit();
                return $lastId;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de la création de l'année scolaire: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère toutes les années scolaires, triées par libellé décroissant.
     * @return array
     */
    public function getAll() {
        $this->db->query('SELECT * FROM annees_scolaires ORDER BY libelle DESC');
        return $this->db->resultSet();
    }

    /**
     * Récupère une année scolaire par son ID.
     * @param int $id
     * @return object|false
     */
    public function getById($id) {
        $this->db->query('SELECT * FROM annees_scolaires WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Met à jour une année scolaire.
     * @param int $id
     * @param string $libelle
     * @param string $date_debut
     * @param string $date_fin
     * @param bool $est_active
     * @return bool
     */
    public function update($id, $libelle, $date_debut, $date_fin, $est_active) {
        $existing = $this->findByLibelle($libelle);
        if ($existing && $existing->id != $id) {
            return false; // Conflit de libellé unique
        }

        try {
            $this->db->beginTransaction();

            if ($est_active) {
                // Désactiver toutes les autres années scolaires si celle-ci devient active
                $current = $this->getById($id); // Récupérer l'état actuel avant la mise à jour
                if ($current && !$current->est_active) { // Si elle n'était pas active et le devient
                    $this->db->query('UPDATE annees_scolaires SET est_active = FALSE WHERE id != :id_to_keep_active AND est_active = TRUE');
                    $this->db->bind(':id_to_keep_active', $id);
                     if (!$this->db->execute()) {
                        $this->db->rollBack();
                        return false;
                    }
                }
            }
            // Si $est_active est false, mais que c'est la seule année active,
            // la logique pour empêcher la désactivation de la dernière année active
            // devrait idéalement être dans le contrôleur ou via une méthode setActive distincte
            // pour éviter de rendre cette méthode update trop complexe.
            // Pour l'instant, on se fie à ce que le contrôleur appelle setActive pour les changements d'état clairs.

            $this->db->query('UPDATE annees_scolaires SET
                              libelle = :libelle,
                              date_debut = :date_debut,
                              date_fin = :date_fin,
                              est_active = :est_active
                              WHERE id = :id');
            $this->db->bind(':id', $id);
            $this->db->bind(':libelle', $libelle);
            $this->db->bind(':date_debut', $date_debut);
            $this->db->bind(':date_fin', $date_fin);
            $this->db->bind(':est_active', $est_active, PDO::PARAM_BOOL);

            if ($this->db->execute()) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de la mise à jour de l'année scolaire: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Définit une année scolaire comme active et désactive toutes les autres.
     * @param int $id L'ID de l'année scolaire à activer.
     * @return bool True en cas de succès, false sinon.
     */
    public function setActive($id) {
        try {
            $this->db->beginTransaction();

            $this->db->query('UPDATE annees_scolaires SET est_active = FALSE WHERE est_active = TRUE');
            if (!$this->db->execute()) {
                $this->db->rollBack();
                return false;
            }

            $this->db->query('UPDATE annees_scolaires SET est_active = TRUE WHERE id = :id');
            $this->db->bind(':id', $id);

            if ($this->db->execute()) {
                if ($this->db->rowCount() > 0) {
                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de l'activation de l'année scolaire: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère l'année scolaire active.
     * @return object|false L'objet année scolaire active ou false si aucune n'est active.
     */
    public function getActiveYear() {
        $this->db->query('SELECT * FROM annees_scolaires WHERE est_active = TRUE LIMIT 1');
        return $this->db->single();
    }

    /**
     * Supprime une année scolaire.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $annee = $this->getById($id);
        if ($annee && $annee->est_active) {
            return false;
        }

        $dependencies = [
            'configurations_pedagogiques' => 'SELECT COUNT(*) as count FROM configurations_pedagogiques WHERE annee_scolaire_id = :id',
            'eleves' => 'SELECT COUNT(*) as count FROM eleves WHERE annee_scolaire_id = :id',
            'notes' => 'SELECT COUNT(*) as count FROM notes WHERE annee_scolaire_id = :id',
            'centres_assignations' => 'SELECT COUNT(*) as count FROM centres_assignations WHERE annee_scolaire_id = :id'
        ];

        foreach($dependencies as $table => $sql) {
            $this->db->query($sql);
            $this->db->bind(':id', $id);
            if ($this->db->single()->count > 0) {
                 error_log("Tentative de suppression de l'année scolaire ID $id qui a des dépendances dans la table $table.");
                return false;
            }
        }

        $this->db->query('DELETE FROM annees_scolaires WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Trouve une année scolaire par son libellé.
     * @param string $libelle
     * @return object|false
     */
    public function findByLibelle($libelle) {
        $this->db->query('SELECT * FROM annees_scolaires WHERE libelle = :libelle');
        $this->db->bind(':libelle', $libelle);
        return $this->db->single();
    }
}
?>
