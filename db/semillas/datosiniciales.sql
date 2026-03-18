USE SICE;

-- GENERO

INSERT INTO genero (nombre_genero) VALUES
('Masculino'),
('Femenino'),
('No especificado');

-- ROLES

INSERT INTO rol (nombre_rol, descripcion, estatus) VALUES
('Administrador', 'Control total del sistema', TRUE),
('Docente', 'Profesor del sistema', TRUE),
('Alumno', 'Estudiante inscrito', TRUE),
('Directivo', 'Miembro de dirección o comité', TRUE);

-- MODULOS

INSERT INTO modulo (nombre_modulo, descripcion) VALUES
('Seguridad', 'Gestión de usuarios, roles y permisos'),
('Personas', 'Gestión de datos personales'),
('Recursos Humanos', 'Gestión de empleados y docentes'),
('Gestión Académica', 'Carreras, planes, materias y grupos'),
('Control Escolar', 'Inscripciones y calificaciones'),
('Eventos', 'Eventos institucionales'),
('Comité', 'Gestión de solicitudes y resoluciones');

-- PERMISOS

INSERT INTO permiso (nombre_permiso, descripcion) VALUES
('crear', 'Permite crear registros'),
('editar', 'Permite editar registros'),
('eliminar', 'Permite eliminar registros'),
('ver', 'Permite visualizar información');

-- NIVEL CARRERA

INSERT INTO nivel_carrera (nombre_nivel) VALUES
('Licenciatura'),
('Maestría'),
('Doctorado');

-- DEPARTAMENTOS (BASE)

INSERT INTO departamento (nombre, descripcion, estatus) VALUES
('Sistemas y Computación', 'Departamento académico de TI', TRUE),
('Ciencias Básicas', 'Departamento de matemáticas y física', TRUE),
('Administración', 'Departamento administrativo', TRUE);

-- PUESTOS

INSERT INTO puesto (nombre_puesto, descripcion) VALUES
('Docente', 'Profesor de asignatura'),
('Jefe de Departamento', 'Responsable del departamento'),
('Administrativo', 'Personal administrativo');

-- TIPO EVENTO

INSERT INTO tipo_evento (nombre_tipo) VALUES
('Académico'),
('Cultural'),
('Deportivo'),
('Conferencia'),
('Seminario');

-- TIPO SOLICITUD

INSERT INTO tipo_solicitud (nombre_tipo) VALUES
('Cambio de carrera'),
('Reingreso'),
('Baja temporal'),
('Baja definitiva'),
('Solicitud especial académica');

-- PERIODOS (EJEMPLO BASE)

INSERT INTO periodo (nombre_periodo, fecha_inicio, fecha_fin, estatus) VALUES
('2026-1', '2026-01-15', '2026-06-30', TRUE),
('2026-2', '2026-08-01', '2026-12-15', TRUE);
