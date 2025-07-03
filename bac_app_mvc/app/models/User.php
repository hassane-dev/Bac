<?php

class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère tous les utilisateurs avec le nom de leur rôle.
     * @return array
     */
    public function getAllWithRoles() {
        $this->db->query("SELECT u.*, r.nom_role
                          FROM users u
                          LEFT JOIN roles r ON u.role_id = r.id
                          ORDER BY u.nom ASC, u.prenom ASC");
        return $this->db->resultSet();
    }

    /**
     * Récupère un utilisateur par son ID avec le nom de son rôle.
     * @param int $id
     * @return object|false
     */
    public function getByIdWithRole($id) {
        $this->db->query("SELECT u.*, r.nom_role
                          FROM users u
                          LEFT JOIN roles r ON u.role_id = r.id
                          WHERE u.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Récupère un utilisateur par son nom d'utilisateur.
     * @param string $username
     * @return object|false
     */
    public function getByUsername($username) {
        $this->db->query("SELECT * FROM users WHERE username = :username");
        $this->db->bind(':username', $username);
        return $this->db->single();
    }


    /**
     * Ajoute un nouvel utilisateur.
     * @param array $data Données de l'utilisateur, incluant mot_de_passe (non haché)
     * @return bool
     */
    public function add($data) {
        $this->db->query("INSERT INTO users (username, mot_de_passe, role_id, nom, prenom, date_naissance, lieu_naissance, sexe, photo, matricule, telephone, email, is_active)
                          VALUES (:username, :mot_de_passe, :role_id, :nom, :prenom, :date_naissance, :lieu_naissance, :sexe, :photo, :matricule, :telephone, :email, :is_active)");

        $this->db->bind(':username', $data['username']);
        $this->db->bind(':mot_de_passe', password_hash($data['mot_de_passe'], PASSWORD_DEFAULT));
        $this->db->bind(':role_id', $data['role_id']);
        $this->db->bind(':nom', $data['nom']);
        $this->db->bind(':prenom', $data['prenom']);
        $this->db->bind(':date_naissance', $data['date_naissance']);
        $this->db->bind(':lieu_naissance', $data['lieu_naissance']);
        $this->db->bind(':sexe', $data['sexe']);
        $this->db->bind(':photo', $data['photo'] ?? null);
        $this->db->bind(':matricule', $data['matricule'] ?? null);
        $this->db->bind(':telephone', $data['telephone'] ?? null);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':is_active', isset($data['is_active']) ? (int)$data['is_active'] : 1, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Modifie un utilisateur existant.
     * @param int $id
     * @param array $data Données de l'utilisateur. Si mot_de_passe est fourni et non vide, il sera haché.
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE users SET
                    username = :username,
                    role_id = :role_id,
                    nom = :nom,
                    prenom = :prenom,
                    date_naissance = :date_naissance,
                    lieu_naissance = :lieu_naissance,
                    sexe = :sexe,
                    photo = :photo,
                    matricule = :matricule,
                    telephone = :telephone,
                    email = :email,
                    is_active = :is_active ";

        if (!empty($data['mot_de_passe'])) {
            $sql .= ", mot_de_passe = :mot_de_passe ";
        }

        $sql .= "WHERE id = :id";

        $this->db->query($sql);

        $this->db->bind(':id', $id);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':role_id', $data['role_id']);
        $this->db->bind(':nom', $data['nom']);
        $this->db->bind(':prenom', $data['prenom']);
        $this->db->bind(':date_naissance', $data['date_naissance']);
        $this->db->bind(':lieu_naissance', $data['lieu_naissance']);
        $this->db->bind(':sexe', $data['sexe']);
        $this->db->bind(':photo', $data['photo'] ?? null); // Gérer la photo si elle est mise à jour
        $this->db->bind(':matricule', $data['matricule'] ?? null);
        $this->db->bind(':telephone', $data['telephone'] ?? null);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':is_active', isset($data['is_active']) ? (int)$data['is_active'] : 1, PDO::PARAM_INT);

        if (!empty($data['mot_de_passe'])) {
            $this->db->bind(':mot_de_passe', password_hash($data['mot_de_passe'], PASSWORD_DEFAULT));
        }

        return $this->db->execute();
    }

    /**
     * Supprime un utilisateur.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        if ($id == 1) { // Empêcher la suppression de l'admin principal
            $_SESSION['error_message'] = 'L\'administrateur principal ne peut pas être supprimé.';
            return false;
        }
        $this->db->query("DELETE FROM users WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Vérifie si un nom d'utilisateur existe déjà.
     * @param string $username
     * @param int|null $currentId ID de l'utilisateur actuel à exclure (pour la mise à jour)
     * @return bool
     */
    public function usernameExists($username, $currentId = null) {
        $sql = "SELECT id FROM users WHERE username = :username";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':username', $username);
        if ($currentId !== null) {
            $this->db->bind(':current_id', (int)$currentId);
        }
        return $this->db->single() ? true : false;
    }

    /**
     * Vérifie si un email existe déjà.
     * @param string $email
     * @param int|null $currentId ID de l'utilisateur actuel à exclure (pour la mise à jour)
     * @return bool
     */
    public function emailExists($email, $currentId = null) {
        if (empty($email)) return false;
        $sql = "SELECT id FROM users WHERE email = :email";
        if ($currentId !== null) {
            $sql .= " AND id != :current_id";
        }
        $this->db->query($sql);
        $this->db->bind(':email', $email);
        if ($currentId !== null) {
            $this->db->bind(':current_id', (int)$currentId);
        }
        return $this->db->single() ? true : false;
    }

    /**
     * Met à jour la date de dernière connexion.
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin($userId) {
        $this->db->query("UPDATE users SET derniere_connexion = NOW() WHERE id = :id");
        $this->db->bind(':id', (int)$userId);
        return $this->db->execute();
    }
}
?>
