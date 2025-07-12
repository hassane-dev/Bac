<?php

class TemplateDocumentModel {
    private $db;
    const BACKGROUND_ELEMENT_NAME = '__DOCUMENT_BACKGROUND__';

    public function __construct($database) {
        $this->db = $database;
    }

    public function getElementsByType($type_document) {
        $this->db->query('SELECT * FROM templates_documents
                          WHERE type_document = :type_document
                          AND est_parametre_fond = FALSE
                          ORDER BY element ASC, langue_affichage ASC');
        $this->db->bind(':type_document', $type_document);
        return $this->db->resultSet();
    }

    public function getElementById($id) {
        $this->db->query('SELECT * FROM templates_documents WHERE id = :id AND est_parametre_fond = FALSE');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function createElement($data) {
        $this->db->query('SELECT id FROM templates_documents
                          WHERE type_document = :type_document
                          AND element = :element
                          AND langue_affichage = :langue_affichage
                          AND est_parametre_fond = FALSE');
        $this->db->bind(':type_document', $data['type_document']);
        $this->db->bind(':element', $data['element']);
        $this->db->bind(':langue_affichage', $data['langue_affichage']);
        if ($this->db->single()) {
            return false;
        }

        $sql = 'INSERT INTO templates_documents
                    (type_document, element, position_x, position_y, taille_police, police, couleur, langue_affichage, visible, est_parametre_fond)
                VALUES
                    (:type_document, :element, :position_x, :position_y, :taille_police, :police, :couleur, :langue_affichage, :visible, FALSE)';

        $this->db->query($sql);
        $this->db->bind(':type_document', $data['type_document']);
        $this->db->bind(':element', $data['element']);
        $this->db->bind(':position_x', $data['position_x'] ?? 0);
        $this->db->bind(':position_y', $data['position_y'] ?? 0);
        $this->db->bind(':taille_police', $data['taille_police'] ?? 10);
        $this->db->bind(':police', $data['police'] ?? 'helvetica');
        $this->db->bind(':couleur', $data['couleur'] ?? '#000000');
        $this->db->bind(':langue_affichage', $data['langue_affichage'] ?? 'fr_ar');
        $this->db->bind(':visible', isset($data['visible']) ? (bool)$data['visible'] : true, PDO::PARAM_BOOL);

        return $this->db->execute();
    }

    public function updateElement($id, $data) {
        $element_to_update = $this->getElementById($id);
        if (!$element_to_update) return false;

        if ( (isset($data['element']) && $data['element'] !== $element_to_update->element) ||
             (isset($data['langue_affichage']) && $data['langue_affichage'] !== $element_to_update->langue_affichage) ||
             (isset($data['type_document']) && $data['type_document'] !== $element_to_update->type_document) )
        {
            $this->db->query('SELECT id FROM templates_documents
                              WHERE type_document = :type_document
                              AND element = :element
                              AND langue_affichage = :langue_affichage
                              AND est_parametre_fond = FALSE
                              AND id != :id');
            $this->db->bind(':type_document', $data['type_document'] ?? $element_to_update->type_document);
            $this->db->bind(':element', $data['element'] ?? $element_to_update->element);
            $this->db->bind(':langue_affichage', $data['langue_affichage'] ?? $element_to_update->langue_affichage);
            $this->db->bind(':id', $id);
            if ($this->db->single()) {
                return false;
            }
        }

        $sql = 'UPDATE templates_documents SET
                    element = :element,
                    position_x = :position_x,
                    position_y = :position_y,
                    taille_police = :taille_police,
                    police = :police,
                    couleur = :couleur,
                    langue_affichage = :langue_affichage,
                    visible = :visible
                WHERE id = :id AND est_parametre_fond = FALSE';

        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':element', $data['element'] ?? $element_to_update->element);
        $this->db->bind(':position_x', $data['position_x'] ?? $element_to_update->position_x);
        $this->db->bind(':position_y', $data['position_y'] ?? $element_to_update->position_y);
        $this->db->bind(':taille_police', $data['taille_police'] ?? $element_to_update->taille_police);
        $this->db->bind(':police', $data['police'] ?? $element_to_update->police);
        $this->db->bind(':couleur', $data['couleur'] ?? $element_to_update->couleur);
        $this->db->bind(':langue_affichage', $data['langue_affichage'] ?? $element_to_update->langue_affichage);
        $this->db->bind(':visible', isset($data['visible']) ? (bool)$data['visible'] : (bool)$element_to_update->visible, PDO::PARAM_BOOL);

        return $this->db->execute();
    }

    public function deleteElement($id) {
        $this->db->query('DELETE FROM templates_documents WHERE id = :id AND est_parametre_fond = FALSE');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getBackgroundSettings($type_document) {
        $this->db->query('SELECT * FROM templates_documents
                          WHERE type_document = :type_document
                          AND est_parametre_fond = TRUE
                          AND element = :element_name');
        $this->db->bind(':type_document', $type_document);
        $this->db->bind(':element_name', self::BACKGROUND_ELEMENT_NAME);
        $settings = $this->db->single();

        if (!$settings) {
            $default_data = [
                'type_fond' => 'couleur',
                'valeur_fond' => '#FFFFFF',
                'opacite_fond' => 1.0,
                'visible' => true,
                'langue_affichage' => 'fr',
            ];
            if ($this->createOrUpdateBackgroundSettings($type_document, $default_data, true)) {
                 return $this->getBackgroundSettings($type_document);
            }
            error_log("Impossible de créer l'entrée de fond par défaut pour le type: $type_document");
            return false;
        }
        return $settings;
    }

    public function createOrUpdateBackgroundSettings($type_document, $data, $force_create = false) {
        $existing = null;
        if (!$force_create) {
            $this->db->query('SELECT id FROM templates_documents WHERE type_document = :type_document AND est_parametre_fond = TRUE AND element = :element_name');
            $this->db->bind(':type_document', $type_document);
            $this->db->bind(':element_name', self::BACKGROUND_ELEMENT_NAME);
            $existing = $this->db->single();
        }

        if ($existing && !$force_create) {
            $sql = 'UPDATE templates_documents SET
                        type_fond = :type_fond,
                        valeur_fond = :valeur_fond,
                        opacite_fond = :opacite_fond,
                        visible = :visible
                    WHERE type_document = :type_document
                    AND est_parametre_fond = TRUE
                    AND element = :element_name';
        } else {
             $sql = 'INSERT INTO templates_documents
                        (type_document, element, est_parametre_fond, type_fond, valeur_fond, opacite_fond, visible,
                         langue_affichage, position_x, position_y, taille_police, police, couleur)
                    VALUES
                        (:type_document, :element_name, TRUE, :type_fond, :valeur_fond, :opacite_fond, :visible,
                         :langue_affichage, 0, 0, 0, "", "")';
        }

        $this->db->query($sql);
        $this->db->bind(':type_document', $type_document);
        $this->db->bind(':element_name', self::BACKGROUND_ELEMENT_NAME);
        $this->db->bind(':type_fond', $data['type_fond'] ?? null);
        $this->db->bind(':valeur_fond', $data['valeur_fond'] ?? null);
        $this->db->bind(':opacite_fond', (float)($data['opacite_fond'] ?? 1.0));
        $this->db->bind(':visible', isset($data['visible']) ? (bool)$data['visible'] : true, PDO::PARAM_BOOL);

        if (!($existing && !$force_create)) {
            $this->db->bind(':langue_affichage', $data['langue_affichage'] ?? 'fr');
        }

        return $this->db->execute();
    }
}
?>
