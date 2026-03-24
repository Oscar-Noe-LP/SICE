USE sice;

-- ============================================================
-- DATOS BASE
-- Inserción de datos respetando integridad referencial
-- ============================================================

-- GENERO
INSERT IGNORE INTO genero (id_genero, nombre_genero)
VALUES (1, 'Masculino'), (2, 'Femenino');

-- PERSONA
INSERT IGNORE INTO persona (id_persona, nombre, apellido_paterno, apellido_materno, curp, fecha_nacimiento, id_genero)
VALUES (1,'Juan','Perez','Lopez','PEJL010101HNLXXX01','2001-01-01',1);

-- DEPARTAMENTO
INSERT IGNORE INTO departamento (id_departamento, nombre, descripcion)
VALUES (1,'Sistemas','Departamento de Sistemas');

-- NIVEL CARRERA
INSERT IGNORE INTO nivel_carrera (id_nivel, nombre_nivel)
VALUES (1,'Licenciatura');

-- CARRERA
INSERT IGNORE INTO carrera (id_carrera, nombre, id_departamento, id_nivel)
VALUES (1,'Ingenieria en Sistemas',1,1);

-- ALUMNO
INSERT IGNORE INTO alumno (id_alumno, id_persona, numero_control, id_carrera, fecha_ingreso, semestre_actual)
VALUES (1,1,'22180001',1,'2024-08-01',1);

-- PERIODO
INSERT IGNORE INTO periodo (id_periodo, nombre_periodo, fecha_inicio, fecha_fin)
VALUES (1,'2025-1','2025-01-01','2025-06-01');

-- EDIFICIO
INSERT IGNORE INTO edificio (id_edificio, nombre_edificio)
VALUES (1,'Edificio A');

-- AULA
INSERT IGNORE INTO aula (id_aula, id_edificio, nombre, capacidad)
VALUES (1,1,'A101',30);

-- PUESTO
INSERT IGNORE INTO puesto (id_puesto, nombre_puesto)
VALUES (1,'Profesor');

-- EMPLEADO
INSERT IGNORE INTO empleado (id_empleado, id_persona, numero_empleado, id_puesto, fecha_contratacion)
VALUES (1,1,'EMP001',1,'2020-01-01');

-- DOCENTE
INSERT IGNORE INTO docente (id_docente, id_empleado, grado_academico, especialidad)
VALUES (1,1,'Maestria','Programacion');

-- MATERIA
INSERT IGNORE INTO materia (id_materia, clave, nombre, creditos, horas_teoria, horas_practica)
VALUES (1,'ISC101','Programacion',8,4,4);

-- GRUPO
INSERT IGNORE INTO grupo (id_grupo, id_materia, id_docente, id_periodo, id_aula, clave_grupo, capacidad)
VALUES (1,1,1,1,1,'A1',30);

-- INSCRIPCION (BASE)
INSERT IGNORE INTO inscripcion (id_inscripcion, id_alumno, id_grupo, fecha_inscripcion, estatus)
VALUES (1,1,1,CURDATE(),'ACTIVA');


-- ============================================================
-- PROCEDIMIENTOS ALMACENADOS (CRUD INSCRIPCION)
-- ============================================================

DELIMITER $$

-- ------------------------------------------------------------
-- CONSULTAR INSCRIPCIONES
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS ver_inscripciones$$
CREATE PROCEDURE ver_inscripciones()
BEGIN
    SELECT 
        i.id_inscripcion,
        p.nombre,
        p.apellido_paterno,
        a.numero_control,
        g.clave_grupo,
        i.estatus,
        i.fecha_inscripcion
    FROM inscripcion i
    JOIN alumno a ON i.id_alumno = a.id_alumno
    JOIN persona p ON a.id_persona = p.id_persona
    JOIN grupo g ON i.id_grupo = g.id_grupo;
END$$


-- ------------------------------------------------------------
-- INSERTAR INSCRIPCION
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS insertar_inscripcion$$
CREATE PROCEDURE insertar_inscripcion(
    IN p_id_alumno INT,
    IN p_id_grupo INT,
    IN p_fecha DATE,
    IN p_estatus VARCHAR(20)
)
BEGIN
    IF p_estatus IS NULL OR p_estatus = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El estatus es obligatorio';
    END IF;

    INSERT INTO inscripcion
    (id_alumno, id_grupo, fecha_inscripcion, estatus)
    VALUES
    (p_id_alumno, p_id_grupo, p_fecha, p_estatus);

    SELECT 'Inscripción insertada correctamente' AS mensaje;
END$$


-- ------------------------------------------------------------
-- ACTUALIZAR INSCRIPCION
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS actualizar_inscripcion$$
CREATE PROCEDURE actualizar_inscripcion(
    IN p_id INT,
    IN p_estatus VARCHAR(20)
)
BEGIN
    DECLARE existe INT;

    SELECT COUNT(*) INTO existe FROM inscripcion WHERE id_inscripcion = p_id;

    IF existe = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La inscripción no existe';
    END IF;

    UPDATE inscripcion
    SET estatus = p_estatus
    WHERE id_inscripcion = p_id;

    SELECT 'Inscripción actualizada correctamente' AS mensaje;
END$$


-- ------------------------------------------------------------
-- ELIMINAR INSCRIPCION
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS eliminar_inscripcion$$
CREATE PROCEDURE eliminar_inscripcion(
    IN p_id INT
)
BEGIN
    DECLARE existe INT;

    SELECT COUNT(*) INTO existe FROM inscripcion WHERE id_inscripcion = p_id;

    IF existe = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La inscripción no existe';
    END IF;

    DELETE FROM inscripcion WHERE id_inscripcion = p_id;

    SELECT 'Inscripción eliminada correctamente' AS mensaje;
END$$

DELIMITER ;

-- ============================================================
-- SEGURIDAD
-- ============================================================

CREATE USER IF NOT EXISTS 'usuario_app'@'localhost' IDENTIFIED BY '123456';

REVOKE ALL PRIVILEGES ON sice.* FROM 'usuario_app'@'localhost';

GRANT EXECUTE ON PROCEDURE sice.ver_inscripciones TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.insertar_inscripcion TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.actualizar_inscripcion TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.eliminar_inscripcion TO 'usuario_app'@'localhost';

FLUSH PRIVILEGES;

-- ============================================================
-- PRUEBAS
-- ============================================================

CALL ver_inscripciones();

CALL insertar_inscripcion(1,1,CURDATE(),'ACTIVA');

CALL actualizar_inscripcion(1,'INACTIVA');

CALL eliminar_inscripcion(1);
