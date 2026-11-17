-- Base de données : cabinet_medical
CREATE DATABASE IF NOT EXISTS cabinet_medical CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE cabinet_medical;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS rendez_vous;
DROP TABLE IF EXISTS disponibilites;
DROP TABLE IF EXISTS medecins;
DROP TABLE IF EXISTS specialites;
DROP TABLE IF EXISTS utilisateurs;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telephone VARCHAR(30) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin','medecin','patient') NOT NULL
);

CREATE TABLE specialites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE medecins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_specialite INT NOT NULL,
    description TEXT,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    duree_rdv INT NOT NULL DEFAULT 30,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_specialite) REFERENCES specialites(id)
);

CREATE TABLE disponibilites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_medecin INT NOT NULL,
    jour_semaine TINYINT NOT NULL, -- 1=Lundi ... 7=Dimanche
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    FOREIGN KEY (id_medecin) REFERENCES medecins(id) ON DELETE CASCADE
);

CREATE TABLE rendez_vous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_patient INT NOT NULL,
    id_medecin INT NOT NULL,
    date_rdv DATE NOT NULL,
    heure_rdv TIME NOT NULL,
    motif TEXT NOT NULL,
    statut ENUM('En attente','Confirmée','Refusée','Annulée','Terminée','Absent') NOT NULL DEFAULT 'En attente',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_patient) REFERENCES utilisateurs(id),
    FOREIGN KEY (id_medecin) REFERENCES medecins(id)
);

-- Mot de passe pour tous les comptes de démonstration : password
-- Hash généré avec password_hash('password', PASSWORD_DEFAULT)
INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role) VALUES
('Admin', 'Cabinet', 'admin@cabinet.test', '0100000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Martin', 'Julie', 'medecin1@cabinet.test', '0100000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'medecin'),
('Bernard', 'Ali', 'medecin2@cabinet.test', '0100000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'medecin'),
('Durand', 'Sara', 'patient1@cabinet.test', '0600000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient'),
('Petit', 'Nora', 'patient2@cabinet.test', '0600000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient');

INSERT INTO specialites (libelle, description) VALUES
('Cardiologie', 'Soins du coeur et des vaisseaux'),
('Dermatologie', 'Soins de la peau'),
('Pédiatrie', 'Soins pour les enfants');

INSERT INTO medecins (id_utilisateur, id_specialite, description, actif, duree_rdv) VALUES
(2, 1, 'Cardiologue, 10 ans d''expérience.', 1, 30),
(3, 3, 'Pédiatre, suivi des nourrissons et enfants.', 1, 30);

INSERT INTO disponibilites (id_medecin, jour_semaine, heure_debut, heure_fin) VALUES
(1, 1, '09:00:00', '12:00:00'),
(1, 3, '14:00:00', '17:00:00'),
(2, 2, '10:00:00', '13:00:00'),
(2, 4, '09:00:00', '12:00:00');

INSERT INTO rendez_vous (id_patient, id_medecin, date_rdv, heure_rdv, motif, statut, date_creation) VALUES
(4, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', 'Douleurs thoraciques légères', 'En attente', NOW()),
(5, 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 'Contrôle annuel', 'Confirmée', NOW()),
(4, 1, CURDATE(), '09:30:00', 'Suivi de consultation', 'Confirmée', NOW());
