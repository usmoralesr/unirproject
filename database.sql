CREATE DATABASE IF NOT EXISTS calidad_agua CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE calidad_agua;

DROP TABLE IF EXISTS measurements;
DROP TABLE IF EXISTS water_bodies;

CREATE TABLE water_bodies (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 state VARCHAR(80) NOT NULL,
 type ENUM('Lago','Laguna','Presa') NOT NULL DEFAULT 'Lago',
 latitude DECIMAL(10,7),
 longitude DECIMAL(10,7)
);

CREATE TABLE measurements (
 id INT AUTO_INCREMENT PRIMARY KEY,
 water_body_id INT NOT NULL,
 measured_at DATETIME NOT NULL,
 wqi DECIMAL(5,2) NOT NULL,
 ph DECIMAL(4,2) NOT NULL,
 dissolved_oxygen DECIMAL(6,2) NOT NULL,
 turbidity DECIMAL(7,2) NOT NULL,
 temperature DECIMAL(5,2) NOT NULL,
 tds DECIMAL(8,2) NOT NULL,
 CONSTRAINT fk_measurement_body FOREIGN KEY(water_body_id)
 REFERENCES water_bodies(id) ON DELETE CASCADE,
 INDEX idx_body_date(water_body_id, measured_at)
);

INSERT INTO water_bodies(name,state,type,latitude,longitude) VALUES
('Lago de Chapala','Jalisco / Michoacán','Lago',20.2500,-103.0000),
('Lago de Pátzcuaro','Michoacán','Lago',19.5700,-101.6200),
('Lago de Zirahuén','Michoacán','Lago',19.4300,-101.7300),
('Laguna de Cuitzeo','Michoacán / Guanajuato','Laguna',19.9300,-101.1400),
('Presa Valle de Bravo','Estado de México','Presa',19.1900,-100.1300);

INSERT INTO measurements(water_body_id,measured_at,wqi,ph,dissolved_oxygen,turbidity,temperature,tds) VALUES
(1,'2026-03-10 09:00:00',63.5,8.10,6.20,12.4,22.1,480),
(1,'2026-06-10 09:00:00',59.2,8.30,5.80,16.2,24.0,510),
(1,'2026-09-10 09:00:00',66.1,7.90,6.50,10.8,23.2,495),
(2,'2026-03-12 10:00:00',71.0,7.80,7.10,8.2,19.0,320),
(2,'2026-06-12 10:00:00',68.4,8.00,6.80,9.6,21.4,350),
(2,'2026-09-12 10:00:00',73.2,7.70,7.40,7.1,20.2,335),
(3,'2026-03-14 11:00:00',84.0,7.50,8.10,3.2,18.3,210),
(3,'2026-06-14 11:00:00',81.5,7.60,7.90,4.1,20.1,225),
(3,'2026-09-14 11:00:00',86.2,7.40,8.30,2.8,19.2,218),
(4,'2026-03-16 08:30:00',48.0,8.60,4.20,24.5,23.0,720),
(4,'2026-06-16 08:30:00',43.5,8.80,3.80,31.0,26.1,790),
(4,'2026-09-16 08:30:00',51.2,8.40,4.60,21.3,24.4,745),
(5,'2026-03-18 12:00:00',76.5,7.90,7.60,6.0,20.0,290),
(5,'2026-06-18 12:00:00',72.1,8.10,7.00,8.4,22.8,315),
(5,'2026-09-18 12:00:00',78.3,7.80,7.80,5.2,21.3,302);
