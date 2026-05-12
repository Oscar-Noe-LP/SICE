USE SICE;

-- ── 1. Insertar roles ──────────────────────────────────────────
INSERT IGNORE INTO rol (nombre_rol, descripcion, estatus) VALUES
('admin',             'Administrador con acceso total',      TRUE),
('docente',           'Docente — acceso a sus grupos',       TRUE),
('servicios-escolares','Gestión y control escolar',          TRUE);

-- ── 2. Crear personas ─────────────────────────────────────────
INSERT INTO persona (nombre, apellido_paterno, estatus) VALUES
('David',  NULL, TRUE),
('Garza',  NULL, TRUE),
('Trivis', NULL, TRUE);

-- ── 3. Crear usuarios con hash MD5 ───────────────────────────
-- md5('David14')          = 'b4d6a4a9c4c6de2c658e2a35f9cabb86'
-- md5('Elpoderdelamor')   = '4d579f0cc1d3dcc65ea9f5e0d5452cee'
-- md5('123456789')        = '25f9e794323b453885f5181f1b624d0b'

INSERT INTO usuario (id_persona, nombre_usuario, password_hash, estatus)
SELECT id_persona, 'David',  'b4d6a4a9c4c6de2c658e2a35f9cabb86', TRUE
FROM persona WHERE nombre = 'David' ORDER BY id_persona DESC LIMIT 1;

INSERT INTO usuario (id_persona, nombre_usuario, password_hash, estatus)
SELECT id_persona, 'Garza',  '4d579f0cc1d3dcc65ea9f5e0d5452cee', TRUE
FROM persona WHERE nombre = 'Garza' ORDER BY id_persona DESC LIMIT 1;

INSERT INTO usuario (id_persona, nombre_usuario, password_hash, estatus)
SELECT id_persona, 'Trivis', '25f9e794323b453885f5181f1b624d0b', TRUE
FROM persona WHERE nombre = 'Trivis' ORDER BY id_persona DESC LIMIT 1;

-- ── 4. Asignar roles ──────────────────────────────────────────
INSERT INTO usuario_rol (id_usuario, id_rol)
SELECT u.id_usuario, r.id_rol
FROM usuario u, rol r
WHERE u.nombre_usuario = 'David' AND r.nombre_rol = 'admin';

INSERT INTO usuario_rol (id_usuario, id_rol)
SELECT u.id_usuario, r.id_rol
FROM usuario u, rol r
WHERE u.nombre_usuario = 'Garza' AND r.nombre_rol = 'docente';

INSERT INTO usuario_rol (id_usuario, id_rol)
SELECT u.id_usuario, r.id_rol
FROM usuario u, rol r
WHERE u.nombre_usuario = 'Trivis' AND r.nombre_rol = 'servicios-escolares';

-- ── 5. Verificar ──────────────────────────────────────────────
SELECT u.nombre_usuario, r.nombre_rol
FROM usuario u
JOIN usuario_rol ur ON u.id_usuario = ur.id_usuario
JOIN rol r ON ur.id_rol = r.id_rol
WHERE u.nombre_usuario IN ('David','Garza','Trivis');



-- Hash MD5 correcto de "David14"
UPDATE usuario
SET password_hash = '18b142801c5931681d66d48f282736cd'
WHERE nombre_usuario = 'David';

-- Hash MD5 correcto de "Elpoderdelamor"
UPDATE usuario
SET password_hash = '58d6c3eb1cdb7c77bf2817ab63da3fa1'
WHERE nombre_usuario = 'Garza';

-- Verificar los 3
SELECT nombre_usuario, password_hash FROM usuario
WHERE nombre_usuario IN ('David', 'Garza', 'Trivis');
