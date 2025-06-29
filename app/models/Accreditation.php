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
     * Avant de supprimer, il faudrait vérifier si elle est utilisée dans roles_accreditations.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // TODO: Vérifier si l'accréditation est utilisée dans roles_accreditations avant de supprimer.
        // Si elle est utilisée, empêcher la suppression ou la gérer.
        // Exemple:
        // $this->db->query("SELECT COUNT(*) as count FROM roles_accreditations WHERE accreditation_id = :id");
        // $this->db->bind(':id', $id);
        // $count = $this->db->single()->count;
        // if ($count > 0) {
        //     return false; // Ou lancer une exception
        // }

        $this->db->query("DELETE FROM accreditations WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }
}
?>
