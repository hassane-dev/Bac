<?php

class ConfigurationLinguistiqueModel {
    private $db;
    private $settings_id = 1; // ID fixe pour l'unique ligne de paramètres

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Récupère les configurations linguistiques.
     * Crée une ligne par défaut si elle n'existe pas.
     * @return object|false L'objet des configurations ou false en cas d'erreur.
     */
    public function getSettings() {
        $this->db->query('SELECT * FROM configurations_linguistiques WHERE id = :id');
        $this->db->bind(':id', $this->settings_id);
        $settings = $this->db->single();

        if (!$settings) {
            $default_langues_actives_json = json_encode(['fr', 'ar']);
            $default_langue_principale = 'fr';
            $default_langue_secondaire = 'ar';
            $default_mode_affichage = 'bilingue';

            $this->db->query('INSERT IGNORE INTO configurations_linguistiques (id, langues_actives_json, langue_principale, langue_secondaire, mode_affichage_documents)
                              VALUES (:id, :langues_actives_json, :langue_principale, :langue_secondaire, :mode_affichage_documents)');
            $this->db->bind(':id', $this->settings_id);
            $this->db->bind(':langues_actives_json', $default_langues_actives_json);
            $this->db->bind(':langue_principale', $default_langue_principale);
            $this->db->bind(':langue_secondaire', $default_langue_secondaire);
            $this->db->bind(':mode_affichage_documents', $default_mode_affichage);

            if ($this->db->execute()) {
                $this->db->query('SELECT * FROM configurations_linguistiques WHERE id = :id');
                $this->db->bind(':id', $this->settings_id);
                $settings = $this->db->single();
            } else {
                error_log("Impossible de créer la ligne de configurations linguistiques par défaut.");
                return false;
            }
        }

        if ($settings && isset($settings->langues_actives_json)) {
            $decoded_langs = json_decode($settings->langues_actives_json, true);
            $settings->langues_actives_array = is_array($decoded_langs) ? $decoded_langs : [];
        } else if ($settings) {
            $settings->langues_actives_array = [];
        }

        return $settings;
    }

    /**
     * Met à jour les configurations linguistiques.
     * @param array $data
     * @return bool True si succès, false sinon.
     */
    public function updateSettings($data) {
        $langues_actives_json = json_encode($data['langues_actives_array'] ?? []);

        $sql = 'UPDATE configurations_linguistiques SET
                    langues_actives_json = :langues_actives_json,
                    langue_principale = :langue_principale,
                    langue_secondaire = :langue_secondaire,
                    mode_affichage_documents = :mode_affichage_documents
                WHERE id = :id';

        $this->db->query($sql);
        $this->db->bind(':id', $this->settings_id);
        $this->db->bind(':langues_actives_json', $langues_actives_json);
        $this->db->bind(':langue_principale', $data['langue_principale']);
        $this->db->bind(':langue_secondaire', (!empty($data['langue_secondaire'])) ? $data['langue_secondaire'] : null, PDO::PARAM_STR|PDO::PARAM_NULL);
        $this->db->bind(':mode_affichage_documents', $data['mode_affichage_documents']);

        return $this->db->execute();
    }
}
?>
