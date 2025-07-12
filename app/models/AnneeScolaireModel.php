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
                // et qu'elle n'était pas déjà l'active.
                $current = $this->getById($id);
                if (!$current || !$current->est_active) { // Si elle devient active
                    $this->db->query('UPDATE annees_scolaires SET est_active = FALSE WHERE id != :id_to_keep_active AND est_active = TRUE');
                    $this->db->bind(':id_to_keep_active', $id);
                     if (!$this->db->execute()) {
                        $this->db->rollBack();
                        return false;
                    }
                }
            } else {
                // Si on essaie de désactiver l'année active, il faut s'assurer qu'une autre est active
                // ou empêcher la désactivation si c'est la seule active.
                // Pour l'instant, on permet la désactivation. Le setActive est plus sûr.
                // La logique pour s'assurer qu'il y a toujours une année active est mieux gérée par setActive()
                // ou au niveau du contrôleur.
            }

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

            // 1. Désactiver toutes les années actuellement actives
            $this->db->query('UPDATE annees_scolaires SET est_active = FALSE WHERE est_active = TRUE');
            if (!$this->db->execute()) { // Erreur si la requête échoue
                $this->db->rollBack();
                return false;
            }

            // 2. Activer l'année spécifiée
            $this->db->query('UPDATE annees_scolaires SET est_active = TRUE WHERE id = :id');
            $this->db->bind(':id', $id);

            if ($this->db->execute()) {
                if ($this->db->rowCount() > 0) { // Vérifier si la mise à jour a affecté une ligne
                    $this->db->commit();
                    return true;
                } else { // L'ID n'a pas été trouvé ou une autre erreur
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
        // Vérifier si l'année est active
        $annee = $this->getById($id);
        if ($annee && $annee->est_active) {
            // Ne pas supprimer une année active. Il faut d'abord en activer une autre.
            return false;
        }

        // Vérifier les dépendances (configurations_pedagogiques, eleves, notes, centres_assignations)
        // La table `configurations_pedagogiques` a ON DELETE CASCADE.
        // Les tables `eleves`, `notes`, `centres_assignations` ont ON DELETE RESTRICT ou CASCADE.
        // Il est plus sûr de vérifier explicitement ici.

        $this->db->query('SELECT COUNT(*) as count FROM configurations_pedagogiques WHERE annee_scolaire_id = :id');
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) return false; // Dépendances dans configurations_pedagogiques

        $this->db->query('SELECT COUNT(*) as count FROM eleves WHERE annee_scolaire_id = :id');
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) return false; // Dépendances dans eleves

        $this->db->query('SELECT COUNT(*) as count FROM notes WHERE annee_scolaire_id = :id');
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) return false; // Dépendances dans notes

        $this->db->query('SELECT COUNT(*) as count FROM centres_assignations WHERE annee_scolaire_id = :id');
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) return false; // Dépendances dans centres_assignations


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
