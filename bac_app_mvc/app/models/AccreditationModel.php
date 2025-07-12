<?php

class AccreditationModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Crée une nouvelle accréditation.
     * @param string $libelle_action Le libellé de l'action/permission.
     * @return bool True si succès, false sinon.
     */
    public function create($libelle_action) {
        if ($this->findByLibelle($libelle_action)) {
            // L'accréditation avec ce libellé existe déjà
            return false;
        }
        $this->db->query('INSERT INTO accreditations (libelle_action) VALUES (:libelle_action)');
        $this->db->bind(':libelle_action', $libelle_action);
        return $this->db->execute();
    }

    /**
     * Récupère toutes les accréditations.
     * @return array La liste des accréditations.
     */
    public function getAll() {
        $this->db->query('SELECT * FROM accreditations ORDER BY libelle_action ASC');
        return $this->db->resultSet();
    }

    /**
     * Récupère une accréditation par son ID.
     * @param int $id L'ID de l'accréditation.
     * @return object|false L'objet accréditation ou false si non trouvée.
     */
    public function getById($id) {
        $this->db->query('SELECT * FROM accreditations WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Met à jour une accréditation existante.
     * @param int $id L'ID de l'accréditation à mettre à jour.
     * @param string $libelle_action Le nouveau libellé.
     * @return bool True si succès, false sinon.
     */
    public function update($id, $libelle_action) {
        // Vérifier si un autre enregistrement avec le même libellé existe déjà
        $existing = $this->findByLibelle($libelle_action);
        if ($existing && $existing->id != $id) {
            return false; // Conflit de libellé unique
        }

        $this->db->query('UPDATE accreditations SET libelle_action = :libelle_action WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':libelle_action', $libelle_action);
        return $this->db->execute();
    }

    /**
     * Supprime une accréditation.
     * @param int $id L'ID de l'accréditation à supprimer.
     * @return bool True si succès, false sinon.
     */
    public function delete($id) {
        $this->db->query('DELETE FROM accreditations WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Trouve une accréditation par son libellé.
     * @param string $libelle_action Le libellé à rechercher.
     * @return object|false L'objet accréditation ou false si non trouvée.
     */
    public function findByLibelle($libelle_action) {
        $this->db->query('SELECT * FROM accreditations WHERE libelle_action = :libelle_action');
        $this->db->bind(':libelle_action', $libelle_action);
        return $this->db->single();
    }
}
?>
