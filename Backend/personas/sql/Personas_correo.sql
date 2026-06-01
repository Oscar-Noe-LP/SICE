USE sice;

-- ============================================================
-- DATOS BASE
-- Inserción de datos respetando integridad referencial
-- Solo se necesitan GENERO y PERSONA (persona_correo depende únicamente de id_persona)
-- ============================================================


-- GENERO
INSERT IGNORE INTO genero (id_genero, nombre_genero)
VALUES (1, 'Masculino'), (2, 'Femenino');

-- PERSONA
INSERT IGNORE INTO persona (id_persona, nombre, apellido_paterno, apellido_materno, curp, fecha_nacimiento, id_genero)
VALUES (1,'Juan','Perez','Lopez','PEJL010101HNLXXX01','2001-01-01',1);

-- PERSONA_CORREO (BASE)
INSERT IGNORE INTO persona_correo (id_correo, id_persona, correo)
VALUES (1,1,'juan.perez@ejemplo.com');

-- ============================================================
-- PROCEDIMIENTOS ALMACENADOS (CRUD PERSONA_CORREO)
-- ============================================================

DELIMITER $$

-- ------------------------------------------------------------
-- CONSULTAR CORREOS
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS ver_persona_correo$$
CREATE PROCEDURE ver_persona_correo()
BEGIN
    SELECT
        pc.id_correo,
        p.nombre,
        p.apellido_paterno,
        p.apellido_materno,
        pc.correo
    FROM persona_correo pc
    JOIN persona p ON pc.id_persona = p.id_persona
    ORDER BY pc.id_correo;
END$$

-- ------------------------------------------------------------
-- INSERTAR CORREO
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS insertar_persona_correo$$
CREATE PROCEDURE insertar_persona_correo(
    IN p_id_persona INT,
    IN p_correo VARCHAR(120)
)
BEGIN
    DECLARE existe_persona INT;
    
    IF p_correo IS NULL OR p_correo = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El correo es obligatorio';
    END IF;
    
    SELECT COUNT(*) INTO existe_persona FROM persona WHERE id_persona = p_id_persona;
    IF existe_persona = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La persona no existe';
    END IF;
    
    INSERT INTO persona_correo (id_persona, correo)
    VALUES (p_id_persona, p_correo);
    
    SELECT 'Correo electrónico insertado correctamente' AS mensaje;
END$$

-- ------------------------------------------------------------
-- ACTUALIZAR CORREO
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS actualizar_persona_correo$$
CREATE PROCEDURE actualizar_persona_correo(
    IN p_id INT,
    IN p_correo VARCHAR(120)
)
BEGIN
    DECLARE existe INT;
    
    IF p_correo IS NULL OR p_correo = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El correo es obligatorio';
    END IF;
    
    SELECT COUNT(*) INTO existe FROM persona_correo WHERE id_correo = p_id;
    IF existe = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El correo no existe';
    END IF;
    
    UPDATE persona_correo
    SET correo = p_correo
    WHERE id_correo = p_id;
    
    SELECT 'Correo electrónico actualizado correctamente' AS mensaje;
END$$

-- ------------------------------------------------------------
-- ELIMINAR CORREO
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS eliminar_persona_correo$$
CREATE PROCEDURE eliminar_persona_correo(
    IN p_id INT
)
BEGIN
    DECLARE existe INT;
    SELECT COUNT(*) INTO existe FROM persona_correo WHERE id_correo = p_id;
    IF existe = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El correo no existe';
    END IF;
    
    DELETE FROM persona_correo WHERE id_correo = p_id;
    SELECT 'Correo electrónico eliminado correctamente' AS mensaje;
END$$

DELIMITER ;

-- ============================================================
-- SEGURIDAD
-- ============================================================
CREATE USER IF NOT EXISTS 'usuario_app'@'localhost' IDENTIFIED BY '123456';
REVOKE ALL PRIVILEGES ON sice.* FROM 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.ver_persona_correo TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.insertar_persona_correo TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.actualizar_persona_correo TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.eliminar_persona_correo TO 'usuario_app'@'localhost';
FLUSH PRIVILEGES;

-- ============================================================
-- PRUEBAS
-- ============================================================
CALL ver_persona_correo();
CALL insertar_persona_correo(1, 'juan2@ejemplo.com');          
CALL actualizar_persona_correo(1, 'juan.actualizado@ejemplo.com');
CALL eliminar_persona_correo(2);                               
CALL ver_persona_correo();                                