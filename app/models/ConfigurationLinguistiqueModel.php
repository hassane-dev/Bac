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
            // Si aucune ligne n'existe, créer une ligne par défaut.
            // Les valeurs par défaut sont définies dans le schéma SQL.
            // (langues_actives_json='["fr", "ar"]', langue_principale='fr', langue_secondaire='ar', mode_affichage_documents='bilingue')
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

        // Désérialiser langues_actives_json
        if ($settings && isset($settings->langues_actives_json)) {
            $decoded_langs = json_decode($settings->langues_actives_json, true);
            // S'assurer que c'est un tableau même si json_decode échoue ou retourne null
            $settings->langues_actives_array = is_array($decoded_langs) ? $decoded_langs : [];
        } else if ($settings) {
            $settings->langues_actives_array = [];
        }

        return $settings;
    }

    /**
     * Met à jour les configurations linguistiques.
     * @param array $data Tableau associatif des données.
     *                    Doit contenir: 'langues_actives_array', 'langue_principale',
     *                                   'langue_secondaire', 'mode_affichage_documents'.
     * @return bool True si succès, false sinon.
     */
    public function updateSettings($data) {
        // S'assurer que langues_actives_array est bien un tableau avant de l'encoder
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
        // langue_secondaire peut être NULL, donc vérifier si la clé existe et n'est pas vide
        $this->db->bind(':langue_secondaire', (!empty($data['langue_secondaire'])) ? $data['langue_secondaire'] : null);
        $this->db->bind(':mode_affichage_documents', $data['mode_affichage_documents']);

        return $this->db->execute();
    }
}
?>
