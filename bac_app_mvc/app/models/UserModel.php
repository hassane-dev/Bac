<?php

class UserModel {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Crée un nouvel utilisateur.
     * @param array $data Données de l'utilisateur.
     * @return bool True si succès, false sinon.
     */
    public function create($data) {
        if ($this->findByUsername($data['username'])) {
            return false;
        }
        if (!empty($data['email']) && $this->findByEmail($data['email'])) {
            return false;
        }

        $this->db->query('INSERT INTO users (username, mot_de_passe, role_id, nom, prenom, date_naissance, lieu_naissance, sexe, email, is_active, matricule, telephone, photo)
                          VALUES (:username, :mot_de_passe, :role_id, :nom, :prenom, :date_naissance, :lieu_naissance, :sexe, :email, :is_active, :matricule, :telephone, :photo)');

        $this->db->bind(':username', $data['username']);
        $this->db->bind(':mot_de_passe', password_hash($data['mot_de_passe'], PASSWORD_DEFAULT));
        $this->db->bind(':role_id', $data['role_id']);
        $this->db->bind(':nom', $data['nom']);
        $this->db->bind(':prenom', $data['prenom']);
        $this->db->bind(':date_naissance', $data['date_naissance']);
        $this->db->bind(':lieu_naissance', $data['lieu_naissance']);
        $this->db->bind(':sexe', $data['sexe']);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':is_active', $data['is_active'] ?? true, PDO::PARAM_BOOL);
        $this->db->bind(':matricule', $data['matricule'] ?? null);
        $this->db->bind(':telephone', $data['telephone'] ?? null);
        $this->db->bind(':photo', $data['photo'] ?? null);

        return $this->db->execute();
    }

    /**
     * Récupère tous les utilisateurs avec le nom de leur rôle.
     * @return array La liste des utilisateurs.
     */
    public function getAll() {
        $this->db->query('SELECT u.*, r.nom_role FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.nom ASC, u.prenom ASC');
        return $this->db->resultSet();
    }

    /**
     * Récupère un utilisateur par son ID, avec le nom de son rôle.
     * @param int $id L'ID de l'utilisateur.
     * @return object|false L'objet utilisateur ou false si non trouvé.
     */
    public function getById($id) {
        $this->db->query('SELECT u.*, r.nom_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Met à jour un utilisateur existant.
     * @param int $id L'ID de l'utilisateur.
     * @param array $data Les données à mettre à jour.
     * @return bool True si succès, false sinon.
     */
    public function update($id, $data) {
        $user_to_update = $this->getById($id);
        if (!$user_to_update) return false;

        if (isset($data['username']) && strtolower($data['username']) !== strtolower($user_to_update->username)) {
            if ($this->findByUsername($data['username'])) {
                return false;
            }
        }
        if (isset($data['email']) && !empty($data['email']) && strtolower($data['email']) !== strtolower($user_to_update->email ?? '')) {
            if ($this->findByEmail($data['email'])) {
                return false;
            }
        }

        $sql = 'UPDATE users SET
                    username = :username,
                    role_id = :role_id,
                    nom = :nom,
                    prenom = :prenom,
                    date_naissance = :date_naissance,
                    lieu_naissance = :lieu_naissance,
                    sexe = :sexe,
                    email = :email,
                    is_active = :is_active,
                    matricule = :matricule,
                    telephone = :telephone,
                    photo = :photo';

        if (!empty($data['mot_de_passe'])) {
            $sql .= ', mot_de_passe = :mot_de_passe';
        }
        $sql .= ' WHERE id = :id';

        $this->db->query($sql);

        $this->db->bind(':id', $id);
        $this->db->bind(':username', $data['username'] ?? $user_to_update->username);
        $this->db->bind(':role_id', $data['role_id'] ?? $user_to_update->role_id);
        $this->db->bind(':nom', $data['nom'] ?? $user_to_update->nom);
        $this->db->bind(':prenom', $data['prenom'] ?? $user_to_update->prenom);
        $this->db->bind(':date_naissance', $data['date_naissance'] ?? $user_to_update->date_naissance);
        $this->db->bind(':lieu_naissance', $data['lieu_naissance'] ?? $user_to_update->lieu_naissance);
        $this->db->bind(':sexe', $data['sexe'] ?? $user_to_update->sexe);
        $this->db->bind(':email', $data['email'] ?? $user_to_update->email); // Permet de vider l'email si $data['email'] est une chaine vide
        $this->db->bind(':is_active', $data['is_active'] ?? $user_to_update->is_active, PDO::PARAM_BOOL);
        $this->db->bind(':matricule', $data['matricule'] ?? $user_to_update->matricule);
        $this->db->bind(':telephone', $data['telephone'] ?? $user_to_update->telephone);
        $this->db->bind(':photo', $data['photo'] ?? $user_to_update->photo);


        if (!empty($data['mot_de_passe'])) {
            $this->db->bind(':mot_de_passe', password_hash($data['mot_de_passe'], PASSWORD_DEFAULT));
        }

        return $this->db->execute();
    }

    /**
     * Supprime un utilisateur.
     * @param int $id L'ID de l'utilisateur à supprimer.
     * @return bool True si succès, false sinon.
     */
    public function delete($id) {
        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Trouve un utilisateur par son nom d'utilisateur.
     * @param string $username Le nom d'utilisateur.
     * @return object|false L'objet utilisateur ou false si non trouvé.
     */
    public function findByUsername($username) {
        $this->db->query('SELECT u.*, r.nom_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = :username');
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    /**
     * Trouve un utilisateur par son email.
     * @param string $email L'email.
     * @return object|false L'objet utilisateur ou false si non trouvé.
     */
    public function findByEmail($email) {
        if (empty($email)) return false; // Ne pas chercher si l'email est vide
        $this->db->query('SELECT u.*, r.nom_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = :email');
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    /**
     * Met à jour la date de dernière connexion.
     * @param int $id User ID.
     * @return bool
     */
    public function updateLastLogin($id) {
        $this->db->query('UPDATE users SET derniere_connexion = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>
