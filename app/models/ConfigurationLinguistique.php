<?php

class ConfigurationLinguistique {
    private $db;
    private $settingsId = 1; // ID fixe pour l'unique ligne de configuration

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère la configuration linguistique.
     * @return object|false
     */
    public function getSettings() {
        $this->db->query("SELECT * FROM configurations_linguistiques WHERE id = :id");
        $this->db->bind(':id', $this->settingsId);
        $settings = $this->db->single();

        if (!$settings) {
            // La ligne est normalement insérée par bac_app.sql, mais comme fallback:
            $this->db->query("INSERT IGNORE INTO configurations_linguistiques (id, langues_actives_json, langue_principale, mode_affichage_documents)
                              VALUES (:id, :default_json, :default_lang, :default_mode)");
            $this->db->bind(':id', $this->settingsId);
            $this->db->bind(':default_json', '["fr","ar"]');
            $this->db->bind(':default_lang', 'fr');
            $this->db->bind(':default_mode', 'bilingue');
            $this->db->execute();

            $this->db->query("SELECT * FROM configurations_linguistiques WHERE id = :id");
            $this->db->bind(':id', $this->settingsId);
            return $this->db->single();
        }
        // Décoder le JSON des langues actives
        if ($settings) {
            $settings->langues_actives_array = json_decode($settings->langues_actives_json, true) ?? [];
        }
        return $settings;
    }

    /**
     * Met à jour la configuration linguistique.
     * @param array $data
     * @return bool
     */
    public function updateSettings($data) {
        $this->db->query("UPDATE configurations_linguistiques SET
                            langues_actives_json = :langues_actives_json,
                            langue_principale = :langue_principale,
                            langue_secondaire = :langue_secondaire,
                            mode_affichage_documents = :mode_affichage_documents
                          WHERE id = :id");

        $this->db->bind(':id', $this->settingsId);
        $this->db->bind(':langues_actives_json', $data['langues_actives_json']);
        $this->db->bind(':langue_principale', $data['langue_principale']);
        $this->db->bind(':langue_secondaire', $data['langue_secondaire'] ?? null);
        $this->db->bind(':mode_affichage_documents', $data['mode_affichage_documents']);

        return $this->db->execute();
    }

    /**
     * Récupère les langues disponibles à partir des fichiers dans le dossier /lang.
     * @return array Tableau associatif ['code' => 'Nom Complet']
     */
    public function getAvailableLanguagesFromFiles() {
        $langDir = APP_ROOT . '/lang/';
        $availableLangs = [];
        $files = scandir($langDir);
        foreach ($files as $file) {
            if (strpos($file, '.php') !== false && $file !== 'index.php' && $file !== '.gitkeep') {
                $code = str_replace('.php', '', $file);
                // Essayer de récupérer un nom plus descriptif depuis le fichier de langue lui-même
                // Supposons que chaque fichier de langue a une clé comme 'language_name_native' ou 'language_name_fr'
                $translations = include $langDir . $file;
                $name = $translations['language_name_native'] ?? $translations['language_name_in_french'] ?? strtoupper($code);
                $availableLangs[$code] = $name;
            }
        }
        // Ajouter des noms plus conviviaux si non trouvés
        if (isset($availableLangs['fr']) && $availableLangs['fr'] === 'FR') $availableLangs['fr'] = 'Français';
        if (isset($availableLangs['ar']) && $availableLangs['ar'] === 'AR') $availableLangs['ar'] = 'العربية (Arabe)';
        // Ajoutez d'autres langues si nécessaire

        return $availableLangs;
    }
}
?>
