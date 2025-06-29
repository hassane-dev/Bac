<?php

class AnneeScolaire {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les années scolaires.
     * @return array
     */
    public function getAll() {
        $this->db->query("SELECT * FROM annees_scolaires ORDER BY libelle DESC");
        return $this->db->resultSet();
    }

    /**
     * Récupère une année scolaire par son ID.
     * @param int $id
     * @return object|false
     */
    public function getById($id) {
        $this->db->query("SELECT * FROM annees_scolaires WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Récupère l'année scolaire active.
     * @return object|false
     */
    public function getActiveYear() {
        $this->db->query("SELECT * FROM annees_scolaires WHERE est_active = 1 LIMIT 1");
        return $this->db->single();
    }


    /**
     * Ajoute une nouvelle année scolaire.
     * @param array $data ['libelle', 'date_debut', 'date_fin', 'est_active']
     * @return bool
     */
    public function add($data) {
        // Si cette année est marquée comme active, désactiver toutes les autres
        if (isset($data['est_active']) && $data['est_active'] == 1) {
            $this->deactivateAllOthers(null); // Désactive toutes les autres
        }

        $this->db->query("INSERT INTO annees_scolaires (libelle, date_debut, date_fin, est_active)
                          VALUES (:libelle, :date_debut, :date_fin, :est_active)");

        $this->db->bind(':libelle', $data['libelle']);
        $this->db->bind(':date_debut', $data['date_debut'] ?: null);
        $this->db->bind(':date_fin', $data['date_fin'] ?: null);
        $this->db->bind(':est_active', $data['est_active'] ?? 0, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Modifie une année scolaire existante.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        // Si cette année est marquée comme active, désactiver toutes les autres
        if (isset($data['est_active']) && $data['est_active'] == 1) {
            $this->deactivateAllOthers($id);
        }

        $this->db->query("UPDATE annees_scolaires SET
                            libelle = :libelle,
                            date_debut = :date_debut,
                            date_fin = :date_fin,
                            est_active = :est_active
                          WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':libelle', $data['libelle']);
        $this->db->bind(':date_debut', $data['date_debut'] ?: null);
        $this->db->bind(':date_fin', $data['date_fin'] ?: null);
        $this->db->bind(':est_active', $data['est_active'] ?? 0, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Supprime une année scolaire.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // TODO: Vérifier si l'année scolaire est utilisée (ex: dans configurations_pedagogiques) avant de supprimer.
        $this->db->query("DELETE FROM annees_scolaires WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Active une année scolaire spécifique et désactive toutes les autres.
     * @param int $id ID de l'année à activer
     * @return bool
     */
    public function setActive($id) {
        $this->db->query("UPDATE annees_scolaires SET est_active = 0 WHERE id != :id");
        $this->db->bind(':id', $id);
        if (!$this->db->execute()) return false;

        $this->db->query("UPDATE annees_scolaires SET est_active = 1 WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Désactive toutes les autres années scolaires, sauf celle spécifiée (optionnel).
     * @param int|null $excludeId ID de l'année à ne PAS désactiver.
     * @return bool
     */
    private function deactivateAllOthers($excludeId = null) {
        $sql = "UPDATE annees_scolaires SET est_active = 0";
        if ($excludeId !== null) {
            $sql .= " WHERE id != :exclude_id";
        }
        $this->db->query($sql);
        if ($excludeId !== null) {
            $this->db->bind(':exclude_id', $excludeId);
        }
        return $this->db->execute();
    }

    /**
     * Vérifie si un libellé d'année scolaire existe déjà.
     * @param string $libelle
     * @param int|null $currentId ID de l'année actuelle à exclure (pour la mise à jour)
     * @return bool
     */
    public function libelleExists($libelle, $currentId = null) {
        $sql = "SELECT id FROM annees_scolaires WHERE libelle = :libelle";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':libelle', $libelle);
        if ($currentId !== null) {
            $this->db->bind(':current_id', $currentId);
        }
        return $this->db->single() ? true : false;
    }

}
?>
