USE sice;

-- ============================================================
-- DATOS BASE
-- Inserción de registros necesarios para que el módulo
-- persona funcione de forma independiente.
-- Se usa INSERT IGNORE para evitar errores si los datos
-- ya existen en la base de datos.
-- ============================================================

-- Catálogo de géneros (dependencia directa de persona)
INSERT IGNORE INTO genero (id_genero, nombre_genero) VALUES (1, 'Masculino');
INSERT IGNORE INTO genero (id_genero, nombre_genero) VALUES (2, 'Femenino');
INSERT IGNORE INTO genero (id_genero, nombre_genero) VALUES (3, 'No especificado');

-- Personas de prueba
INSERT IGNORE INTO persona (id_persona, nombre, apellido_paterno, apellido_materno, curp, fecha_nacimiento, id_genero, estatus)
VALUES (1, 'Juan', 'García', 'López', 'GALJ010101HDFXXX01', '2001-01-01', 1, 1);

INSERT IGNORE INTO persona (id_persona, nombre, apellido_paterno, apellido_materno, curp, fecha_nacimiento, id_genero, estatus)
VALUES (2, 'María', 'Hernández', 'Martínez', 'HEMM020202MDFXXX02', '2002-02-02', 2, 1);

INSERT IGNORE INTO persona (id_persona, nombre, apellido_paterno, apellido_materno, curp, fecha_nacimiento, id_genero, estatus)
VALUES (3, 'Carlos', 'Martínez', 'Ruiz', 'MARC800101HDFXXX03', '1980-01-01', 1, 1);

-- ============================================================
-- PROCEDIMIENTOS ALMACENADOS
-- La aplicación accede a la tabla persona únicamente
-- a través de estos procedimientos. Ninguna operación se
-- realiza directamente sobre la tabla desde el exterior.
-- ============================================================

DELIMITER $$

-- ------------------------------------------------------------
-- sp_insertar_persona
-- Registra una nueva persona en el sistema.
-- Valida que:
--   - El CURP no esté ya registrado (es único)
--   - El género exista en el catálogo
--   - Los campos obligatorios no estén vacíos
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_insertar_persona$$
CREATE PROCEDURE sp_insertar_persona(
    IN pNombre           VARCHAR(100),  -- Nombre(s) de la persona
    IN pApellidoPaterno  VARCHAR(100),  -- Apellido paterno
    IN pApellidoMaterno  VARCHAR(100),  -- Apellido materno (puede ser NULL)
    IN pCurp             VARCHAR(18),   -- CURP único de 18 caracteres
    IN pFechaNacimiento  DATE,          -- Fecha de nacimiento
    IN pIdGenero         INT,           -- FK hacia catálogo genero
    IN pEstatus          TINYINT        -- 1 = activo, 0 = inactivo
)
BEGIN
    DECLARE vCurpExiste  INT DEFAULT 0;
    DECLARE vGeneroExiste INT DEFAULT 0;

    -- Validar que el nombre no esté vacío
    IF pNombre IS NULL OR pNombre = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El nombre no puede estar vacío.';
    END IF;

    -- Validar que el CURP no esté ya registrado
    SELECT COUNT(*) INTO vCurpExiste
    FROM persona WHERE curp = pCurp;

    IF vCurpExiste > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ya existe una persona registrada con ese CURP.';
    END IF;

    -- Validar que el género exista en el catálogo
    SELECT COUNT(*) INTO vGeneroExiste
    FROM genero WHERE id_genero = pIdGenero;

    IF vGeneroExiste = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El género especificado no existe en el catálogo.';
    END IF;

    -- Insertar la persona si pasan todas las validaciones
    INSERT INTO persona (nombre, apellido_paterno, apellido_materno, curp, fecha_nacimiento, id_genero, estatus)
    VALUES (pNombre, pApellidoPaterno, pApellidoMaterno, pCurp, pFechaNacimiento, pIdGenero, pEstatus);

    SELECT 'Persona registrada correctamente.' AS mensaje, LAST_INSERT_ID() AS id_persona;
END$$


-- ------------------------------------------------------------
-- sp_mostrar_personas
-- Devuelve todas las personas con información completa
-- usando JOIN para mostrar el nombre del género en lugar del ID.
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_mostrar_personas$$
CREATE PROCEDURE sp_mostrar_personas()
BEGIN
    SELECT
        p.id_persona,
        p.nombre,
        p.apellido_paterno,
        p.apellido_materno,
        -- Nombre completo construido en una sola columna
        CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) AS nombre_completo,
        p.curp,
        p.fecha_nacimiento,
        -- JOIN para mostrar el nombre del género en lugar del ID
        g.nombre_genero AS genero,
        -- Campo calculado para mostrar estatus legible
        CASE WHEN p.estatus = 1 THEN 'Activo' ELSE 'Inactivo' END AS estatus,
        p.fecha_creacion
    FROM persona p
    INNER JOIN genero g ON p.id_genero = g.id_genero
    ORDER BY p.apellido_paterno, p.apellido_materno, p.nombre;
END$$


