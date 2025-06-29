-- SQL Script for Baccalaureat Management Application
-- Designed for: Local network, 100% offline
-- Main language: PHP (MVC architecture)
-- Database: MySQL

SET FOREIGN_KEY_CHECKS=0;

-- Table: series
CREATE TABLE IF NOT EXISTS series (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(10) UNIQUE NOT NULL,
  libelle VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: matieres
CREATE TABLE IF NOT EXISTS matieres (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(10) UNIQUE NOT NULL,
  nom VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: centres
CREATE TABLE IF NOT EXISTS centres (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom_centre VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: lycees
CREATE TABLE IF NOT EXISTS lycees (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom_lycee VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: roles
CREATE TABLE IF NOT EXISTS roles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom_role VARCHAR(100) UNIQUE NOT NULL COMMENT 'Examples: Administrateur, Agent d’enrôlement, Chef de centre, Correcteur, Directeur lycée'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: accreditations
CREATE TABLE IF NOT EXISTS accreditations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  libelle_action VARCHAR(100) UNIQUE NOT NULL COMMENT 'Examples: create_student, edit_grades, view_reports'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: eleves
CREATE TABLE IF NOT EXISTS eleves (
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
CREATE TABLE IF NOT EXISTS series_matieres (
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
CREATE TABLE IF NOT EXISTS notes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  eleve_id INT NOT NULL,
  matiere_id INT NOT NULL,
  note FLOAT NOT NULL CHECK (note BETWEEN 0 AND 20),
  date_saisie DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  correcteur_id INT NULL COMMENT 'User ID of the corrector if tracking is needed',
  FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  -- FOREIGN KEY (correcteur_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE, -- Add this after users table is created
  UNIQUE KEY uk_eleve_matiere (eleve_id, matiere_id) COMMENT 'Ensures one note per student per subject'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
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
  email VARCHAR(100) DEFAULT NULL UNIQUE,
  is_active BOOLEAN DEFAULT TRUE,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  derniere_connexion DATETIME NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for notes.correcteur_id now that users table is defined
-- ALTER TABLE notes ADD CONSTRAINT fk_notes_correcteur FOREIGN KEY (correcteur_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Table: roles_accreditations
CREATE TABLE IF NOT EXISTS roles_accreditations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  role_id INT NOT NULL,
  accreditation_id INT NOT NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (accreditation_id) REFERENCES accreditations(id) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uk_role_accreditation (role_id, accreditation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: annees_scolaires (NOUVELLE TABLE)
CREATE TABLE IF NOT EXISTS annees_scolaires (
  id INT PRIMARY KEY AUTO_INCREMENT,
  libelle VARCHAR(20) NOT NULL UNIQUE COMMENT 'e.g., 2023-2024',
  date_debut DATE NULL,
  date_fin DATE NULL,
  est_active BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: configurations_pedagogiques (NOUVELLE TABLE)
CREATE TABLE IF NOT EXISTS configurations_pedagogiques (
  id INT PRIMARY KEY AUTO_INCREMENT,
  annee_scolaire_id INT NOT NULL,
  seuil_admission FLOAT DEFAULT 10.00 NOT NULL,
  seuil_second_tour FLOAT DEFAULT 9.50 NOT NULL,
  mention_passable FLOAT NOT NULL DEFAULT 10.00,
  mention_AB FLOAT NOT NULL DEFAULT 12.00 COMMENT 'Assez Bien',
  mention_bien FLOAT NOT NULL DEFAULT 14.00 COMMENT 'Bien',
  mention_TB FLOAT NOT NULL DEFAULT 16.00 COMMENT 'Très Bien',
  mention_exc FLOAT NOT NULL DEFAULT 18.00 COMMENT 'Excellent / Félicitations du jury',
  FOREIGN KEY (annee_scolaire_id) REFERENCES annees_scolaires(id) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uk_config_pedagogique_annee (annee_scolaire_id) COMMENT 'Une seule config péda par année scolaire'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: configurations_linguistiques (NOUVELLE TABLE - Globale)
CREATE TABLE IF NOT EXISTS configurations_linguistiques (
  id INT PRIMARY KEY AUTO_INCREMENT, -- Il n'y aura qu'une seule ligne
  langues_actives_json TEXT NOT NULL COMMENT 'JSON array of active language codes, e.g., ["fr", "ar"]',
  langue_principale VARCHAR(10) NOT NULL DEFAULT 'fr',
  langue_secondaire VARCHAR(10) DEFAULT NULL,
  mode_affichage_documents ENUM('unilingue', 'bilingue') NOT NULL DEFAULT 'bilingue' COMMENT 'Comment les documents doivent être générés'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: parametres_generaux (REVISÉE)
CREATE TABLE IF NOT EXISTS parametres_generaux (
  id INT PRIMARY KEY AUTO_INCREMENT, -- Il n'y aura qu'une seule ligne
  republique_de VARCHAR(255) NULL,
  devise_republique VARCHAR(255) NULL,
  ministere_nom VARCHAR(255) NULL,
  office_examen_nom VARCHAR(255) NULL,
  direction_nom VARCHAR(255) NULL,
  logo_pays_path VARCHAR(255) NULL,
  armoirie_pays_path VARCHAR(255) NULL,
  drapeau_pays_path VARCHAR(255) NULL,
  signature_directeur_path VARCHAR(255) NULL COMMENT 'Signature du DG de l''office, facultatif',
  cachet_office_path VARCHAR(255) NULL COMMENT 'Cachet de l''office, facultatif',
  ville_office VARCHAR(100) NULL COMMENT 'Ville où se situe l''office pour les documents'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: templates_documents (MODIFIÉE)
CREATE TABLE IF NOT EXISTS templates_documents (
  id INT PRIMARY KEY AUTO_INCREMENT,
  type_document ENUM('diplome','releve','carte') NOT NULL,
  element VARCHAR(50) NOT NULL COMMENT 'e.g., nom_eleve, date_naissance, moyenne_generale, qr_code, ou "document_background" pour le fond',
  position_x INT NOT NULL DEFAULT 0 COMMENT 'Position X en mm or pixels',
  position_y INT NOT NULL DEFAULT 0 COMMENT 'Position Y en mm or pixels',
  taille_police INT NULL DEFAULT 10 COMMENT 'Font size in points, NULL si non applicable (ex: fond)',
  police VARCHAR(100) NULL DEFAULT 'helvetica' COMMENT 'Font family, NULL si non applicable',
  couleur VARCHAR(7) NULL DEFAULT '#000000' COMMENT 'Hex color code, NULL si non applicable',
  langue_affichage ENUM('fr','ar','fr_ar') NULL DEFAULT 'fr_ar' COMMENT 'Language to display this element in, NULL si non applicable',
  visible BOOLEAN NOT NULL DEFAULT TRUE,

  -- Champs pour le fond du document (utilisés si element='document_background' ou via un flag dédié)
  -- Pour simplifier, on pourrait avoir une ligne spéciale par type_document où element='_BACKGROUND_'
  -- Ou une approche avec est_parametre_fond comme discuté.
  -- Adoptons l'approche avec est_parametre_fond pour plus de clarté.
  est_parametre_fond BOOLEAN NOT NULL DEFAULT FALSE,
  type_fond ENUM('couleur', 'theme_app', 'image_upload') NULL,
  valeur_fond VARCHAR(255) NULL COMMENT 'Code HEX, nom du thème, ou chemin de l''image de fond',
  opacite_fond FLOAT NULL DEFAULT 1.0 CHECK (opacite_fond BETWEEN 0 AND 1),

  -- S'assurer qu'un élément textuel est unique pour un type de document et une langue (sauf si c'est un param de fond)
  UNIQUE KEY uk_template_element_langue (type_document, element, langue_affichage, est_parametre_fond),
  -- S'assurer qu'il n'y a qu'un seul paramètre de fond par type de document
  UNIQUE KEY uk_template_fond (type_document, est_parametre_fond)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Inserting default global configurations (une seule ligne pour chacune)
INSERT INTO configurations_linguistiques (id, langues_actives_json, langue_principale, langue_secondaire, mode_affichage_documents)
VALUES (1, '["fr", "ar"]', 'fr', 'ar', 'bilingue')
ON DUPLICATE KEY UPDATE langues_actives_json='["fr", "ar"]', langue_principale='fr', langue_secondaire='ar', mode_affichage_documents='bilingue';

INSERT INTO parametres_generaux (id, republique_de, devise_republique, ministere_nom, office_examen_nom, direction_nom)
VALUES (1, 'République du Tchad', 'Unité - Travail - Progrès', 'Ministère de l''Éducation Nationale et de la Promotion Civique', 'Office National des Examens et Concours du Supérieur (ONECS)', 'Direction Générale')
ON DUPLICATE KEY UPDATE republique_de='République du Tchad', devise_republique='Unité - Travail - Progrès';


SET FOREIGN_KEY_CHECKS=1;

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
