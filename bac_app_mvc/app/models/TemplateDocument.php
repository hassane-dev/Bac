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
                          ORDER BY est_parametre_fond DESC, element ASC");
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
        // S'il n'y a pas d'entrée pour le fond, on en crée un objet vide par défaut
        if ($output['fond'] === null) {
            $output['fond'] = (object) [
                'type_document' => $type_document,
                'element' => '_BACKGROUND_',
                'est_parametre_fond' => true,
                'type_fond' => null, // ou 'couleur' par défaut
                'valeur_fond' => null, // ou '#FFFFFF' par défaut
                'opacite_fond' => 1.0,
                'visible' => true, // Le fond est toujours "visible" s'il est défini
                'id' => null // Pas d'ID car non existant en BDD
            ];
        }
        return $output;
    }

    public function getElementById($id) {
        $this->db->query("SELECT * FROM templates_documents WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    public function addElement($data) {
        $this->db->query("INSERT INTO templates_documents
                            (type_document, element, position_x, position_y, taille_police, police, couleur, langue_affichage, visible, est_parametre_fond)
                          VALUES
                            (:type_document, :element, :position_x, :position_y, :taille_police, :police, :couleur, :langue_affichage, :visible, FALSE)");

        $this->db->bind(':type_document', $data['type_document']);
        $this->db->bind(':element', $data['element']);
        $this->db->bind(':position_x', (int)$data['position_x']);
        $this->db->bind(':position_y', (int)$data['position_y']);
        $this->db->bind(':taille_police', (int)$data['taille_police']);
        $this->db->bind(':police', $data['police'] ?? 'helvetica');
        $this->db->bind(':couleur', $data['couleur'] ?? '#000000');
        $this->db->bind(':langue_affichage', $data['langue_affichage']);
        $this->db->bind(':visible', isset($data['visible']) ? (int)$data['visible'] : 1, PDO::PARAM_INT);

        return $this->db->execute();
    }

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

        $this->db->bind(':id', (int)$id);
        $this->db->bind(':element', $data['element']);
        $this->db->bind(':position_x', (int)$data['position_x']);
        $this->db->bind(':position_y', (int)$data['position_y']);
        $this->db->bind(':taille_police', (int)$data['taille_police']);
        $this->db->bind(':police', $data['police'] ?? 'helvetica');
        $this->db->bind(':couleur', $data['couleur'] ?? '#000000');
        $this->db->bind(':langue_affichage', $data['langue_affichage']);
        $this->db->bind(':visible', isset($data['visible']) ? (int)$data['visible'] : 1, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function saveBackgroundSettings($type_document, $data) {
        // Vérifier si une entrée pour le fond existe déjà
        $this->db->query("SELECT id FROM templates_documents WHERE type_document = :type_document AND est_parametre_fond = TRUE");
        $this->db->bind(':type_document', $type_document);
        $existingFond = $this->db->single();

        if ($existingFond) {
            // Mettre à jour
            $this->db->query("UPDATE templates_documents SET
                                type_fond = :type_fond,
                                valeur_fond = :valeur_fond,
                                opacite_fond = :opacite_fond
                              WHERE id = :id");
            $this->db->bind(':id', $existingFond->id);
        } else {
            // Insérer
            $this->db->query("INSERT INTO templates_documents
                                (type_document, element, est_parametre_fond, type_fond, valeur_fond, opacite_fond, visible, position_x, position_y, taille_police, police, couleur, langue_affichage)
                              VALUES
                                (:type_document, '_BACKGROUND_', TRUE, :type_fond, :valeur_fond, :opacite_fond, TRUE, 0, 0, NULL, NULL, NULL, NULL)");
        }

        $this->db->bind(':type_document', $type_document);
        $this->db->bind(':type_fond', $data['type_fond'] ?? null); // type_fond peut être null si on veut juste effacer
        $this->db->bind(':valeur_fond', $data['valeur_fond'] ?? null);
        $this->db->bind(':opacite_fond', isset($data['opacite_fond']) ? (float)$data['opacite_fond'] : 1.0);

        return $this->db->execute();
    }

    public function deleteElement($id) {
        $this->db->query("DELETE FROM templates_documents WHERE id = :id AND est_parametre_fond = FALSE");
        $this->db->bind(':id', (int)$id);
        return $this->db->execute();
    }
}
?>
