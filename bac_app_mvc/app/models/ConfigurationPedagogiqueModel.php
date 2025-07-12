<?php

class ConfigurationPedagogiqueModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Récupère la configuration pédagogique pour une année scolaire donnée.
     * @param int $annee_scolaire_id L'ID de l'année scolaire.
     * @return object|false L'objet configuration ou false si non trouvé.
     */
    public function getByAnneeScolaireId($annee_scolaire_id) {
        $this->db->query('SELECT cp.*, ans.libelle as annee_scolaire_libelle
                          FROM configurations_pedagogiques cp
                          JOIN annees_scolaires ans ON cp.annee_scolaire_id = ans.id
                          WHERE cp.annee_scolaire_id = :annee_scolaire_id');
        $this->db->bind(':annee_scolaire_id', $annee_scolaire_id);
        return $this->db->single();
    }

    /**
     * Crée ou met à jour la configuration pédagogique pour une année scolaire.
     * @param array $data Tableau associatif des données. Doit inclure 'annee_scolaire_id'.
     * @return bool True si succès, false sinon.
     */
    public function createOrUpdate($data) {
        $existing = $this->getByAnneeScolaireId($data['annee_scolaire_id']);

        if ($existing) {
            // Mise à jour
            $sql = 'UPDATE configurations_pedagogiques SET
                        seuil_admission = :seuil_admission,
                        seuil_second_tour = :seuil_second_tour,
                        mention_passable = :mention_passable,
                        mention_AB = :mention_AB,
                        mention_bien = :mention_bien,
                        mention_TB = :mention_TB,
                        mention_exc = :mention_exc
                    WHERE annee_scolaire_id = :annee_scolaire_id';
        } else {
            // Création
            $sql = 'INSERT INTO configurations_pedagogiques
                        (annee_scolaire_id, seuil_admission, seuil_second_tour, mention_passable, mention_AB, mention_bien, mention_TB, mention_exc)
                    VALUES
                        (:annee_scolaire_id, :seuil_admission, :seuil_second_tour, :mention_passable, :mention_AB, :mention_bien, :mention_TB, :mention_exc)';
        }

        $this->db->query($sql);
        $this->db->bind(':annee_scolaire_id', $data['annee_scolaire_id']);
        $this->db->bind(':seuil_admission', (float)($data['seuil_admission'] ?? 10.00));
        $this->db->bind(':seuil_second_tour', (float)($data['seuil_second_tour'] ?? 9.50));
        $this->db->bind(':mention_passable', (float)($data['mention_passable'] ?? 10.00));
        $this->db->bind(':mention_AB', (float)($data['mention_AB'] ?? 12.00));
        $this->db->bind(':mention_bien', (float)($data['mention_bien'] ?? 14.00));
        $this->db->bind(':mention_TB', (float)($data['mention_TB'] ?? 16.00));
        $this->db->bind(':mention_exc', (float)($data['mention_exc'] ?? 18.00));

        return $this->db->execute();
    }

    /**
     * Récupère toutes les configurations pédagogiques avec le libellé de l'année scolaire.
     * @return array
     */
    public function getAllWithAnneeLibelle() {
        $this->db->query('SELECT cp.*, ans.libelle as annee_scolaire_libelle
                          FROM configurations_pedagogiques cp
                          JOIN annees_scolaires ans ON cp.annee_scolaire_id = ans.id
                          ORDER BY ans.libelle DESC');
        return $this->db->resultSet();
    }

    /**
     * Supprime une configuration pédagogique.
     * Normalement géré par ON DELETE CASCADE lors de la suppression de l'année scolaire.
     * @param int $annee_scolaire_id
     * @return bool
     */
    public function deleteByAnneeScolaireId($annee_scolaire_id) {
        $this->db->query('DELETE FROM configurations_pedagogiques WHERE annee_scolaire_id = :annee_scolaire_id');
        $this->db->bind(':annee_scolaire_id', $annee_scolaire_id);
        return $this->db->execute();
    }
}
?>
