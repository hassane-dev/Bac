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
            // Si aucune ligne n'existe, on en crée une par défaut (peut arriver sur une nouvelle installation)
            // Les valeurs par défaut sont NULL dans la DB, donc c'est ok.
            // Ou on pourrait insérer des valeurs vides ici.
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
     *                    Les clés doivent correspondre aux noms des colonnes.
     * @return bool True si succès, false sinon.
     */
    public function updateSettings($data) {
        // Construire la requête SQL dynamiquement basée sur les données fournies
        $fields_to_update = [];
        foreach ($data as $key => $value) {
            // S'assurer que la clé est une colonne valide pour éviter les injections SQL si les clés venaient de l'extérieur
            // Pour ce cas, les clés sont contrôlées par le contrôleur, donc c'est moins critique mais bonne pratique.
            // Colonnes autorisées :
            $allowed_columns = [
                'republique_de', 'devise_republique', 'ministere_nom',
                'office_examen_nom', 'direction_nom', 'logo_pays_path',
                'armoirie_pays_path', 'drapeau_pays_path', 'signature_directeur_path',
                'cachet_office_path', 'ville_office'
            ];
            if (in_array($key, $allowed_columns)) {
                $fields_to_update[] = "`" . $key . "` = :" . $key;
            }
        }

        if (empty($fields_to_update)) {
            return true; // Rien à mettre à jour
        }

        $sql = 'UPDATE parametres_generaux SET ' . implode(', ', $fields_to_update) . ' WHERE id = :id';

        $this->db->query($sql);
        $this->db->bind(':id', $this->settings_id);

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_columns)) { // Lier seulement les valeurs pour les colonnes autorisées
                $this->db->bind(':' . $key, $value);
            }
        }

        return $this->db->execute();
    }
}
?>
