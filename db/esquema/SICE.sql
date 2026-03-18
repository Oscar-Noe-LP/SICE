-- versionado inicial de la BD 
-- corrida y validada en mariadb 
-- creacion db

DROP DATABASE IF EXISTS SICE;
CREATE DATABASE SICE;
USE SICE;

-- modulo 1 : seguridad

CREATE TABLE modulo (
    id_modulo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_modulo VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
);

CREATE TABLE rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    estatus BOOLEAN DEFAULT TRUE
);

CREATE TABLE permiso (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    nombre_permiso VARCHAR(100) NOT NULL,
    descripcion TEXT
);

CREATE TABLE permiso_modulo (
    id_permiso INT,
    id_modulo INT,
    PRIMARY KEY (id_permiso, id_modulo),
    FOREIGN KEY (id_permiso) REFERENCES permiso(id_permiso),
    FOREIGN KEY (id_modulo) REFERENCES modulo(id_modulo)
);

CREATE TABLE rol_permiso (
    id_rol INT,
    id_permiso INT,
    PRIMARY KEY (id_rol, id_permiso),
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol),
    FOREIGN KEY (id_permiso) REFERENCES permiso(id_permiso)
);

-- modulo 2 : personas 

CREATE TABLE genero (
    id_genero INT AUTO_INCREMENT PRIMARY KEY, -- que inclusivos lol
    nombre_genero VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE persona (
    id_persona INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100),
    apellido_materno VARCHAR(100),
    curp VARCHAR(18) UNIQUE,
    fecha_nacimiento DATE,
    id_genero INT,
    estatus BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_genero) REFERENCES genero(id_genero)
);

CREATE TABLE persona_telefono (
    id_telefono INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_persona) REFERENCES persona(id_persona)
);

CREATE TABLE persona_correo (
    id_correo INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    correo VARCHAR(120) NOT NULL,
    FOREIGN KEY (id_persona) REFERENCES persona(id_persona)
);

CREATE TABLE persona_direccion (
    id_direccion INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    direccion TEXT NOT NULL,
    FOREIGN KEY (id_persona) REFERENCES persona(id_persona)
);

-- modulo 3 usuarios 

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    nombre_usuario VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255),
    estatus BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME,
    FOREIGN KEY (id_persona) REFERENCES persona(id_persona)
);

CREATE TABLE usuario_rol (
    id_usuario INT,
    id_rol INT,
    PRIMARY KEY (id_usuario, id_rol),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);

CREATE TABLE bitacora (
    id_bitacora INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_modulo INT,
    accion VARCHAR(200),
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    direccion_ip VARCHAR(45),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_modulo) REFERENCES modulo(id_modulo)
);

-- modulo 4 :  rh 

CREATE TABLE departamento (
    id_departamento INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    estatus BOOLEAN DEFAULT TRUE
);

CREATE TABLE puesto (
    id_puesto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_puesto VARCHAR(100) NOT NULL,
    descripcion TEXT
);

CREATE TABLE empleado (
    id_empleado INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    numero_empleado VARCHAR(20) UNIQUE NOT NULL,
    id_puesto INT NOT NULL,
    fecha_contratacion DATE NOT NULL,
    estatus BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_persona) REFERENCES persona(id_persona),
    FOREIGN KEY (id_puesto) REFERENCES puesto(id_puesto)
);

CREATE TABLE docente (
    id_docente INT AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT NOT NULL,
    grado_academico VARCHAR(150),
    especialidad VARCHAR(150),
    FOREIGN KEY (id_empleado) REFERENCES empleado(id_empleado)
);

CREATE TABLE adscripcion (
    id_adscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT NOT NULL,
    id_departamento INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    FOREIGN KEY (id_empleado) REFERENCES empleado(id_empleado),
    FOREIGN KEY (id_departamento) REFERENCES departamento(id_departamento)
);

-- modulo 5 : gestion academic

CREATE TABLE nivel_carrera (
    id_nivel INT AUTO_INCREMENT PRIMARY KEY,
    nombre_nivel VARCHAR(50) NOT NULL
);

CREATE TABLE carrera (
    id_carrera INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    id_departamento INT,
    id_nivel INT,
    estatus BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_departamento) REFERENCES departamento(id_departamento),
    FOREIGN KEY (id_nivel) REFERENCES nivel_carrera(id_nivel)
);

CREATE TABLE plan_estudio (
    id_plan INT AUTO_INCREMENT PRIMARY KEY,
    id_carrera INT,
    nombre_plan VARCHAR(100),
    anio_vigencia INT,
    total_creditos INT,
    estatus BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_carrera) REFERENCES carrera(id_carrera)
);

