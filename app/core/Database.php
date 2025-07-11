<?php

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASS;

    private $dbh; // Database Handler
    private $stmt; // Statement
    private $error;
    private $inTransaction = false;

    public function __construct() {
        // Vérifier si les constantes DB sont définies
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            // Tenter de charger config.php si ce n'est pas déjà fait.
            $configFile = dirname(__DIR__, 2) . '/config.php';
            if (file_exists($configFile) && !defined('APP_ROOT')) { // APP_ROOT est aussi dans config.php
                require_once $configFile;
            } else if (!defined('APP_ROOT') && defined('DB_HOST')) {
                // config.php a été chargé mais peut-être pas complètement initialisé ou constantes manquantes
            } else if (!defined('DB_HOST')) {
                 die("Constantes de base de données non définies. Vérifiez config.php.");
            }
             // Réassigner au cas où elles viennent d'être chargées
            $this->host = DB_HOST;
            $this->db_name = DB_NAME;
            $this->user = DB_USER;
            $this->pass = DB_PASS;
        }


        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true, // Connexions persistantes
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Mode d'erreur pour lancer des exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // Mode de fetch par défaut (objets anonymes)
            PDO::ATTR_EMULATE_PREPARES => false, // Désactiver l'émulation des requêtes préparées pour utiliser les vraies
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Gérer l'erreur de connexion. Ne pas utiliser View::renderError ici car View pourrait ne pas être chargé ou dépendre de DB.
            $show_errors = (defined('SHOW_ERRORS') && SHOW_ERRORS);
            $errorMessage = "Erreur de connexion à la base de données.";
            if ($show_errors) {
                $errorMessage .= " Détails: " . $this->error;
            }
            error_log("Erreur de connexion DB: " . $this->error); // Toujours logger
            die($errorMessage); // Arrêt brutal si la DB n'est pas accessible.
        }
    }

    public function query($sql) {
        if (!$this->dbh) {
             $this->handleError("Aucune connexion à la base de données disponible pour la requête : " . $sql);
             return;
        }
        try {
            $this->stmt = $this->dbh->prepare($sql);
        } catch (PDOException $e) {
            $this->handleError("Erreur de préparation: " . $e->getMessage() . " | SQL: " . $sql);
        }
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value): $type = PDO::PARAM_INT; break;
                case is_bool($value): $type = PDO::PARAM_BOOL; break;
                case is_null($value): $type = PDO::PARAM_NULL; break;
                default: $type = PDO::PARAM_STR;
            }
        }
        if ($this->stmt) {
            try {
                $this->stmt->bindValue($param, $value, $type);
            } catch (PDOException $e) {
                 $this->handleError("Erreur de liaison du paramètre $param: " . $e->getMessage());
            }
        } else {
            $this->handleError("Tentative de liaison sur une requête non préparée ou échouée.");
        }
    }

    public function execute() {
        if (!$this->stmt) {
            $this->handleError("Tentative d'exécution sur une requête non préparée ou échouée.");
            return false;
        }
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->handleError("Erreur d'exécution: " . $e->getMessage() . " | SQL: " . ($this->stmt->queryString ?? 'N/A'));
            return false;
        }
    }

    public function resultSet() {
        if (!$this->stmt) return [];
        $this->execute(); // S'assurer que la requête est exécutée avant de fetcher
        try {
            return $this->stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de la récupération du jeu de résultats: " . $e->getMessage());
            return [];
        }
    }

    public function single() {
        if (!$this->stmt) return false;
        $this->execute(); // S'assurer que la requête est exécutée
        try {
            return $this->stmt->fetch();
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de la récupération d'un seul résultat: " . $e->getMessage());
            return false;
        }
    }

    public function rowCount() {
        return $this->stmt ? $this->stmt->rowCount() : 0;
    }

    public function lastInsertId() {
        return $this->dbh ? $this->dbh->lastInsertId() : false;
    }

    public function beginTransaction() {
        if (!$this->dbh) return false;
        if ($this->inTransaction) return true; // Déjà en transaction
        try {
            $this->inTransaction = $this->dbh->beginTransaction();
            return $this->inTransaction;
        } catch (PDOException $e) {
            $this->handleError("Erreur lors du démarrage de la transaction: " . $e->getMessage());
            return false;
        }
    }

    public function commit() {
        if (!$this->dbh || !$this->inTransaction) return false;
        try {
            if ($this->dbh->commit()) {
                $this->inTransaction = false;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de la validation de la transaction: " . $e->getMessage());
            // Tenter un rollback en cas d'échec du commit
            if ($this->inTransaction) { // Vérifier si toujours en transaction après l'échec du commit
                $this->rollBack();
            }
            return false;
        }
    }

    public function rollBack() {
        if (!$this->dbh || !$this->inTransaction) return false;
        try {
            if ($this->dbh->rollBack()) {
                $this->inTransaction = false;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de l'annulation de la transaction: " . $e->getMessage());
            return false;
        }
    }

    public function inTransaction() {
        return $this->inTransaction;
    }

    public function getError() {
        return $this->error;
    }

    private function handleError($message) {
        $this->error = $message;
        error_log("Erreur Database: " . $this->error); // Toujours logger

        $show_errors = (defined('SHOW_ERRORS') && SHOW_ERRORS);
        if ($show_errors) {
            // Ne pas utiliser View::renderError ici pour éviter les dépendances circulaires
            // ou si View n'est pas encore disponible.
            $htmlError = "<div style='border:1px solid #F00; padding:15px; margin:10px; background-color:#FFEEEE;'>";
            $htmlError .= "<strong>Erreur de Base de Données:</strong><br><pre>" . htmlspecialchars($this->error) . "</pre>";
            $htmlError .= "</div>";
            if (!headers_sent()) { // Ne pas essayer d'afficher si les headers sont déjà envoyés
                 echo $htmlError;
            }
            // Dans un contexte de production, on ne voudrait pas die() ici, mais plutôt lancer une exception
            // qui serait attrapée par un gestionnaire d'erreur global.
            // Pour ce projet simple, un die() peut être acceptable en dev si SHOW_ERRORS est true.
            // die();
        }
        // En production, on ne voudrait pas afficher l'erreur détaillée à l'utilisateur.
        // On pourrait lancer une exception personnalisée ici.
    }
}
?>
