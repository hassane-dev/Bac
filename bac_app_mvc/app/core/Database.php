<?php

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private_user = DB_USER;
    private $pass = DB_PASS;

    private $dbh; // Database Handler
    private $stmt; // Statement
    private $error;

    public function __construct() {
        // Définir le DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true, // Connexion persistante pour améliorer les performances
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lancer des exceptions en cas d'erreur
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Récupérer les résultats sous forme de tableau associatif
            PDO::ATTR_EMULATE_PREPARES => false, // Utiliser de vraies requêtes préparées
        ];

        // Créer une instance de PDO
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // En mode développement, afficher l'erreur. En production, logguer l'erreur.
            if (SHOW_ERRORS) {
                die("Erreur de connexion à la base de données : " . $this->error);
            } else {
                // Log l'erreur dans un fichier ou un système de logging
                error_log("Erreur de connexion DB: " . $this->error);
                die("Une erreur est survenue. Veuillez réessayer plus tard.");
            }
        }
    }

    /**
     * Prépare une requête SQL.
     * @param string $sql La requête SQL à préparer.
     */
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
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
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Exécute la requête préparée.
     * @return bool True en cas de succès, false sinon.
     */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (SHOW_ERRORS) {
                echo "Erreur d'exécution de la requête: " . $this->error . "<br>SQL: " . $this->stmt->queryString;
            } else {
                error_log("Erreur SQL: " . $this->error . " | Query: " . $this->stmt->queryString);
            }
            return false;
        }
    }

    /**
     * Récupère tous les résultats de la requête sous forme de tableau d'objets (ou tableaux associatifs).
     * @return array
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(); // Utilise le FETCH_MODE défini dans le constructeur (PDO::FETCH_ASSOC)
    }

    /**
     * Récupère un seul résultat de la requête.
     * @return mixed
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch(); // Utilise le FETCH_MODE défini dans le constructeur
    }

    /**
     * Récupère le nombre de lignes affectées par la dernière requête DELETE, INSERT ou UPDATE.
     * @return int
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * Récupère l'ID du dernier enregistrement inséré.
     * @return string
     */
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    /**
     * Commence une transaction.
     */
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    /**
     * Valide une transaction.
     */
    public function commit() {
        return $this->dbh->commit();
    }

    /**
     * Annule une transaction.
     */
    public function rollBack() {
        return $this->dbh->rollBack();
    }

    /**
     * Affiche les informations de la requête préparée (utile pour le débogage).
     */
    public function debugDumpParams() {
        return $this->stmt->debugDumpParams();
    }

    /**
     * Récupère la dernière erreur PDO.
     * @return string
     */
    public function getError() {
        return $this->error;
    }
}

?>
