
-- insercion de datos para el funcionamiento de la tabla evaluacion

USE SICE;

SELECT * FROM grupo;

INSERT INTO genero (nombre_genero)
VALUES ('Masculino'), ('Femenino');

INSERT INTO persona 
(nombre, apellido_paterno, apellido_materno, curp, fecha_nacimiento, id_genero)
VALUES
('Juan','Perez','Lopez','PEPJ900101HSLRRN01','1990-01-01',1);

INSERT INTO persona
(nombre, apellido_paterno, apellido_materno, curp, fecha_nacimiento, id_genero)
VALUES
('Carlos','Ramirez','Lopez','RALC030101HSLPRN01','2003-01-01',1),
('Ana','Martinez','Hernandez','MAHA030202MSLRRN02','2003-02-02',2),
('Luis','Garcia','Torres','GATL030303HSLRRN03','2003-03-03',1),
('Maria','Lopez','Sanchez','LOSM030404MSLRRN04','2003-04-04',2);


SELECT * FROM carrera;

INSERT INTO alumno
(id_persona, numero_control, id_carrera, fecha_ingreso, semestre_actual)
VALUES
(1,'22100001',1,'2022-08-15',5),
(2,'22100002',1,'2022-08-15',5),
(3,'22100003',1,'2022-08-15',5),
(4,'22100004',1,'2022-08-15',5);



INSERT INTO inscripcion
(id_alumno,id_grupo,fecha_inscripcion,estatus)
VALUES
(1,1,'2026-01-10','Activo'),
(2,1,'2026-01-10','Activo'),
(3,1,'2026-01-10','Activo'),
(4,1,'2026-01-10','Activo');

INSERT INTO calificacion (id_inscripcion, id_evaluacion, calificacion)
VALUES
(1, 1, 90.00),
(1, 2, 85.00),
(1, 3, 88.00),
(1, 4, 92.00);


INSERT INTO puesto (nombre_puesto, descripcion)
VALUES ('Docente','Profesor de asignatura');


INSERT INTO empleado
(id_persona, numero_empleado, id_puesto, fecha_contratacion)
VALUES
(1,'EMP001',1,'2020-01-01');


INSERT INTO docente
(id_empleado, grado_academico, especialidad)
VALUES
(1,'Maestria','Sistemas');


INSERT INTO departamento (nombre, descripcion)
VALUES ('Sistemas','Departamento de Sistemas');

INSERT INTO nivel_carrera (nombre_nivel)
VALUES ('Licenciatura');


INSERT INTO carrera
(nombre,id_departamento,id_nivel)
VALUES
('Ingenieria en Sistemas',1,1);



INSERT INTO materia
(clave,nombre,creditos,horas_teoria,horas_practica)
VALUES
('SCD101','Base de Datos',5,3,2);


INSERT INTO periodo
(nombre_periodo,fecha_inicio,fecha_fin)
VALUES
('2026-1','2026-01-01','2026-06-30');


INSERT INTO edificio (nombre_edificio)
VALUES ('Edificio A');

INSERT INTO aula
(id_edificio,nombre,capacidad)
VALUES
(1,'A1',40);


INSERT INTO grupo
(id_materia,id_docente,id_periodo,id_aula,clave_grupo,capacidad)
VALUES
(1,1,1,1,'BD1',40);


INSERT INTO grupo
(id_materia,id_docente,id_periodo,id_aula,clave_grupo,capacidad)
VALUES
(1,1,1,1,'BD2',40);


INSERT INTO evaluacion (id_grupo,nombre,porcentaje)
VALUES
(1,'Tareas',20.00),
(1,'Proyecto',30.00),
(1,'Examen Parcial',20.00),
(1,'Examen Final',30.00);

SELECT * FROM evaluacion;


-- CRUD BASICO CON LA TABLA EVALUACION

USE SICE;
SELECT * FROM alumno;


UPDATE evaluacion
SET nombre = 'Tareas Modificadas'
WHERE id_evaluacion = 2;


SELECT * FROM evaluacion;

DELETE FROM evaluacion
WHERE id_evaluacion = 6;

SELECT * FROM evaluacion;


-- MANEJO DE SEGURIDAD CON PROCEDURES EN LA TABLA EVALUACION 
USE SICE;

/*
Este procedimiento permite registrar una nueva evaluación dentro de un grupo académico.

La evaluación representa un componente de calificación dentro de una materia
*/

DROP PROCEDURE IF EXISTS sp_insertar_evaluacion;

CREATE PROCEDURE sp_insertar_evaluacion(
    IN p_id_grupo INT,
    IN p_nombre VARCHAR(50),
    IN p_porcentaje DECIMAL(5,2)
)
INSERT INTO evaluacion (id_grupo, nombre, porcentaje)
VALUES (p_id_grupo, p_nombre, p_porcentaje);



/*
Este procedimiento tiene como objetivo consultar todos los registros almacenados en la tabla evaluacion.

Su función principal es recuperar la información relacionada con las evaluaciones registradas en el sistema
*/

DROP PROCEDURE IF EXISTS sp_mostrar_evaluaciones;

CREATE PROCEDURE sp_mostrar_evaluaciones()
SELECT
    e.id_evaluacion,
    e.nombre AS evaluacion,
    e.porcentaje,
    g.clave_grupo,
    m.nombre AS materia
FROM evaluacion e
JOIN grupo g ON e.id_grupo = g.id_grupo
JOIN materia m ON g.id_materia = m.id_materia;


/*
Este procedimiento permite consultar una evaluación específica a partir de su identificador.
*/


DROP PROCEDURE IF EXISTS sp_buscar_evaluacion_por_id_alumno;

