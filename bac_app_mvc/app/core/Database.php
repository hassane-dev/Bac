<?php

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $user = DB_USER; // Corrigé de private_user
    private $pass = DB_PASS;

    private $dbh; // Database Handler
    private $stmt; // Statement
    private $error;

    public function __construct() {
        // Définir le DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // Changé en FETCH_OBJ pour retourner des objets
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (SHOW_ERRORS) {
                View::renderError("Erreur de connexion à la base de données : " . $this->error, 500);
            } else {
                error_log("Erreur de connexion DB: " . $this->error);
                View::renderError("Une erreur de base de données est survenue. Veuillez réessayer plus tard.", 500);
            }
        }
    }

    /**
     * Prépare une requête SQL.
     * @param string $sql La requête SQL à préparer.
     */
    public function query($sql) {
        try {
            $this->stmt = $this->dbh->prepare($sql);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (SHOW_ERRORS) {
                 View::renderError("Erreur de préparation de la requête: " . $this->error . "<br>SQL: " . $sql, 500);
            } else {
                error_log("Erreur SQL (prepare): " . $this->error . " | Query: " . $sql);
                View::renderError("Une erreur de base de données est survenue.", 500);
            }
        }
    }

    /**
     * Lie les valeurs aux paramètres de la requête préparée.
     * @param mixed $param Le nom du paramètre (ex: :id)
     * @param mixed $value La valeur à lier
     * @param mixed $type Le type de données PDO (ex: PDO::PARAM_INT)
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        if ($this->stmt) {
            $this->stmt->bindValue($param, $value, $type);
        }
    }

    /**
     * Exécute la requête préparée.
     * @return bool True en cas de succès, false sinon.
     */
    public function execute() {
        if (!$this->stmt) return false;
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (SHOW_ERRORS) {
                View::renderError("Erreur d'exécution de la requête: " . $this->error . "<br>SQL: " . $this->stmt->queryString, 500);
            } else {
                error_log("Erreur SQL (execute): " . $this->error . " | Query: " . $this->stmt->queryString);
                 View::renderError("Une erreur de base de données est survenue lors de l'exécution.", 500);
            }
            return false;
        }
    }

    /**
     * Récupère tous les résultats de la requête.
     * @return array
     */
    public function resultSet() {
        if (!$this->execute()) return [];
        return $this->stmt->fetchAll(); // Utilise le FETCH_MODE défini (PDO::FETCH_OBJ)
    }

    /**
     * Récupère un seul résultat de la requête.
     * @return mixed (object|false)
     */
    public function single() {
        if (!$this->execute()) return false;
        return $this->stmt->fetch(); // Utilise le FETCH_MODE défini
    }

    /**
     * Récupère le nombre de lignes affectées par la dernière requête DELETE, INSERT ou UPDATE.
     * @return int
     */
    public function rowCount() {
        return $this->stmt ? $this->stmt->rowCount() : 0;
    }

    /**
     * Récupère l'ID du dernier enregistrement inséré.
     * @return string|false
     */
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    /** Commence une transaction. */
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    /** Valide une transaction. */
    public function commit() {
        return $this->dbh->commit();
    }

    /** Annule une transaction. */
    public function rollBack() {
        return $this->dbh->rollBack();
    }

    /** Récupère la dernière erreur PDO. @return string */
    public function getError() {
        return $this->error;
    }
}
?>
