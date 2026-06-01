-- ============================================
-- Creación de la tabla genero
-- ============================================

CREATE TABLE genero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_genero VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

-- ============================================
-- Inserción de datos iniciales (CREATE)
-- ============================================

INSERT INTO genero (nombre_genero)
VALUES ('Masculino'), ('Femenino'), ('Prefiero no decir');

-- ============================================
-- Consulta de registros (READ)
-- ============================================

SELECT * FROM genero;

-- ============================================
-- Actualización de un registro (UPDATE)
-- ============================================

UPDATE genero
SET nombre_genero = 'No especificado'
WHERE id = 3;

-- ============================================
-- Eliminación de un registro (DELETE)
-- ============================================

DELETE FROM genero
WHERE id = 3;