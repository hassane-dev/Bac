<?php

class ConfigurationPedagogique {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les configurations pédagogiques avec le libellé de l'année scolaire.
     * @return array
     */
    public function getAllWithAnneeScolaire() {
        $this->db->query("SELECT cp.*, ans.libelle as annee_scolaire_libelle
                          FROM configurations_pedagogiques cp
                          INNER JOIN annees_scolaires ans ON cp.annee_scolaire_id = ans.id
                          ORDER BY ans.libelle DESC");
        return $this->db->resultSet();
    }

    /**
     * Récupère la configuration pédagogique pour une année scolaire spécifique.
     * @param int $annee_scolaire_id
     * @return object|false
     */
    public function getByAnneeScolaireId($annee_scolaire_id) {
        $this->db->query("SELECT * FROM configurations_pedagogiques WHERE annee_scolaire_id = :annee_scolaire_id");
        $this->db->bind(':annee_scolaire_id', (int)$annee_scolaire_id);
        return $this->db->single();
    }

    /**
     * Récupère une configuration pédagogique par son ID (PK de la table config).
     * @param int $id
     * @return object|false
     */
    public function getById($id) {
        $this->db->query("SELECT cp.*, ans.libelle as annee_scolaire_libelle
                          FROM configurations_pedagogiques cp
                          INNER JOIN annees_scolaires ans ON cp.annee_scolaire_id = ans.id
                          WHERE cp.id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    /**
     * Récupère la configuration pédagogique de l'année scolaire active.
     * @return object|false
     */
    public function getActiveConfig() {
        $this->db->query("SELECT cp.*
                          FROM configurations_pedagogiques cp
                          INNER JOIN annees_scolaires ans ON cp.annee_scolaire_id = ans.id
                          WHERE ans.est_active = TRUE LIMIT 1");
        return $this->db->single();
    }

    /**
     * Ajoute ou met à jour une configuration pédagogique pour une année scolaire.
     * La table a une contrainte UNIQUE sur annee_scolaire_id, donc on utilise INSERT ... ON DUPLICATE KEY UPDATE.
     * @param array $data
     * @return bool
     */
    public function save($data) {
        $this->db->query("INSERT INTO configurations_pedagogiques
                            (annee_scolaire_id, seuil_admission, seuil_second_tour, mention_passable, mention_AB, mention_bien, mention_TB, mention_exc)
                          VALUES
                            (:annee_scolaire_id, :seuil_admission, :seuil_second_tour, :mention_passable, :mention_AB, :mention_bien, :mention_TB, :mention_exc)
                          ON DUPLICATE KEY UPDATE
                            seuil_admission = VALUES(seuil_admission),
                            seuil_second_tour = VALUES(seuil_second_tour),
                            mention_passable = VALUES(mention_passable),
                            mention_AB = VALUES(mention_AB),
                            mention_bien = VALUES(mention_bien),
                            mention_TB = VALUES(mention_TB),
                            mention_exc = VALUES(mention_exc)");

        $this->db->bind(':annee_scolaire_id', $data['annee_scolaire_id']);
        $this->db->bind(':seuil_admission', $data['seuil_admission']);
        $this->db->bind(':seuil_second_tour', $data['seuil_second_tour']);
        $this->db->bind(':mention_passable', $data['mention_passable']);
        $this->db->bind(':mention_AB', $data['mention_AB']);
        $this->db->bind(':mention_bien', $data['mention_bien']);
        $this->db->bind(':mention_TB', $data['mention_TB']);
        $this->db->bind(':mention_exc', $data['mention_exc']);

        return $this->db->execute();
    }

    /**
     * Supprime une configuration pédagogique par l'ID de la configuration.
     * (Peu utilisé car la suppression est plutôt liée à la suppression de l'année scolaire)
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $this->db->query("DELETE FROM configurations_pedagogiques WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->execute();
    }
}
?>
