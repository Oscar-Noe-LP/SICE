-- SICE
-- MIGRACION FUNCIONAL FRONTEND/BACKEND

USE SICE;

-- 1. PERSONA

ALTER TABLE persona
ADD COLUMN IF NOT EXISTS estado_civil VARCHAR(50);

ALTER TABLE persona
ADD COLUMN IF NOT EXISTS estado VARCHAR(60);

-- 2. MATERIA

ALTER TABLE materia
ADD COLUMN IF NOT EXISTS descripcion TEXT;

-- 3. GRUPO - HORARIOS

ALTER TABLE grupo
ADD COLUMN IF NOT EXISTS dia VARCHAR(20);

ALTER TABLE grupo
ADD COLUMN IF NOT EXISTS hora_inicio TIME;

ALTER TABLE grupo
ADD COLUMN IF NOT EXISTS hora_fin TIME;

-- 4. ALUMNO.ESTATUS

ALTER TABLE alumno
MODIFY estatus VARCHAR(50) DEFAULT 'Activo';

-- 5. CATALOGO ESTATUS ALUMNO

CREATE TABLE IF NOT EXISTS estatus_alumno (
    id_estatus_alumno INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

INSERT IGNORE INTO estatus_alumno(nombre) VALUES
('Activo'),
('Baja Temporal'),
('Baja Definitiva'),
('Titulado'),
('Egresado');

-- 6. RELACION ALUMNO -> ESTATUS

ALTER TABLE alumno
ADD COLUMN IF NOT EXISTS id_estatus_alumno INT;

SET @fk_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE
        TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'alumno'
        AND CONSTRAINT_NAME = 'fk_alumno_estatus'
);

SET @sql = IF(
    @fk_exists = 0,
    'ALTER TABLE alumno
     ADD CONSTRAINT fk_alumno_estatus
     FOREIGN KEY (id_estatus_alumno)
     REFERENCES estatus_alumno(id_estatus_alumno)',
    'SELECT "FK fk_alumno_estatus ya existe"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. TURNO

CREATE TABLE IF NOT EXISTS turno (
    id_turno INT AUTO_INCREMENT PRIMARY KEY,
    nombre_turno VARCHAR(50) NOT NULL UNIQUE
);

INSERT IGNORE INTO turno(nombre_turno) VALUES
('Matutino'),
('Vespertino'),
('Mixto');

-- 8. RELACION GRUPO -> TURNO

ALTER TABLE grupo
ADD COLUMN IF NOT EXISTS id_turno INT;

SET @fk_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE
        TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'grupo'
        AND CONSTRAINT_NAME = 'fk_grupo_turno'
);

SET @sql = IF(
    @fk_exists = 0,
    'ALTER TABLE grupo
     ADD CONSTRAINT fk_grupo_turno
     FOREIGN KEY (id_turno)
     REFERENCES turno(id_turno)',
    'SELECT "FK fk_grupo_turno ya existe"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 9. KARDEX

CREATE TABLE IF NOT EXISTS kardex (
    id_kardex INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno INT NOT NULL,
    promedio_general DECIMAL(5,2),
    creditos_acumulados INT DEFAULT 0,
    FOREIGN KEY (id_alumno)
    REFERENCES alumno(id_alumno)
);

CREATE TABLE IF NOT EXISTS detalle_kardex (
    id_detalle_kardex INT AUTO_INCREMENT PRIMARY KEY,
    id_kardex INT NOT NULL,
    id_materia INT NOT NULL,
    calificacion_final DECIMAL(5,2),
    estado VARCHAR(30),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kardex)
    REFERENCES kardex(id_kardex),
    FOREIGN KEY (id_materia)
    REFERENCES materia(id_materia)
);

-- 10. VALIDACIONES

DESCRIBE persona;
DESCRIBE materia;
DESCRIBE grupo;
DESCRIBE alumno;

SELECT * FROM estatus_alumno;
SELECT * FROM turno;

-- FIN MIGRACION
