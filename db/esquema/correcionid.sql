USE SICE;

-- =====================================================
-- 1. CREAR COLUMNAS SI NO EXISTEN
-- =====================================================

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'rol'
        AND COLUMN_NAME = 'clave'
    ),
    'SELECT "rol.clave existe";',
    'ALTER TABLE rol ADD COLUMN clave VARCHAR(50) NULL;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'modulo'
        AND COLUMN_NAME = 'clave'
    ),
    'SELECT "modulo.clave existe";',
    'ALTER TABLE modulo ADD COLUMN clave VARCHAR(50) NULL;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'permiso'
        AND COLUMN_NAME = 'clave'
    ),
    'SELECT "permiso.clave existe";',
    'ALTER TABLE permiso ADD COLUMN clave VARCHAR(100) NULL;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 2. ASIGNAR CLAVES A ROLES
-- =====================================================

UPDATE rol
SET clave = 'ADMIN'
WHERE LOWER(TRIM(nombre_rol)) = LOWER('Administrador');

UPDATE rol
SET clave = 'CONTROL_ESCOLAR'
WHERE LOWER(TRIM(nombre_rol)) = LOWER('Control Escolar');

UPDATE rol
SET clave = 'DOCENTE'
WHERE LOWER(TRIM(nombre_rol)) = LOWER('Docente');

UPDATE rol
SET clave = 'ALUMNO'
WHERE LOWER(TRIM(nombre_rol)) = LOWER('Alumno');

UPDATE rol
SET clave = 'DIRECTIVO'
WHERE LOWER(TRIM(nombre_rol)) = LOWER('Directivo');

-- =====================================================
-- 3. ASIGNAR CLAVES A MODULOS
-- =====================================================

UPDATE modulo
SET clave = 'SEGURIDAD'
WHERE LOWER(TRIM(nombre_modulo)) = LOWER('Seguridad');

UPDATE modulo
SET clave = 'PERSONAS'
WHERE LOWER(TRIM(nombre_modulo)) = LOWER('Personas');

UPDATE modulo
SET clave = 'RH'
WHERE LOWER(TRIM(nombre_modulo)) = LOWER('Recursos Humanos');

UPDATE modulo
SET clave = 'ACADEMICO'
WHERE LOWER(TRIM(nombre_modulo)) IN (
    LOWER('Académico'),
    LOWER('Academico'),
    LOWER('Gestión Académica'),
    LOWER('Gestion Academica')
);

UPDATE modulo
SET clave = 'ESCOLAR'
WHERE LOWER(TRIM(nombre_modulo)) IN (
    LOWER('Escolar'),
    LOWER('Control Escolar')
);

UPDATE modulo
SET clave = 'EVENTOS'
WHERE LOWER(TRIM(nombre_modulo)) = LOWER('Eventos');

UPDATE modulo
SET clave = 'COMITE'
WHERE LOWER(TRIM(nombre_modulo)) IN (
    LOWER('Comité'),
    LOWER('Comite')
);

-- =====================================================
-- 4. ASIGNAR CLAVES A PERMISOS
-- =====================================================

UPDATE permiso
SET clave = 'CREAR'
WHERE LOWER(TRIM(nombre_permiso)) = LOWER('crear');

UPDATE permiso
SET clave = 'EDITAR'
WHERE LOWER(TRIM(nombre_permiso)) = LOWER('editar');

UPDATE permiso
SET clave = 'ELIMINAR'
WHERE LOWER(TRIM(nombre_permiso)) = LOWER('eliminar');

UPDATE permiso
SET clave = 'VER'
WHERE LOWER(TRIM(nombre_permiso)) = LOWER('ver');

-- =====================================================
-- 5. RELLENAR CUALQUIER NULL RESTANTE
-- =====================================================

UPDATE rol
SET clave = CONCAT('ROL_', id_rol)
WHERE clave IS NULL OR TRIM(clave) = '';

UPDATE modulo
SET clave = CONCAT('MODULO_', id_modulo)
WHERE clave IS NULL OR TRIM(clave) = '';

UPDATE permiso
SET clave = CONCAT('PERMISO_', id_permiso)
WHERE clave IS NULL OR TRIM(clave) = '';

-- =====================================================
-- 6. HACER CLAVES OBLIGATORIAS
-- =====================================================

ALTER TABLE rol
MODIFY clave VARCHAR(50) NOT NULL;

ALTER TABLE modulo
MODIFY clave VARCHAR(50) NOT NULL;

ALTER TABLE permiso
MODIFY clave VARCHAR(100) NOT NULL;

-- =====================================================
-- 7. CREAR INDICES UNIQUE SI NO EXISTEN
-- =====================================================

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'rol'
        AND INDEX_NAME = 'uq_rol_clave'
    ),
    'SELECT "uq_rol_clave existe";',
    'ALTER TABLE rol ADD CONSTRAINT uq_rol_clave UNIQUE(clave);'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'modulo'
        AND INDEX_NAME = 'uq_modulo_clave'
    ),
    'SELECT "uq_modulo_clave existe";',
    'ALTER TABLE modulo ADD CONSTRAINT uq_modulo_clave UNIQUE(clave);'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'permiso'
        AND INDEX_NAME = 'uq_permiso_clave'
    ),
    'SELECT "uq_permiso_clave existe";',
    'ALTER TABLE permiso ADD CONSTRAINT uq_permiso_clave UNIQUE(clave);'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 8. VALIDACION FINAL
-- =====================================================

SELECT id_rol, nombre_rol, clave
FROM rol;

SELECT id_modulo, nombre_modulo, clave
FROM modulo;

SELECT id_permiso, nombre_permiso, clave
FROM permiso;

-- =====================================================
-- FIN MIGRACION
-- =====================================================

SELECT id_rol, nombre_rol, clave FROM rol;

SELECT id_modulo, nombre_modulo, clave FROM modulo;

SELECT id_permiso, nombre_permiso, clave FROM permiso;
