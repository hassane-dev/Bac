<?php

class TemplateDocumentModel {
    private $db;
    // Valeur conventionnelle pour l'élément représentant les paramètres de fond
    const BACKGROUND_ELEMENT_NAME = '__DOCUMENT_BACKGROUND__';

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Récupère tous les éléments (non-fond) pour un type de document donné.
     * @param string $type_document ENUM('diplome', 'releve', 'carte')
     * @return array Liste des éléments.
     */
    public function getElementsByType($type_document) {
        $this->db->query('SELECT * FROM templates_documents
                          WHERE type_document = :type_document
                          AND est_parametre_fond = FALSE
                          ORDER BY element ASC, langue_affichage ASC');
        $this->db->bind(':type_document', $type_document);
        return $this->db->resultSet();
    }

    /**
     * Récupère un élément spécifique par son ID.
     * @param int $id
     * @return object|false
     */
    public function getElementById($id) {
        $this->db->query('SELECT * FROM templates_documents WHERE id = :id AND est_parametre_fond = FALSE');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Crée un nouvel élément de template (non-fond).
     * @param array $data (type_document, element, position_x, position_y, taille_police, police, couleur, langue_affichage, visible)
     * @return bool
     */
    public function createElement($data) {
        // Vérifier l'unicité
        $this->db->query('SELECT id FROM templates_documents
                          WHERE type_document = :type_document
                          AND element = :element
                          AND langue_affichage = :langue_affichage
                          AND est_parametre_fond = FALSE');
        $this->db->bind(':type_document', $data['type_document']);
        $this->db->bind(':element', $data['element']);
        $this->db->bind(':langue_affichage', $data['langue_affichage']);
        if ($this->db->single()) {
            return false; // Conflit d'unicité
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
        $this->db->bind(':visible', $data['visible'] ?? true, PDO::PARAM_BOOL);

        return $this->db->execute();
    }

    /**
     * Met à jour un élément de template existant (non-fond).
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateElement($id, $data) {
        $element_to_update = $this->getElementById($id);
        if (!$element_to_update) return false;

        // Vérifier l'unicité si les champs concernés sont modifiés
        if ( (isset($data['element']) && $data['element'] !== $element_to_update->element) ||
             (isset($data['langue_affichage']) && $data['langue_affichage'] !== $element_to_update->langue_affichage) )
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
                return false; // Conflit d'unicité
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
        $this->db->bind(':visible', $data['visible'] ?? $element_to_update->visible, PDO::PARAM_BOOL);

        return $this->db->execute();
    }

    /**
     * Supprime un élément de template (non-fond).
     * @param int $id
     * @return bool
     */
    public function deleteElement($id) {
        $this->db->query('DELETE FROM templates_documents WHERE id = :id AND est_parametre_fond = FALSE');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // --- Gestion des paramètres de fond ---

    /**
     * Récupère les paramètres de fond pour un type de document.
     * @param string $type_document
     * @return object|false
     */
    public function getBackgroundSettings($type_document) {
        $this->db->query('SELECT * FROM templates_documents
                          WHERE type_document = :type_document
                          AND est_parametre_fond = TRUE
                          AND element = :element_name'); // Utiliser le nom conventionnel
        $this->db->bind(':type_document', $type_document);
        $this->db->bind(':element_name', self::BACKGROUND_ELEMENT_NAME);
        $settings = $this->db->single();

        if (!$settings) {
            // Créer une entrée par défaut si elle n'existe pas
            $default_data = [
                'type_document' => $type_document,
                'element' => self::BACKGROUND_ELEMENT_NAME,
                'est_parametre_fond' => true,
                'type_fond' => 'couleur', // Ou null si on préfère forcer un choix explicite
                'valeur_fond' => '#FFFFFF', // Blanc par défaut
                'opacite_fond' => 1.0,
                'langue_affichage' => 'fr', // Non pertinent pour le fond global, mais la colonne est NOT NULL si on n'a pas changé le schéma
                'visible' => true, // Le fond est visible par défaut
                'position_x' => 0, 'position_y' => 0, 'taille_police' => 0, 'police' => '', 'couleur' => '' // Non pertinent
            ];
            if ($this->createOrUpdateBackgroundSettings($type_document, $default_data)) {
                 return $this->getBackgroundSettings($type_document); // Réessayer de récupérer
            }
            return false;
        }
        return $settings;
    }

    /**
     * Crée ou met à jour les paramètres de fond pour un type de document.
     * @param string $type_document
     * @param array $data (type_fond, valeur_fond, opacite_fond)
     * @return bool
     */
    public function createOrUpdateBackgroundSettings($type_document, $data) {
        $existing = $this->getBackgroundSettings($type_document); // Cette méthode crée déjà une ligne si inexistante.
                                                               // Donc ici, on est presque toujours en mode UPDATE si getBackgroundSettings a réussi.
                                                               // Sauf si getBackgroundSettings a retourné false après échec de création.

        if ($existing && $existing->id) { // Mise à jour
            $sql = 'UPDATE templates_documents SET
                        type_fond = :type_fond,
                        valeur_fond = :valeur_fond,
                        opacite_fond = :opacite_fond,
                        visible = :visible
                        -- Les autres champs comme position, police, etc. ne sont pas pertinents pour le fond global.
                        -- langue_affichage est mis à une valeur fixe ou ignoré pour le fond.
                    WHERE type_document = :type_document
                    AND est_parametre_fond = TRUE
                    AND element = :element_name';
        } else { // Création (si getBackgroundSettings a échoué à créer la ligne par défaut, ou pour forcer une création)
             $sql = 'INSERT INTO templates_documents
                        (type_document, element, est_parametre_fond, type_fond, valeur_fond, opacite_fond, visible, langue_affichage,
                         position_x, position_y, taille_police, police, couleur)
                    VALUES
                        (:type_document, :element_name, TRUE, :type_fond, :valeur_fond, :opacite_fond, :visible, :langue_affichage,
                         0, 0, 0, "", "")';
        }

        $this->db->query($sql);
        $this->db->bind(':type_document', $type_document);
        $this->db->bind(':element_name', self::BACKGROUND_ELEMENT_NAME);
        $this->db->bind(':type_fond', $data['type_fond'] ?? null);
        $this->db->bind(':valeur_fond', $data['valeur_fond'] ?? null);
        $this->db->bind(':opacite_fond', $data['opacite_fond'] ?? 1.0);
        $this->db->bind(':visible', $data['visible'] ?? true, PDO::PARAM_BOOL);
        if (!($existing && $existing->id)) { // Si c'est une insertion
            $this->db->bind(':langue_affichage', $data['langue_affichage'] ?? 'fr'); // Valeur par défaut si insertion
        }

        return $this->db->execute();
    }
}
?>
