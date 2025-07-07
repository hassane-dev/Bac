<?php

class Serie {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les séries.
     * @return array
     */
    public function getAll() {
        $this->db->query("SELECT * FROM series ORDER BY code ASC");
        return $this->db->resultSet();
    }

    /**
     * Récupère une série par son ID.
     * @param int $id
     * @return object|false
     */
    public function getById($id) {
        $this->db->query("SELECT * FROM series WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    /**
     * Récupère une série par son code.
     * @param string $code
     * @return object|false
     */
    public function getByCode($code) {
        $this->db->query("SELECT * FROM series WHERE code = :code");
        $this->db->bind(':code', $code);
        return $this->db->single();
    }

    /**
     * Ajoute une nouvelle série.
     * @param array $data ['code', 'libelle']
     * @return bool
     */
    public function add($data) {
        $this->db->query("INSERT INTO series (code, libelle) VALUES (:code, :libelle)");
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':libelle', $data['libelle']);
        return $this->db->execute();
    }

    /**
     * Modifie une série existante.
     * @param int $id
     * @param array $data ['code', 'libelle']
     * @return bool
     */
    public function update($id, $data) {
        $this->db->query("UPDATE series SET code = :code, libelle = :libelle WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':libelle', $data['libelle']);
        return $this->db->execute();
    }

    /**
     * Supprime une série.
     * Avant de supprimer, il faudrait vérifier si elle est utilisée (élèves, series_matieres).
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $id = (int)$id;
        // TODO: Vérifier les dépendances (eleves, series_matieres) avant suppression
        // Exemple simple pour series_matieres:
        $this->db->query("SELECT COUNT(*) as count FROM series_matieres WHERE serie_id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->single()->count > 0) {
            $_SESSION['error_message'] = 'Cette série est liée à des matières et ne peut être supprimée. Supprimez d\'abord les liaisons.';
            return false;
        }
        // Ajouter une vérification pour les élèves si nécessaire.

        $this->db->query("DELETE FROM series WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Vérifie si un code de série existe déjà.
     * @param string $code
     * @param int|null $currentId ID de la série actuelle à exclure (pour la mise à jour)
     * @return bool
     */
    public function codeExists($code, $currentId = null) {
        $sql = "SELECT id FROM series WHERE code = :code";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':code', $code);
        if ($currentId !== null) {
            $this->db->bind(':current_id', (int)$currentId);
        }
        return $this->db->single() ? true : false;
    }

    /**
     * Récupère les matières associées à une série avec détails (coefficient, obligatoire).
     * @param int $serie_id
     * @return array
     */
    public function getMatieresAssociees($serie_id) {
        $this->db->query("SELECT m.id as matiere_id, m.code as matiere_code, m.nom as matiere_nom,
                                 sm.coefficient, sm.obligatoire
                          FROM series_matieres sm
                          INNER JOIN matieres m ON sm.matiere_id = m.id
                          WHERE sm.serie_id = :serie_id
                          ORDER BY m.nom ASC");
        $this->db->bind(':serie_id', (int)$serie_id);
        return $this->db->resultSet();
    }

    /**
     * Met à jour les matières associées à une série (coefficients, statut obligatoire).
     * @param int $serie_id
     * @param array $matieres_details Tableau de détails des matières.
     *        Chaque élément: ['matiere_id' => id, 'coefficient' => x, 'obligatoire' => bool]
     * @return bool
     */
    public function updateMatieresAssociees($serie_id, $matieres_details) {
        $serie_id = (int)$serie_id;

        // Utiliser une transaction pour assurer l'intégrité des données
        // Note: La classe Database doit supporter beginTransaction, commit, rollBack
        // et le contrôleur doit pouvoir y accéder ou le modèle doit gérer cela en interne.
        // Pour cet exemple, nous supposons que la classe Database gère cela.
        // $this->db->getDbInstance()->beginTransaction(); // Si la méthode existe dans votre classe Database
        // ou si $this->db est directement l'objet PDO : $this->db->beginTransaction();

        try {
            // 1. Supprimer toutes les associations existantes pour cette série
            $this->db->query("DELETE FROM series_matieres WHERE serie_id = :serie_id");
            $this->db->bind(':serie_id', $serie_id);
            $this->db->execute();

            // 2. Insérer les nouvelles associations
            if (!empty($matieres_details)) {
                // Préparer la requête une seule fois
                $this->db->query("INSERT INTO series_matieres (serie_id, matiere_id, coefficient, obligatoire)
                                  VALUES (:serie_id, :matiere_id, :coefficient, :obligatoire)");

                foreach ($matieres_details as $detail) {
                    if (empty($detail['matiere_id']) || !isset($detail['coefficient'])) continue; // Ignorer si données incomplètes

                    $this->db->bind(':serie_id', $serie_id);
                    $this->db->bind(':matiere_id', (int)$detail['matiere_id']);
                    $this->db->bind(':coefficient', (float)$detail['coefficient']);
                    $this->db->bind(':obligatoire', isset($detail['obligatoire']) && $detail['obligatoire'] ? 1 : 0, PDO::PARAM_INT);

                    if (!$this->db->execute()) {
                        // $this->db->rollBack(); // Annuler la transaction
                        error_log("Erreur lors de l'insertion de series_matieres pour serie_id: $serie_id, matiere_id: {$detail['matiere_id']}");
                        return false; // Échec de l'une des insertions
                    }
                }
            }
            // $this->db->commit(); // Valider la transaction
            return true;
        } catch (Exception $e) {
            // $this->db->rollBack(); // Annuler en cas d'exception
            error_log("Exception dans updateMatieresAssociees pour serie_id $serie_id: " . $e->getMessage());
            return false;
        }
    }
}
?>
