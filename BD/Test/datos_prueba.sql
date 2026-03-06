-- Insertar géneros
INSERT INTO genero (nombre_genero) VALUES ('Masculino'), ('Femenino');

-- Insertar departamentos y niveles
INSERT INTO departamento (nombre) VALUES ('Tecnologías de la Información');
INSERT INTO nivel_carrera (nombre) VALUES ('Licenciatura');

-- Insertar personas
INSERT INTO persona (nombre, apellido_paterno, apellido_materno, curp, fecha_nacimiento, id_genero)
VALUES ('Juan', 'Pérez', 'López', 'PEPJ800101HDFRRN09', '1980-01-01', 1),
       ('María', 'García', 'Hernández', 'GAHM950505MDFRRN08', '1995-05-05', 2);

-- Insertar carrera
INSERT INTO carrera (nombre, id_departamento, id_nivel, estatus)
VALUES ('Ingeniería en Sistemas', 1, 1, TRUE);

-- Insertar alumnos
INSERT INTO alumno (id_persona, numero_control, id_carrera, fecha_ingreso, semestre_actual, estatus)
VALUES (1, '20260001', 1, '2024-08-01', 2, TRUE),
       (2, '20260002', 1, '2024-08-01', 2, TRUE);