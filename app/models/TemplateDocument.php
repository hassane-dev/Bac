<?php

class TemplateDocument {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère tous les éléments d'un template de document spécifique, y compris le fond.
     * @param string $type_document 'diplome', 'releve', 'carte'
     * @return array ['elements' => [], 'fond' => null|object]
     */
    public function getElementsByType($type_document) {
        $this->db->query("SELECT * FROM templates_documents
                          WHERE type_document = :type_document
                          ORDER BY est_parametre_fond DESC, element ASC"); // Fond en premier s'il existe
        $this->db->bind(':type_document', $type_document);
        $results = $this->db->resultSet();

        $output = ['elements' => [], 'fond' => null];
        foreach ($results as $row) {
            if ($row->est_parametre_fond) {
                $output['fond'] = $row;
            } else {
                $output['elements'][] = $row;
            }
        }
        return $output;
    }

    /**
     * Récupère un élément spécifique par son ID.
     * @param int $id
     * @return object|false
     */
    public function getElementById($id) {
        $this->db->query("SELECT * FROM templates_documents WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Ajoute un nouvel élément textuel à un template.
     * @param array $data
     * @return bool
     */
    public function addElement($data) {
        $this->db->query("INSERT INTO templates_documents
                            (type_document, element, position_x, position_y, taille_police, police, couleur, langue_affichage, visible, est_parametre_fond)
                          VALUES
                            (:type_document, :element, :position_x, :position_y, :taille_police, :police, :couleur, :langue_affichage, :visible, FALSE)");

        $this->db->bind(':type_document', $data['type_document']);
        $this->db->bind(':element', $data['element']);
        $this->db->bind(':position_x', $data['position_x']);
        $this->db->bind(':position_y', $data['position_y']);
        $this->db->bind(':taille_police', $data['taille_police']);
        $this->db->bind(':police', $data['police'] ?? 'helvetica');
        $this->db->bind(':couleur', $data['couleur'] ?? '#000000');
        $this->db->bind(':langue_affichage', $data['langue_affichage']);
        $this->db->bind(':visible', $data['visible'] ?? true, PDO::PARAM_BOOL);

        return $this->db->execute();
    }

    /**
     * Met à jour un élément textuel existant.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateElement($id, $data) {
        $this->db->query("UPDATE templates_documents SET
                            element = :element,
                            position_x = :position_x,
                            position_y = :position_y,
                            taille_police = :taille_police,
                            police = :police,
                            couleur = :couleur,
                            langue_affichage = :langue_affichage,
                            visible = :visible
                          WHERE id = :id AND est_parametre_fond = FALSE");

        $this->db->bind(':id', $id);
        $this->db->bind(':element', $data['element']);
        $this->db->bind(':position_x', $data['position_x']);
        $this->db->bind(':position_y', $data['position_y']);
        $this->db->bind(':taille_police', $data['taille_police']);
        $this->db->bind(':police', $data['police'] ?? 'helvetica');
        $this->db->bind(':couleur', $data['couleur'] ?? '#000000');
        $this->db->bind(':langue_affichage', $data['langue_affichage']);
        $this->db->bind(':visible', $data['visible'] ?? true, PDO::PARAM_BOOL);

        return $this->db->execute();
    }

    /**
     * Met à jour ou insère les paramètres de fond pour un type de document.
     * @param string $type_document
     * @param array $data ['type_fond', 'valeur_fond', 'opacite_fond']
     * @return bool
     */
    public function saveBackgroundSettings($type_document, $data) {
        $this->db->query("INSERT INTO templates_documents
                            (type_document, element, est_parametre_fond, type_fond, valeur_fond, opacite_fond, visible, position_x, position_y)
                          VALUES
                            (:type_document, '_BACKGROUND_', TRUE, :type_fond, :valeur_fond, :opacite_fond, TRUE, 0, 0)
                          ON DUPLICATE KEY UPDATE
                            type_fond = VALUES(type_fond),
                            valeur_fond = VALUES(valeur_fond),
                            opacite_fond = VALUES(opacite_fond)");

        $this->db->bind(':type_document', $type_document);
        $this->db->bind(':type_fond', $data['type_fond'] ?? null);
        $this->db->bind(':valeur_fond', $data['valeur_fond'] ?? null);
        $this->db->bind(':opacite_fond', $data['opacite_fond'] ?? 1.0);

        return $this->db->execute();
    }

    /**
     * Supprime un élément de template (textuel).
     * @param int $id
     * @return bool
     */
    public function deleteElement($id) {
        $this->db->query("DELETE FROM templates_documents WHERE id = :id AND est_parametre_fond = FALSE");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>
