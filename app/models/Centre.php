<?php

class Centre {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $this->db->query("SELECT * FROM centres ORDER BY nom_centre ASC");
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM centres WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    public function add($data) {
        $this->db->query("INSERT INTO centres (nom_centre, code_centre, description) VALUES (:nom_centre, :code_centre, :description)");
        $this->db->bind(':nom_centre', $data['nom_centre']);
        $this->db->bind(':code_centre', $data['code_centre'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        return $this->db->execute();
    }

    public function update($id, $data) {
        $this->db->query("UPDATE centres SET nom_centre = :nom_centre, code_centre = :code_centre, description = :description WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        $this->db->bind(':nom_centre', $data['nom_centre']);
        $this->db->bind(':code_centre', $data['code_centre'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        return $this->db->execute();
    }

    public function delete($id) {
        $id = (int)$id;
        // Vérifier si le centre est utilisé par des élèves
        $this->db->query("SELECT COUNT(*) as count FROM eleves WHERE centre_id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) {
            $_SESSION['error_message'] = 'Ce centre est assigné à des élèves et ne peut être supprimé.';
            return false;
        }
        // Vérifier si le centre est utilisé dans les assignations
        $this->db->query("SELECT COUNT(*) as count FROM centres_assignations WHERE centre_id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) {
            $_SESSION['error_message'] = 'Ce centre a des assignations de lycées/séries et ne peut être supprimé.';
            return false;
        }
        // Vérifier si le centre a des salles
        $this->db->query("SELECT COUNT(*) as count FROM salles WHERE centre_id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) {
            $_SESSION['error_message'] = 'Ce centre a des salles associées et ne peut être supprimé. Supprimez d\'abord les salles.';
            return false;
        }

        $this->db->query("DELETE FROM centres WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function nomExists($nomCentre, $currentId = null) {
        $sql = "SELECT id FROM centres WHERE nom_centre = :nom_centre";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':nom_centre', $nomCentre);
        if ($currentId !== null) {
            $this->db->bind(':current_id', (int)$currentId);
        }
        return $this->db->single() ? true : false;
    }

    /**
     * Vérifie si un code de centre existe déjà.
     * @param string $codeCentre
     * @param int|null $currentId ID du centre actuel à exclure (pour la mise à jour)
     * @return bool
     */
    public function codeCentreExists($codeCentre, $currentId = null) {
        if (empty($codeCentre)) return false; // Un code vide n'est pas considéré comme "existant" en tant que doublon
        $sql = "SELECT id FROM centres WHERE code_centre = :code_centre";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':code_centre', $codeCentre);
        if ($currentId !== null) {
            $this->db->bind(':current_id', (int)$currentId);
        }
        return $this->db->single() ? true : false;
    }

    // Méthodes pour salles et assignations seront ajoutées ici plus tard
    public function getSallesByCentreId($centre_id) {
        $this->db->query("SELECT * FROM salles WHERE centre_id = :centre_id ORDER BY numero_salle ASC");
        $this->db->bind(':centre_id', (int)$centre_id);
        return $this->db->resultSet();
    }

    public function getAssignations($centre_id, $annee_scolaire_id) {
        $this->db->query("SELECT ca.*, l.nom_lycee, s.code as serie_code, s.libelle as serie_libelle
                          FROM centres_assignations ca
                          LEFT JOIN lycees l ON ca.lycee_id = l.id
                          LEFT JOIN series s ON ca.serie_id = s.id
                          WHERE ca.centre_id = :centre_id AND ca.annee_scolaire_id = :annee_scolaire_id
                          ORDER BY l.nom_lycee, s.code");
        $this->db->bind(':centre_id', (int)$centre_id);
        $this->db->bind(':annee_scolaire_id', (int)$annee_scolaire_id);
        return $this->db->resultSet();
    }

    /**
     * Ajoute une nouvelle assignation (lycée/série) à un centre pour une année scolaire.
     * @param array $data ['centre_id', 'lycee_id' (nullable), 'serie_id' (nullable), 'annee_scolaire_id']
     * @return bool
     */
    public function addAssignation($data) {
        // Vérifier qu'au moins lycee_id ou serie_id est fourni
        if (empty($data['lycee_id']) && empty($data['serie_id'])) {
            $_SESSION['error_message'] = 'Veuillez sélectionner au moins un lycée ou une série pour l\'assignation.';
            return false;
        }

        // Vérifier si cette assignation exacte existe déjà pour éviter les doublons (la BDD a une contrainte UNIQUE)
        $this->db->query("SELECT id FROM centres_assignations
                          WHERE centre_id = :centre_id
                          AND annee_scolaire_id = :annee_scolaire_id
                          AND (lycee_id = :lycee_id OR (:lycee_id IS NULL AND lycee_id IS NULL))
                          AND (serie_id = :serie_id OR (:serie_id IS NULL AND serie_id IS NULL))");
        $this->db->bind(':centre_id', (int)$data['centre_id']);
        $this->db->bind(':annee_scolaire_id', (int)$data['annee_scolaire_id']);
        $this->db->bind(':lycee_id', isset($data['lycee_id']) ? (int)$data['lycee_id'] : null);
        $this->db->bind(':serie_id', isset($data['serie_id']) ? (int)$data['serie_id'] : null);

        if ($this->db->single()) {
            $_SESSION['error_message'] = 'Cette assignation (lycée/série) existe déjà pour ce centre et cette année scolaire.';
            return false;
        }


        $this->db->query("INSERT INTO centres_assignations (centre_id, lycee_id, serie_id, annee_scolaire_id)
                          VALUES (:centre_id, :lycee_id, :serie_id, :annee_scolaire_id)");

        $this->db->bind(':centre_id', (int)$data['centre_id']);
        $this->db->bind(':lycee_id', isset($data['lycee_id']) && !empty($data['lycee_id']) ? (int)$data['lycee_id'] : null);
        $this->db->bind(':serie_id', isset($data['serie_id']) && !empty($data['serie_id']) ? (int)$data['serie_id'] : null);
        $this->db->bind(':annee_scolaire_id', (int)$data['annee_scolaire_id']);

        return $this->db->execute();
    }

    /**
     * Supprime une assignation spécifique.
     * @param int $assignation_id ID de l'entrée dans la table centres_assignations
     * @return bool
     */
    public function removeAssignation($assignation_id) {
        $this->db->query("DELETE FROM centres_assignations WHERE id = :id");
        $this->db->bind(':id', (int)$assignation_id);
        return $this->db->execute();
    }
}
?>
