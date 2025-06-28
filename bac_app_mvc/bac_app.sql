-- SQL Script for Baccalaureat Management Application
-- Designed for: Local network, 100% offline
-- Main language: PHP (MVC architecture)
-- Database: MySQL

-- Disable foreign key checks temporarily to avoid issues with table creation order
SET FOREIGN_KEY_CHECKS=0;

-- Table: series
CREATE TABLE series (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(10) UNIQUE NOT NULL,
  libelle VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: matieres
CREATE TABLE matieres (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(10) UNIQUE NOT NULL,
  nom VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: centres
CREATE TABLE centres (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom_centre VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: lycees
CREATE TABLE lycees (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom_lycee VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: roles
CREATE TABLE roles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom_role VARCHAR(100) UNIQUE NOT NULL COMMENT 'Examples: Administrateur, Agent d’enrôlement, Chef de centre, Correcteur, Directeur lycée'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: accreditations
CREATE TABLE accreditations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  libelle_action VARCHAR(100) UNIQUE NOT NULL COMMENT 'Examples: create_student, edit_grades, view_reports'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: eleves
CREATE TABLE eleves (
  id INT PRIMARY KEY AUTO_INCREMENT,
  matricule VARCHAR(20) UNIQUE NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  date_naissance DATE NOT NULL,
  sexe ENUM('M','F') NOT NULL,
  serie_id INT NOT NULL,
  centre_id INT NOT NULL,
  lycee_id INT NOT NULL,
  photo VARCHAR(255) NULL COMMENT 'Path to the photo file',
  empreinte1 TEXT NULL COMMENT 'Base64 encoded fingerprint data or path',
  empreinte2 TEXT NULL,
  empreinte3 TEXT NULL,
  empreinte4 TEXT NULL,
  empreinte5 TEXT NULL,
  empreinte6 TEXT NULL,
  empreinte7 TEXT NULL,
  empreinte8 TEXT NULL,
  empreinte9 TEXT NULL,
  empreinte10 TEXT NULL,
  date_enrolement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (serie_id) REFERENCES series(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (centre_id) REFERENCES centres(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (lycee_id) REFERENCES lycees(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: series_matieres
CREATE TABLE series_matieres (
  id INT PRIMARY KEY AUTO_INCREMENT,
  serie_id INT NOT NULL,
  matiere_id INT NOT NULL,
  coefficient FLOAT NOT NULL,
  obligatoire BOOLEAN NOT NULL DEFAULT TRUE,
  FOREIGN KEY (serie_id) REFERENCES series(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uk_serie_matiere (serie_id, matiere_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: notes
CREATE TABLE notes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  eleve_id INT NOT NULL,
  matiere_id INT NOT NULL,
  note FLOAT NOT NULL CHECK (note BETWEEN 0 AND 20),
  date_saisie DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  correcteur_id INT NULL COMMENT 'User ID of the corrector if tracking is needed',
  FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  -- FOREIGN KEY (correcteur_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE, -- Uncomment if users table is created first and linking is desired
  UNIQUE KEY uk_eleve_matiere (eleve_id, matiere_id) COMMENT 'Ensures one note per student per subject'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(100) UNIQUE NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL COMMENT 'Hashed password',
  role_id INT NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  date_naissance DATE NOT NULL,
  lieu_naissance VARCHAR(100) NOT NULL,
  sexe ENUM('M','F') NOT NULL,
  photo VARCHAR(255) DEFAULT NULL COMMENT 'Path to the user photo file',
  matricule VARCHAR(50) DEFAULT NULL COMMENT 'Employee ID or similar',
  telephone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(100) DEFAULT NULL UNIQUE, -- Added email for potential future use (e.g. password recovery, though offline focus)
  is_active BOOLEAN DEFAULT TRUE,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  derniere_connexion DATETIME NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for notes.correcteur_id now that users table is defined
-- ALTER TABLE notes ADD CONSTRAINT fk_notes_correcteur FOREIGN KEY (correcteur_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Table: roles_accreditations
CREATE TABLE roles_accreditations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  role_id INT NOT NULL,
  accreditation_id INT NOT NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (accreditation_id) REFERENCES accreditations(id) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uk_role_accreditation (role_id, accreditation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: parametres_generaux
CREATE TABLE parametres_generaux (
  id INT PRIMARY KEY AUTO_INCREMENT,
  annee_scolaire VARCHAR(20) NOT NULL UNIQUE COMMENT 'e.g., 2023-2024',
  seuil_admission FLOAT DEFAULT 10.00 NOT NULL,
  seuil_second_tour FLOAT DEFAULT 9.50 NOT NULL,
  mention_passable FLOAT NOT NULL DEFAULT 10.00,
  mention_AB FLOAT NOT NULL DEFAULT 12.00 COMMENT 'Assez Bien',
  mention_bien FLOAT NOT NULL DEFAULT 14.00 COMMENT 'Bien',
  mention_TB FLOAT NOT NULL DEFAULT 16.00 COMMENT 'Très Bien',
  mention_exc FLOAT NOT NULL DEFAULT 18.00 COMMENT 'Excellent / Félicitations du jury',
  langues_active TEXT NOT NULL COMMENT 'JSON array of active language codes, e.g., ["fr", "ar"]',
  langue_principale VARCHAR(10) NOT NULL DEFAULT 'fr',
  langue_secondaire VARCHAR(10) DEFAULT NULL,
  signature_officielle VARCHAR(255) NULL COMMENT 'Path to image file for official signature',
  cachet_logo VARCHAR(255) NULL COMMENT 'Path to image file for official stamp/logo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: templates_documents
CREATE TABLE templates_documents (
  id INT PRIMARY KEY AUTO_INCREMENT,
  type_document ENUM('diplome','releve','carte') NOT NULL,
  element VARCHAR(50) NOT NULL COMMENT 'e.g., nom_eleve, date_naissance, moyenne_generale, qr_code',
  position_x INT NOT NULL DEFAULT 0 COMMENT 'Position X in mm or pixels, depends on PDF lib',
  position_y INT NOT NULL DEFAULT 0 COMMENT 'Position Y in mm or pixels',
  taille_police INT NOT NULL DEFAULT 10 COMMENT 'Font size in points',
  police VARCHAR(100) DEFAULT 'helvetica' COMMENT 'Font family',
  couleur VARCHAR(7) DEFAULT '#000000' COMMENT 'Hex color code',
  langue_affichage ENUM('fr','ar','fr_ar') NOT NULL DEFAULT 'fr_ar' COMMENT 'Language to display this element in',
  visible BOOLEAN NOT NULL DEFAULT TRUE,
  UNIQUE KEY uk_template_element (type_document, element, langue_affichage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Example data (optional, can be added later)
-- INSERT INTO roles (nom_role) VALUES ('Administrateur'), ('Agent d’enrôlement'), ('Chef de centre'), ('Correcteur'), ('Directeur lycée');
-- INSERT INTO series (code, libelle) VALUES ('L2', 'Langues et Lettres'), ('S2', 'Sciences Expérimentales');
-- INSERT INTO matieres (code, nom) VALUES ('MATH', 'Mathématiques'), ('FRAN', 'Français'), ('HIST', 'Histoire');
-- INSERT INTO parametres_generaux (annee_scolaire, langues_active, langue_principale) VALUES ('2023-2024', '["fr", "ar"]', 'fr');

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

-- Add a note about character sets and collations
-- All tables are created with utf8mb4 character set and utf8mb4_unicode_ci collation
-- to support a wide range of characters, including Arabic.
-- The ENGINE is InnoDB, which supports transactions and foreign keys.

-- End of SQL Script
-- To import this file into MySQL:
-- mysql -u username -p database_name < bac_app.sql
-- Or use a GUI tool like phpMyAdmin.
-- Make sure the database 'bac_app' (or as defined in config.php) exists before importing.
-- CREATE DATABASE IF NOT EXISTS bac_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- User creation and privileges (example, adjust as needed for security)
-- CREATE USER 'bac_user'@'localhost' IDENTIFIED BY 'your_strong_password';
-- GRANT ALL PRIVILEGES ON bac_app.* TO 'bac_user'@'localhost';
-- FLUSH PRIVILEGES;