CREATE TABLE materia (
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(20) UNIQUE,
    nombre VARCHAR(150),
    creditos INT,
    horas_teoria INT,
    horas_practica INT,
    estatus BOOLEAN DEFAULT TRUE
);

CREATE TABLE plan_materia (
    id_plan INT,
    id_materia INT,
    semestre INT,
    PRIMARY KEY (id_plan, id_materia),
    FOREIGN KEY (id_plan) REFERENCES plan_estudio(id_plan),
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia)
);

CREATE TABLE prerrequisito (
    id_materia INT,
    id_materia_prerrequisito INT,
    PRIMARY KEY (id_materia, id_materia_prerrequisito),
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia),
    FOREIGN KEY (id_materia_prerrequisito) REFERENCES materia(id_materia)
);

CREATE TABLE periodo (
    id_periodo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_periodo VARCHAR(50),
    fecha_inicio DATE,
    fecha_fin DATE,
    estatus BOOLEAN DEFAULT TRUE
);

CREATE TABLE edificio (
    id_edificio INT AUTO_INCREMENT PRIMARY KEY,
    nombre_edificio VARCHAR(100)
);

CREATE TABLE aula (
    id_aula INT AUTO_INCREMENT PRIMARY KEY,
    id_edificio INT,
    nombre VARCHAR(50),
    capacidad INT,
    FOREIGN KEY (id_edificio) REFERENCES edificio(id_edificio)
);

CREATE TABLE grupo (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT,
    id_docente INT,
    id_periodo INT,
    id_aula INT,
    clave_grupo VARCHAR(10),
    capacidad INT,
    estatus BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia),
    FOREIGN KEY (id_docente) REFERENCES docente(id_docente),
    FOREIGN KEY (id_periodo) REFERENCES periodo(id_periodo),
    FOREIGN KEY (id_aula) REFERENCES aula(id_aula)
);

-- modulo 6 : control escolar

CREATE TABLE alumno (
    id_alumno INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT,
    numero_control VARCHAR(20) UNIQUE,
    id_carrera INT,
    fecha_ingreso DATE,
    semestre_actual INT,
    estatus BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_persona) REFERENCES persona(id_persona),
    FOREIGN KEY (id_carrera) REFERENCES carrera(id_carrera)
);

CREATE TABLE inscripcion (
    id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno INT,
    id_grupo INT,
    fecha_inscripcion DATE,
    estatus VARCHAR(50),
    FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno),
    FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo),
    UNIQUE (id_alumno, id_grupo)
);

CREATE TABLE evaluacion (
    id_evaluacion INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT,
    nombre VARCHAR(50),
    porcentaje DECIMAL(5,2),
    FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo)
);

CREATE TABLE calificacion (
    id_calificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_inscripcion INT,
    id_evaluacion INT,
    calificacion DECIMAL(5,2),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_inscripcion) REFERENCES inscripcion(id_inscripcion),
    FOREIGN KEY (id_evaluacion) REFERENCES evaluacion(id_evaluacion),
    UNIQUE (id_inscripcion, id_evaluacion)
);

-- modulo 7 : eventos

CREATE TABLE tipo_evento (
    id_tipo_evento INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tipo VARCHAR(100)
);

CREATE TABLE evento (
    id_evento INT AUTO_INCREMENT PRIMARY KEY,
    nombre_evento VARCHAR(200),
    id_tipo_evento INT,
    fecha DATE,
    descripcion TEXT,
    FOREIGN KEY (id_tipo_evento) REFERENCES tipo_evento(id_tipo_evento)
);

CREATE TABLE participacion_evento (
    id_participacion INT AUTO_INCREMENT PRIMARY KEY,
    id_evento INT,
    id_alumno INT,
    constancia_emitida BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_evento) REFERENCES evento(id_evento),
    FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno)
);

-- modulo 8: direccion / comite

CREATE TABLE tipo_solicitud (
    id_tipo_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tipo VARCHAR(150)
);

CREATE TABLE solicitud_comite (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT,
    id_tipo_solicitud INT,
    descripcion TEXT,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    estatus VARCHAR(50),
    FOREIGN KEY (id_persona) REFERENCES persona(id_persona),
    FOREIGN KEY (id_tipo_solicitud) REFERENCES tipo_solicitud(id_tipo_solicitud)
);

CREATE TABLE sesion_comite (
    id_sesion INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE,
    descripcion TEXT
);

CREATE TABLE resolucion_comite (
    id_resolucion INT AUTO_INCREMENT PRIMARY KEY,
    id_sesion INT,
    id_solicitud INT,
    decision TEXT,
    fecha_resolucion DATE,
    FOREIGN KEY (id_sesion) REFERENCES sesion_comite(id_sesion),
    FOREIGN KEY (id_solicitud) REFERENCES solicitud_comite(id_solicitud)
);
