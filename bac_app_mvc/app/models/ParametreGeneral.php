<?php

class ParametreGeneral {
    private $db;
    private $settingsId = 1; // ID fixe pour l'unique ligne de paramètres

    public function __construct($db) {
        $this->db = $db;
        // S'assurer que la ligne de configuration existe, sinon l'initialiser.
        $this->ensureSettingsExist();
    }

    /**
     * S'assure que la ligne de configuration existe dans la base de données.
     * Crée une ligne avec des valeurs par défaut si elle n'existe pas.
     */
    private function ensureSettingsExist() {
        $this->db->query("SELECT id FROM parametres_generaux WHERE id = :id");
        $this->db->bind(':id', $this->settingsId);
        if (!$this->db->single()) {
            $this->db->query("INSERT INTO parametres_generaux (id, republique_de, devise_republique, ministere_nom, office_examen_nom, direction_nom, ville_office)
                              VALUES (:id, :rep, :dev, :min, :off, :dir, :ville)");
            $this->db->bind(':id', $this->settingsId);
            $this->db->bind(':rep', 'République de...'); // Valeur par défaut
            $this->db->bind(':dev', 'Devise...');      // Valeur par défaut
            $this->db->bind(':min', 'Ministère de...'); // Valeur par défaut
            $this->db->bind(':off', 'Office des Examens...'); // Valeur par défaut
            $this->db->bind(':dir', 'Direction de...'); // Valeur par défaut
            $this->db->bind(':ville', 'Ville...'); // Valeur par défaut
            $this->db->execute();
        }
    }

    /**
     * Récupère les paramètres généraux.
     * @return object|false
     */
    public function getSettings() {
        $this->db->query("SELECT * FROM parametres_generaux WHERE id = :id");
        $this->db->bind(':id', $this->settingsId);
        return $this->db->single(); // Devrait toujours retourner un objet après ensureSettingsExist
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
     * Utilisé en interne par le contrôleur après un upload réussi.
     * @param string $fieldName Le nom du champ dans la base de données (ex: 'logo_pays_path')
     * @param string|null $filePath Le chemin du fichier, ou null pour effacer
     * @return bool
     */
    public function updateFilePath($fieldName, $filePath) {
        $allowedFields = [
            'logo_pays_path', 'armoirie_pays_path', 'drapeau_pays_path',
            'signature_directeur_path', 'cachet_office_path'
        ];
        if (!in_array($fieldName, $allowedFields)) {
            error_log("Tentative de mise à jour d'un champ non autorisé dans ParametreGeneral: " . $fieldName);
            return false;
        }

        $this->db->query("UPDATE parametres_generaux SET {$fieldName} = :file_path WHERE id = :id");
        $this->db->bind(':file_path', $filePath); // Peut être null pour effacer
        $this->db->bind(':id', $this->settingsId);
        return $this->db->execute();
    }
}
?>
