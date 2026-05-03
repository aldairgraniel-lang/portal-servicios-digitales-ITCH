SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS servicios
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE servicios;

-- =========================
-- CONFIGURACION
-- =========================
CREATE TABLE IF NOT EXISTS configuracion (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(50) NOT NULL UNIQUE,
  valor VARCHAR(50) NOT NULL
);

INSERT IGNORE INTO configuracion (clave, valor) VALUES 
('registro_abierto', '0'),
('registro_ingles_abierto', '0'),
('registro_presentacion_abierto', '0'),
('registro_aceptacion_abierto', '0'),
('registro_justificantes_abierto', '0');

-- =========================
-- REPRESENTANTES
-- =========================
CREATE TABLE IF NOT EXISTS representantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_control VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- CARRERAS
-- =========================
CREATE TABLE IF NOT EXISTS carreras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL
);

INSERT IGNORE INTO carreras (nombre) VALUES
('Ingeniería en Administración'),
('Licenciatura en Administración'),
('Arquitectura'),
('Licenciatura en Biología'),
('Licenciatura en Turismo'),
('Ingeniería Civil'),
('Contador Público'),
('Ingeniería Eléctrica'),
('Ingeniería Electromecánica'),
('Ingeniería en Gestión Empresarial'),
('Ingeniería en Desarrollo de Aplicaciones'),
('Ingeniería en Sistemas Computacionales'),
('Ingeniería en Tecnologías de la Información y Comunicaciones');

-- =========================
-- SEMESTRES
-- =========================
CREATE TABLE IF NOT EXISTS semestres (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero INT NOT NULL
  
);

INSERT IGNORE INTO semestres (numero) VALUES
(1),(2),(3),(4),(5),(6),(7),(8),(9),(10),(11),(12);

-- =========================
-- TIPOS DE ESTUDIANTE
-- =========================
CREATE TABLE IF NOT EXISTS tipo_estudiante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

INSERT IGNORE INTO tipo_estudiante (nombre) VALUES 
('Egresado'), 
('Cursando semestre');

-- =========================
-- CURSOS
-- =========================
CREATE TABLE IF NOT EXISTS cursos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  clave VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO cursos (nombre) VALUES
('Curso de Verano - Matemáticas'),
('Curso de Verano - Física'),
('Curso de Verano - Programación');

-- =========================
-- USUARIOS
-- =========================
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','docente') NOT NULL DEFAULT 'docente'
);

INSERT IGNORE INTO usuarios (id, usuario, password, rol) VALUES
(1,'admin', MD5('123456'), 'admin'),
(2,'docente', MD5('123456'), 'docente');

-- =========================
-- PERIODOS
-- =========================
CREATE TABLE IF NOT EXISTS periodos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

INSERT IGNORE INTO periodos (nombre) VALUES 
('ENERO-JUNIO'), 
('AGOSTO-DICIEMBRE'), 
('EGRESADO');

-- =========================
-- TIPOS DE TRÁMITE
-- =========================
CREATE TABLE IF NOT EXISTS tipos_tramite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tramite VARCHAR(100) NOT NULL
);

INSERT IGNORE INTO tipos_tramite (nombre_tramite) VALUES 
('SERVICIO SOCIAL'), 
('RESIDENCIA PROFESIONAL');

-- =========================
-- VERANO
-- =========================
CREATE TABLE IF NOT EXISTS VERANO (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  numero_celular VARCHAR(100) NOT NULL,
  numero_control VARCHAR(100) NOT NULL,
  carrera VARCHAR(150) NOT NULL,
  semestre INT NOT NULL,
  curso_interes VARCHAR(150) NOT NULL,
  representante_1 VARCHAR(150) NOT NULL,
  representante_2 VARCHAR(150) NOT NULL,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =========================
-- AVISOS
-- =========================
CREATE TABLE IF NOT EXISTS avisos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(200) NOT NULL,
  contenido TEXT NOT NULL,
  tipo ENUM('info','advertencia','urgente') DEFAULT 'info',
  activo TINYINT(1) DEFAULT 1,
  id_docente INT NOT NULL,
  archivo VARCHAR(255),
  fecha_pub TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_docente FOREIGN KEY (id_docente) REFERENCES usuarios(id)
  ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- REGISTRO DE INGLÉS
-- =========================
CREATE TABLE IF NOT EXISTS registro_ingles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    numero_control VARCHAR(20) NOT NULL,
    carrera VARCHAR(100) NOT NULL,
    periodo VARCHAR(50) NOT NULL,
    tipo_alumno VARCHAR(50) NOT NULL,
    semestre VARCHAR(20) DEFAULT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- SOLICITUDES DE CARTAS
-- =========================
CREATE TABLE IF NOT EXISTS solicitudes_cartas_presentacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_estudiante VARCHAR(255) NOT NULL,
    numero_control VARCHAR(20) NOT NULL,
    tipo_tramite VARCHAR(100) NOT NULL,
    archivo_pdf VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS solicitudes_cartas_aceptacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_control VARCHAR(20) NOT NULL,
    tipo_tramite VARCHAR(100) NOT NULL,
    archivo_pdf VARCHAR(255) DEFAULT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- JUSTIFICANTES
-- =========================
CREATE TABLE IF NOT EXISTS justificantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    n_control VARCHAR(15) NOT NULL,
    motivo VARCHAR(50) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    archivo_ruta VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
