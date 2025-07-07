<?php

class Salle {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les salles pour un centre donné.
     * @param int $centre_id
     * @return array
     */
    public function getByCentreId($centre_id) {
        $this->db->query("SELECT * FROM salles WHERE centre_id = :centre_id ORDER BY numero_salle ASC");
        $this->db->bind(':centre_id', (int)$centre_id);
        return $this->db->resultSet();
    }

    /**
     * Récupère une salle par son ID.
     * @param int $id
     * @return object|false
     */
    public function getById($id) {
        $this->db->query("SELECT * FROM salles WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    /**
     * Ajoute une nouvelle salle à un centre.
     * @param array $data ['centre_id', 'numero_salle', 'capacite', 'description']
     * @return bool
     */
    public function add($data) {
        $this->db->query("INSERT INTO salles (centre_id, numero_salle, capacite, description)
                          VALUES (:centre_id, :numero_salle, :capacite, :description)");
        $this->db->bind(':centre_id', (int)$data['centre_id']);
        $this->db->bind(':numero_salle', $data['numero_salle']);
        $this->db->bind(':capacite', (int)$data['capacite']);
        $this->db->bind(':description', $data['description'] ?? null);
        return $this->db->execute();
    }

    /**
     * Modifie une salle existante.
     * @param int $id
     * @param array $data ['numero_salle', 'capacite', 'description'] (centre_id n'est pas modifiable ici)
     * @return bool
     */
    public function update($id, $data) {
        $this->db->query("UPDATE salles SET
                            numero_salle = :numero_salle,
                            capacite = :capacite,
                            description = :description
                          WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        $this->db->bind(':numero_salle', $data['numero_salle']);
        $this->db->bind(':capacite', (int)$data['capacite']);
        $this->db->bind(':description', $data['description'] ?? null);
        return $this->db->execute();
    }

    /**
     * Supprime une salle.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // TODO: Vérifier si la salle est utilisée (par exemple, dans une répartition d'élèves) avant suppression.
        $this->db->query("DELETE FROM salles WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->execute();
    }

    /**
     * Vérifie si un numéro de salle existe déjà pour un centre donné.
     * @param int $centre_id
     * @param string $numero_salle
     * @param int|null $currentSalleId ID de la salle actuelle à exclure (pour la mise à jour)
     * @return bool
     */
    public function numeroSalleExistsInCentre($centre_id, $numero_salle, $currentSalleId = null) {
        $sql = "SELECT id FROM salles WHERE centre_id = :centre_id AND numero_salle = :numero_salle";
        if ($currentSalleId !== null) {
            $sql .= " AND id != :current_salle_id";
        }
        $this->db->query($sql);
        $this->db->bind(':centre_id', (int)$centre_id);
        $this->db->bind(':numero_salle', $numero_salle);
        if ($currentSalleId !== null) {
            $this->db->bind(':current_salle_id', (int)$currentSalleId);
        }
        return $this->db->single() ? true : false;
    }
}
?>