CREATE PROCEDURE sp_buscar_evaluacion_por_id_alumno(
    IN p_id_alumno INT
)
SELECT 
    a.id_alumno,
    CONCAT(p.nombre,' ',p.apellido_paterno,' ',p.apellido_materno) AS nombre_alumno,
    m.nombre AS materia,
    e.nombre AS evaluacion,
    e.porcentaje,
    c.calificacion
FROM alumno a
JOIN persona p ON a.id_persona = p.id_persona
JOIN inscripcion i ON a.id_alumno = i.id_alumno
JOIN grupo g ON i.id_grupo = g.id_grupo
JOIN materia m ON g.id_materia = m.id_materia
JOIN calificacion c ON i.id_inscripcion = c.id_inscripcion
JOIN evaluacion e ON c.id_evaluacion = e.id_evaluacion
WHERE a.id_alumno = p_id_alumno;


/*
Este procedimiento permite consultar una evaluación específica a partir de su identificador.
*/

DROP PROCEDURE IF EXISTS sp_buscar_evaluacion_por_nombre_alumno;

CREATE PROCEDURE sp_buscar_evaluacion_por_nombre_alumno(
    IN p_nombre VARCHAR(100)
)
SELECT 
    a.id_alumno,
    CONCAT(p.nombre,' ',p.apellido_paterno,' ',p.apellido_materno) AS nombre_alumno,
    m.nombre AS materia,
    e.nombre AS evaluacion,
    e.porcentaje,
    c.calificacion
FROM alumno a
JOIN persona p ON a.id_persona = p.id_persona
JOIN inscripcion i ON a.id_alumno = i.id_alumno
JOIN grupo g ON i.id_grupo = g.id_grupo
JOIN materia m ON g.id_materia = m.id_materia
JOIN calificacion c ON i.id_inscripcion = c.id_inscripcion
JOIN evaluacion e ON c.id_evaluacion = e.id_evaluacion
WHERE p.nombre LIKE CONCAT('%',p_nombre,'%');



/*
Este procedimiento permite consultar una evaluación específica a partir de su identificador.
*/
DROP PROCEDURE IF EXISTS sp_buscar_evaluacion_por_curp;

CREATE PROCEDURE sp_buscar_evaluacion_por_curp(
    IN p_curp VARCHAR(18)
)
SELECT 
    a.id_alumno,
    CONCAT(p.nombre,' ',p.apellido_paterno,' ',p.apellido_materno) AS nombre_alumno,
    m.nombre AS materia,
    e.nombre AS evaluacion,
    e.porcentaje,
    c.calificacion
FROM alumno a
JOIN persona p ON a.id_persona = p.id_persona
JOIN inscripcion i ON a.id_alumno = i.id_alumno
JOIN grupo g ON i.id_grupo = g.id_grupo
JOIN materia m ON g.id_materia = m.id_materia
JOIN calificacion c ON i.id_inscripcion = c.id_inscripcion
JOIN evaluacion e ON c.id_evaluacion = e.id_evaluacion
WHERE p.curp = p_curp;


/*
El procedimiento sp_actualizar_evaluacion permite modificar la información de una evaluación existente.
*/

DROP PROCEDURE IF EXISTS sp_actualizar_evaluacion;

CREATE PROCEDURE sp_actualizar_evaluacion(
    IN p_id_evaluacion INT,
    IN p_id_grupo INT,
    IN p_nombre VARCHAR(50),
    IN p_porcentaje DECIMAL(5,2)
)
UPDATE evaluacion
SET
    id_grupo = p_id_grupo,
    nombre = p_nombre,
    porcentaje = p_porcentaje
WHERE id_evaluacion = p_id_evaluacion;


/*
Este procedimiento permite eliminar una evaluación existente del sistema.
*/

DROP PROCEDURE IF EXISTS sp_eliminar_evaluacion;

CREATE PROCEDURE sp_eliminar_evaluacion(
    IN p_id_evaluacion INT
)
DELETE FROM evaluacion
WHERE id_evaluacion = p_id_evaluacion
AND id_evaluacion NOT IN (
    SELECT id_evaluacion FROM calificacion
);

CALL sp_insertar_evaluacion(1, 'Tareas', 20.00);
CALL sp_insertar_evaluacion(1, 'Proyecto', 30.00);
CALL sp_insertar_evaluacion(1, 'Examen Parcial', 20.00);
CALL sp_insertar_evaluacion(1, 'Examen Final', 30.00);

SELECT * FROM evaluacion;


CALL sp_mostrar_evaluaciones();


CALL sp_buscar_evaluacion_por_nombre_alumno('Carlos');

SELECT * FROM evaluacion;

CALL sp_actualizar_evaluacion(1,1,'Tareas Modificadas',25.00);

SELECT * FROM evaluacion;

CALL sp_eliminar_evaluacion(7);

SELECT * FROM evaluacion;

-- MANEJO DE SEGURIDAD CON USARIOS EN LA TABLA EVALUACION

USE  sice
CREATE USER 'usuario_app'@'localhost' IDENTIFIED BY '123456';

GRANT USAGE ON sice.* TO 'usuario_app'@'localhost';

REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'usuario_app'@'localhost';
GRANT USAGE ON sice.* TO 'usuario_app'@'localhost';

GRANT EXECUTE ON PROCEDURE sice.sp_insertar_evaluacion TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.sp_mostrar_evaluaciones TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.sp_buscar_evaluacion_por_id_alumno TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.sp_actualizar_evaluacion TO 'usuario_app'@'localhost';
GRANT EXECUTE ON PROCEDURE sice.sp_eliminar_evaluacion TO 'usuario_app'@'localhost';


FLUSH PRIVILEGES;

SHOW GRANTS FOR 'usuario_app'@'localhost';


SHOW CREATE PROCEDURE sice.sp_insertar_evaluacion;
