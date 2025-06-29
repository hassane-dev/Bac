<?php

class ParametreGeneral {
    private $db;
    private $settingsId = 1; // ID fixe pour l'unique ligne de paramètres

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère les paramètres généraux.
     * @return object|false
     */
    public function getSettings() {
        $this->db->query("SELECT * FROM parametres_generaux WHERE id = :id");
        $this->db->bind(':id', $this->settingsId);
        $settings = $this->db->single();

        // S'il n'y a pas encore de ligne, on en crée une vide pour éviter les erreurs
        if (!$settings) {
            return $this->initializeDefaultSettings();
        }
        return $settings;
    }

    /**
     * Initialise une ligne de paramètres par défaut si elle n'existe pas.
     * @return object
     */
    private function initializeDefaultSettings() {
        $this->db->query("INSERT IGNORE INTO parametres_generaux (id, republique_de, devise_republique, ministere_nom, office_examen_nom, direction_nom)
                          VALUES (:id, :rep, :dev, :min, :off, :dir)");
        $this->db->bind(':id', $this->settingsId);
        $this->db->bind(':rep', 'République de...');
        $this->db->bind(':dev', 'Devise...');
        $this->db->bind(':min', 'Ministère de...');
        $this->db->bind(':off', 'Office des Examens...');
        $this->db->bind(':dir', 'Direction de...');
        $this->db->execute();

        // Récupérer les paramètres fraîchement insérés (ou existants si INSERT IGNORE n'a rien fait)
        $this->db->query("SELECT * FROM parametres_generaux WHERE id = :id");
        $this->db->bind(':id', $this->settingsId);
        return $this->db->single();
    }

    /**
     * Met à jour les paramètres généraux.
     * @param array $data Données des paramètres.
     * @return bool
     */
    public function updateSettings($data) {
        $this->db->query("UPDATE parametres_generaux SET
                            republique_de = :republique_de,
                            devise_republique = :devise_republique,
                            ministere_nom = :ministere_nom,
                            office_examen_nom = :office_examen_nom,
                            direction_nom = :direction_nom,
                            logo_pays_path = :logo_pays_path,
                            armoirie_pays_path = :armoirie_pays_path,
                            drapeau_pays_path = :drapeau_pays_path,
                            signature_directeur_path = :signature_directeur_path,
                            cachet_office_path = :cachet_office_path,
                            ville_office = :ville_office
                          WHERE id = :id");

        $this->db->bind(':id', $this->settingsId);
        $this->db->bind(':republique_de', $data['republique_de'] ?? null);
        $this->db->bind(':devise_republique', $data['devise_republique'] ?? null);
        $this->db->bind(':ministere_nom', $data['ministere_nom'] ?? null);
        $this->db->bind(':office_examen_nom', $data['office_examen_nom'] ?? null);
        $this->db->bind(':direction_nom', $data['direction_nom'] ?? null);
        $this->db->bind(':logo_pays_path', $data['logo_pays_path'] ?? null);
        $this->db->bind(':armoirie_pays_path', $data['armoirie_pays_path'] ?? null);
        $this->db->bind(':drapeau_pays_path', $data['drapeau_pays_path'] ?? null);
        $this->db->bind(':signature_directeur_path', $data['signature_directeur_path'] ?? null);
        $this->db->bind(':cachet_office_path', $data['cachet_office_path'] ?? null);
        $this->db->bind(':ville_office', $data['ville_office'] ?? null);

        return $this->db->execute();
    }

    /**
     * Met à jour uniquement le chemin d'un fichier spécifique.
     * @param string $fieldName Le nom du champ dans la base de données (ex: 'logo_pays_path')
     * @param string $filePath Le chemin du fichier
     * @return bool
     */
    public function updateFilePath($fieldName, $filePath) {
        // Valider que $fieldName est un champ autorisé pour éviter les injections SQL si on construisait la requête dynamiquement.
        // Ici, on utilise un bind, donc c'est plus sûr, mais une vérification explicite du nom de champ est une bonne pratique.
        $allowedFields = [
            'logo_pays_path', 'armoirie_pays_path', 'drapeau_pays_path',
            'signature_directeur_path', 'cachet_office_path'
        ];
        if (!in_array($fieldName, $allowedFields)) {
            return false;
        }

        $this->db->query("UPDATE parametres_generaux SET {$fieldName} = :file_path WHERE id = :id");
        $this->db->bind(':file_path', $filePath);
        $this->db->bind(':id', $this->settingsId);
        return $this->db->execute();
    }
}
?>
