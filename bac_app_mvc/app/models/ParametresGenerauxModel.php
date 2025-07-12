<?php

class ParametresGenerauxModel {
    private $db;
    private $settings_id = 1; // ID fixe pour l'unique ligne de paramètres

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Récupère les paramètres généraux.
     * Crée une ligne par défaut si elle n'existe pas.
     * @return object|false L'objet des paramètres ou false en cas d'erreur.
     */
    public function getSettings() {
        $this->db->query('SELECT * FROM parametres_generaux WHERE id = :id');
        $this->db->bind(':id', $this->settings_id);
        $settings = $this->db->single();

        if (!$settings) {
            $this->db->query('INSERT IGNORE INTO parametres_generaux (id) VALUES (:id)');
            $this->db->bind(':id', $this->settings_id);
            if ($this->db->execute()) {
                $this->db->query('SELECT * FROM parametres_generaux WHERE id = :id');
                $this->db->bind(':id', $this->settings_id);
                $settings = $this->db->single();
            } else {
                error_log("Impossible de créer la ligne de paramètres par défaut.");
                return false;
            }
        }
        return $settings;
    }

    /**
     * Met à jour les paramètres généraux.
     * @param array $data Tableau associatif des données à mettre à jour.
     * @return bool True si succès, false sinon.
     */
    public function updateSettings($data) {
        $fields_to_update = [];
        $allowed_columns = [
            'republique_de', 'devise_republique', 'ministere_nom',
            'office_examen_nom', 'direction_nom', 'logo_pays_path',
            'armoirie_pays_path', 'drapeau_pays_path', 'signature_directeur_path',
            'cachet_office_path', 'ville_office'
        ];

        $bind_data = [];
        foreach ($allowed_columns as $column) {
            if (array_key_exists($column, $data)) {
                $fields_to_update[] = "`" . $column . "` = :" . $column;
                $bind_data[$column] = $data[$column];
            }
        }

        if (empty($fields_to_update)) {
            return true;
        }

        $sql = 'UPDATE parametres_generaux SET ' . implode(', ', $fields_to_update) . ' WHERE id = :id';

        $this->db->query($sql);
        $this->db->bind(':id', $this->settings_id);

        foreach ($bind_data as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }

        return $this->db->execute();
    }
}
?>