-- ------------------------------------------------------------
-- sp_buscar_persona_por_curp
-- Busca una persona específica usando su CURP como identificador.
-- Útil para consultas individuales y validaciones.
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_buscar_persona_por_curp$$
CREATE PROCEDURE sp_buscar_persona_por_curp(
    IN pCurp VARCHAR(18) -- CURP de la persona a buscar
)
BEGIN
    DECLARE vExiste INT DEFAULT 0;

    -- Validar que el CURP exista antes de consultar
    SELECT COUNT(*) INTO vExiste
    FROM persona WHERE curp = pCurp;

    IF vExiste = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se encontró ninguna persona con ese CURP.';
    END IF;

    SELECT
        p.id_persona,
        p.nombre,
        p.apellido_paterno,
        p.apellido_materno,
        CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) AS nombre_completo,
        p.curp,
        p.fecha_nacimiento,
        g.nombre_genero AS genero,
        CASE WHEN p.estatus = 1 THEN 'Activo' ELSE 'Inactivo' END AS estatus,
        p.fecha_creacion
    FROM persona p
    INNER JOIN genero g ON p.id_genero = g.id_genero
    WHERE p.curp = pCurp;
END$$


-- ------------------------------------------------------------
-- sp_actualizar_persona
-- Modifica los datos de una persona existente.
-- Valida que la persona exista y que el nuevo CURP
-- no esté siendo usado por otra persona diferente.
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_actualizar_persona$$
CREATE PROCEDURE sp_actualizar_persona(
    IN pIdPersona        INT,           -- ID de la persona a modificar
    IN pNombre           VARCHAR(100),  -- Nuevo nombre
    IN pApellidoPaterno  VARCHAR(100),  -- Nuevo apellido paterno
    IN pApellidoMaterno  VARCHAR(100),  -- Nuevo apellido materno
    IN pCurp             VARCHAR(18),   -- Nuevo CURP
    IN pFechaNacimiento  DATE,          -- Nueva fecha de nacimiento
    IN pIdGenero         INT,           -- Nuevo género
    IN pEstatus          TINYINT        -- Nuevo estatus
)
BEGIN
    DECLARE vExiste     INT DEFAULT 0;
    DECLARE vCurpDuplicado INT DEFAULT 0;

    -- Verificar que la persona exista
    SELECT COUNT(*) INTO vExiste
    FROM persona WHERE id_persona = pIdPersona;

    IF vExiste = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se encontró la persona con ese ID.';
    END IF;

    -- Verificar que el CURP no esté siendo usado por otra persona
    SELECT COUNT(*) INTO vCurpDuplicado
    FROM persona
    WHERE curp = pCurp AND id_persona != pIdPersona;

    IF vCurpDuplicado > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El CURP ya está registrado para otra persona.';
    END IF;

    -- Actualizar los datos de la persona
    UPDATE persona
    SET nombre          = pNombre,
        apellido_paterno = pApellidoPaterno,
        apellido_materno = pApellidoMaterno,
        curp             = pCurp,
        fecha_nacimiento = pFechaNacimiento,
        id_genero        = pIdGenero,
        estatus          = pEstatus
    WHERE id_persona = pIdPersona;

    SELECT 'Persona actualizada correctamente.' AS mensaje;
END$$


-- ------------------------------------------------------------
-- sp_eliminar_persona
-- Elimina una persona por su ID.
-- Verifica que exista antes de eliminar.
-- Nota: si la persona tiene registros dependientes (alumno,
-- empleado, usuario) la base de datos rechazará la eliminación
-- por integridad referencial.
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_eliminar_persona$$
CREATE PROCEDURE sp_eliminar_persona(
    IN pIdPersona INT -- ID de la persona a eliminar
)
BEGIN
    DECLARE vExiste INT DEFAULT 0;

    -- Verificar que la persona exista antes de eliminar
    SELECT COUNT(*) INTO vExiste
    FROM persona WHERE id_persona = pIdPersona;

    IF vExiste = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se encontró la persona con ese ID.';
    END IF;

    -- Eliminar la persona
    DELETE FROM persona WHERE id_persona = pIdPersona;

    SELECT 'Persona eliminada correctamente.' AS mensaje;
END$$

DELIMITER ;

-- ============================================================
-- SEGURIDAD
-- Se usa el mismo usuario_app creado en el módulo anterior.
-- Solo se agregan los permisos de ejecución para los
-- procedimientos de este módulo.
-- ============================================================

-- Crear el usuario si no existe (por si se corre este script solo)
CREATE USER IF NOT EXISTS 'usuario_app'@'localhost' IDENTIFIED BY 'AppSice2026!';

-- Otorgar permiso únicamente para ejecutar los procedimientos del módulo
GRANT EXECUTE ON PROCEDURE sice.sp_insertar_persona      TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.sp_mostrar_personas       TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.sp_buscar_persona_por_curp TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.sp_actualizar_persona     TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.sp_eliminar_persona       TO 'usuario_app'@'localhost';

-- Aplicar los cambios de permisos
FLUSH PRIVILEGES;

-- ============================================================
-- PRUEBAS
-- Consultas para verificar que los procedimientos
-- funcionan correctamente después de ejecutar el script.
-- ============================================================

-- Ver todas las personas registradas
CALL sp_mostrar_personas();

-- Buscar una persona por CURP
CALL sp_buscar_persona_por_curp('GALJ010101HDFXXX01');
