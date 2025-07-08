<?php

class Eleve {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère tous les élèves pour une année scolaire donnée, avec informations jointes.
     * @param int $annee_scolaire_id
     * @return array
     */
    public function getAll($annee_scolaire_id) {
        $this->db->query("SELECT e.*, s.libelle as serie_libelle, l.nom_lycee, c.nom_centre
                          FROM eleves e
                          INNER JOIN series s ON e.serie_id = s.id
                          INNER JOIN lycees l ON e.lycee_id = l.id
                          LEFT JOIN centres c ON e.centre_id = c.id
                          WHERE e.annee_scolaire_id = :annee_scolaire_id
                          ORDER BY e.nom ASC, e.prenom ASC");
        $this->db->bind(':annee_scolaire_id', (int)$annee_scolaire_id);
        return $this->db->resultSet();
    }

    /**
     * Récupère un élève par son ID, avec informations jointes.
     * @param int $id
     * @return object|false
     */
    public function getById($id) {
        $this->db->query("SELECT e.*, s.libelle as serie_libelle, l.nom_lycee, c.nom_centre, ans.libelle as annee_scolaire_libelle
                          FROM eleves e
                          INNER JOIN series s ON e.serie_id = s.id
                          INNER JOIN lycees l ON e.lycee_id = l.id
                          INNER JOIN annees_scolaires ans ON e.annee_scolaire_id = ans.id
                          LEFT JOIN centres c ON e.centre_id = c.id
                          WHERE e.id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    /**
     * Ajoute un nouvel élève.
     * @param array $data
     * @return bool
     */
    public function add($data) {
        // Générer numero_sequentiel_serie
        // Ce numéro est global pour la série dans l'année scolaire
        $this->db->query("SELECT COALESCE(MAX(numero_sequentiel_serie), 0) as max_seq FROM eleves WHERE annee_scolaire_id = :annee_scolaire_id AND serie_id = :serie_id");
        $this->db->bind(':annee_scolaire_id', (int)$data['annee_scolaire_id']);
        $this->db->bind(':serie_id', (int)$data['serie_id']);
        $max_seq = $this->db->single()->max_seq;
        $numero_sequentiel = $max_seq + 1;

        // Construire le matricule: CodeCentreCodeSerieNumeroSequentiel
        // Les codes sont passés par le contrôleur après avoir été récupérés des tables respectives.
        $matricule = strtoupper(($data['code_centre'] ?? '') . ($data['code_serie'] ?? '') . $numero_sequentiel);

        $this->db->query("INSERT INTO eleves (matricule, nom, prenom, date_naissance, sexe, serie_id, lycee_id, annee_scolaire_id, photo,
                                            empreinte1, empreinte2, empreinte3, empreinte4, empreinte5,
                                            empreinte6, empreinte7, empreinte8, empreinte9, empreinte10,
                                            centre_id, numero_sequentiel_serie)
                          VALUES (:matricule, :nom, :prenom, :date_naissance, :sexe, :serie_id, :lycee_id, :annee_scolaire_id, :photo,
                                  :empreinte1, :empreinte2, :empreinte3, :empreinte4, :empreinte5,
                                  :empreinte6, :empreinte7, :empreinte8, :empreinte9, :empreinte10,
                                  :centre_id, :numero_sequentiel_serie)");

        $this->db->bind(':matricule', $matricule);
        $this->db->bind(':nom', $data['nom']);
        $this->db->bind(':prenom', $data['prenom']);
        $this->db->bind(':date_naissance', $data['date_naissance']);
        $this->db->bind(':sexe', $data['sexe']);
        $this->db->bind(':serie_id', (int)$data['serie_id']);
        $this->db->bind(':lycee_id', (int)$data['lycee_id']);
        $this->db->bind(':annee_scolaire_id', (int)$data['annee_scolaire_id']);
        $this->db->bind(':photo', $data['photo'] ?? null);
        $this->db->bind(':centre_id', (int)$data['centre_id']); // Doit être NOT NULL maintenant
        $this->db->bind(':numero_sequentiel_serie', $numero_sequentiel);

        for ($i = 1; $i <= 10; $i++) {
            $this->db->bind(":empreinte{$i}", $data["empreinte{$i}"] ?? null);
        }

        return $this->db->execute();
    }

    /**
     * Modifie un élève existant.
     * La regénération du matricule et du numero_sequentiel_serie si la série/année change
     * est complexe et peut avoir des effets de bord. Pour l'instant, on assume que
     * le matricule et le numero_sequentiel_serie ne sont pas modifiés après création.
     * Seuls les autres champs informatifs sont mis à jour.
     * Si une modification de série/année est nécessaire, il vaudrait mieux supprimer et recréer l'élève
     * pour garantir la cohérence des séquences de matricules.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        // Récupérer l'élève actuel pour le matricule et numero_sequentiel_serie
        $currentEleve = $this->getById($id);
        if (!$currentEleve) return false;

        $this->db->query("UPDATE eleves SET
                            nom = :nom, prenom = :prenom, date_naissance = :date_naissance, sexe = :sexe,
                            serie_id = :serie_id, lycee_id = :lycee_id, annee_scolaire_id = :annee_scolaire_id,
                            photo = :photo,
                            empreinte1 = :empreinte1, empreinte2 = :empreinte2, empreinte3 = :empreinte3, empreinte4 = :empreinte4, empreinte5 = :empreinte5,
                            empreinte6 = :empreinte6, empreinte7 = :empreinte7, empreinte8 = :empreinte8, empreinte9 = :empreinte9, empreinte10 = :empreinte10,
                            centre_id = :centre_id
                            -- Matricule et numero_sequentiel_serie ne sont pas modifiés ici pour simplicité.
                          WHERE id = :id");

        $this->db->bind(':id', (int)$id);
        // $this->db->bind(':matricule', $data['matricule']); // Ne pas mettre à jour le matricule directement ici
        $this->db->bind(':nom', $data['nom']);
        $this->db->bind(':prenom', $data['prenom']);
        $this->db->bind(':date_naissance', $data['date_naissance']);
        $this->db->bind(':sexe', $data['sexe']);
        $this->db->bind(':serie_id', (int)$data['serie_id']);
        $this->db->bind(':lycee_id', (int)$data['lycee_id']);
        $this->db->bind(':annee_scolaire_id', (int)$data['annee_scolaire_id']);
        $this->db->bind(':photo', $data['photo'] ?? null);
        $this->db->bind(':centre_id', isset($data['centre_id']) && !empty($data['centre_id']) ? (int)$data['centre_id'] : null);

        for ($i = 1; $i <= 10; $i++) {
            $this->db->bind(":empreinte{$i}", $data["empreinte{$i}"] ?? null);
        }

        return $this->db->execute();
    }

    /**
     * Supprime un élève.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // Avant de supprimer, vérifier les dépendances (notes, repartition_candidats_salles)
        $this->db->query("SELECT COUNT(*) as count FROM notes WHERE eleve_id = :id");
        $this->db->bind(':id', (int)$id);
        if ($this->db->single()->count > 0) {
            $_SESSION['error_message'] = 'Cet élève a des notes enregistrées et ne peut être supprimé.';
            return false;
        }
        // TODO: Ajouter vérification pour repartition_candidats_salles si cette table est implémentée

        $this->db->query("DELETE FROM eleves WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->execute();
    }

    /**
     * Vérifie si un matricule existe déjà pour une année scolaire donnée.
     * @param string $matricule
     * @param int $annee_scolaire_id
     * @param int|null $currentId ID de l'élève actuel à exclure (pour la mise à jour)
     * @return bool
     */
    public function matriculeExists($matricule, $annee_scolaire_id, $currentId = null) {
        $sql = "SELECT id FROM eleves WHERE matricule = :matricule AND annee_scolaire_id = :annee_scolaire_id";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':matricule', $matricule);
        $this->db->bind(':annee_scolaire_id', (int)$annee_scolaire_id);
        if ($currentId !== null) {
            $this->db->bind(':current_id', (int)$currentId);
        }
        return $this->db->single() ? true : false;
    }
}
?>
