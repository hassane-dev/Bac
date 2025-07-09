<?php

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASS;

    private $dbh;
    private $stmt;
    private $error;
    private $inTransaction = false;

    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (defined('SHOW_ERRORS') && SHOW_ERRORS) {
                View::renderError("Erreur de connexion DB : " . $this->error, 500);
            } else {
                error_log("Erreur de connexion DB: " . $this->error);
                View::renderError("Erreur de base de données.", 500);
            }
        }
    }

    public function query($sql) {
        if (!$this->dbh) { // Si la connexion a échoué dans le constructeur
             View::renderError("Aucune connexion à la base de données disponible.", 500);
             return; // ou throw exception
        }
        try {
            $this->stmt = $this->dbh->prepare($sql);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (defined('SHOW_ERRORS') && SHOW_ERRORS) {
                 View::renderError("Erreur de préparation: " . $this->error . " | SQL: " . $sql, 500);
            } else {
                error_log("Erreur SQL (prepare): " . $this->error . " | Query: " . $sql);
                View::renderError("Erreur de base de données.", 500);
            }
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
            $this->stmt->bindValue($param, $value, $type);
        }
    }

    public function execute() {
        if (!$this->stmt) return false;
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (defined('SHOW_ERRORS') && SHOW_ERRORS) {
                View::renderError("Erreur d'exécution: " . $this->error . " | SQL: " . $this->stmt->queryString, 500);
            } else {
                error_log("Erreur SQL (execute): " . $this->error . " | Query: " . $this->stmt->queryString);
                 View::renderError("Erreur de base de données lors de l'exécution.", 500);
            }
            return false;
        }
    }

    public function resultSet() {
        if (!$this->stmt || !$this->execute()) return [];
        return $this->stmt->fetchAll();
    }

    public function single() {
        if (!$this->stmt || !$this->execute()) return false;
        return $this->stmt->fetch();
    }

    public function rowCount() {
        return $this->stmt ? $this->stmt->rowCount() : 0;
    }

    public function lastInsertId() {
        return $this->dbh ? $this->dbh->lastInsertId() : false;
    }

    public function beginTransaction() {
        if (!$this->dbh) return false;
        $this->inTransaction = $this->dbh->beginTransaction();
        return $this->inTransaction;
    }

    public function commit() {
        if (!$this->dbh || !$this->inTransaction) return false;
        $this->inTransaction = false;
        return $this->dbh->commit();
    }

    public function rollBack() {
        if (!$this->dbh || !$this->inTransaction) return false;
        $this->inTransaction = false;
        return $this->dbh->rollBack();
    }

    public function inTransaction() {
        return $this->inTransaction;
    }

    public function getError() {
        return $this->error;
    }
}
?>
