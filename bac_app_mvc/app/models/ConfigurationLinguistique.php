<?php

class ConfigurationLinguistique {
    private $db;
    private $settingsId = 1; // ID fixe pour l'unique ligne de configuration

    public function __construct($db) {
        $this->db = $db;
        $this->ensureSettingsExist();
    }

    private function ensureSettingsExist() {
        $this->db->query("SELECT id FROM configurations_linguistiques WHERE id = :id");
        $this->db->bind(':id', $this->settingsId);
        if (!$this->db->single()) {
            $this->db->query("INSERT INTO configurations_linguistiques (id, langues_actives_json, langue_principale, langue_secondaire, mode_affichage_documents)
                              VALUES (:id, :default_json, :default_lang, :default_secondary, :default_mode)");
            $this->db->bind(':id', $this->settingsId);
            $this->db->bind(':default_json', json_encode(['fr', 'ar']));
            $this->db->bind(':default_lang', 'fr');
            $this->db->bind(':default_secondary', 'ar');
            $this->db->bind(':default_mode', 'bilingue');
            $this->db->execute();
        }
    }

    public function getSettings() {
        $this->db->query("SELECT * FROM configurations_linguistiques WHERE id = :id");
        $this->db->bind(':id', $this->settingsId);
        $settings = $this->db->single();

        if ($settings) {
            $settings->langues_actives_array = json_decode($settings->langues_actives_json, true) ?? [];
        } else { // Au cas où ensureSettingsExist n'aurait pas fonctionné ou si la ligne est supprimée manuellement
             $settings = (object) [
                'id' => $this->settingsId,
                'langues_actives_json' => json_encode(['fr', 'ar']),
                'langues_actives_array' => ['fr', 'ar'],
                'langue_principale' => 'fr',
                'langue_secondaire' => 'ar',
                'mode_affichage_documents' => 'bilingue'
            ];
        }
        return $settings;
    }

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

    public function getAvailableLanguagesFromFiles() {
        $langDir = APP_ROOT . '/lang/';
        $availableLangs = [];
        if (is_dir($langDir)) {
            $files = scandir($langDir);
            foreach ($files as $file) {
                if (strpos($file, '.php') !== false && $file !== '.' && $file !== '..' && $file !== 'index.php' && $file !== '.gitkeep') {
                    $code = str_replace('.php', '', $file);
                    $translations = include $langDir . $file;
                    $name = $translations['language_name_native'] ??
                            ($translations['language_name_in_french'] ?? strtoupper($code));
                    $availableLangs[$code] = $name;
                }
            }
        }
        return $availableLangs;
    }
}
?>
