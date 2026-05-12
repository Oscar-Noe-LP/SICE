USE SICE;

-- ── 1. Insertar roles ──────────────────────────────────────────
INSERT IGNORE INTO rol (nombre_rol, descripcion, estatus) VALUES
('admin',              'Administrador con acceso total', TRUE),
('docente',            'Docente — acceso a sus grupos',  TRUE),
('servicios-escolares','Gestión y control escolar',      TRUE);

-- ── 2. Crear personas ─────────────────────────────────────────
INSERT INTO persona (nombre, apellido_paterno, estatus) VALUES
('David',  NULL, TRUE),
('Garza',  NULL, TRUE),
('Trivis', NULL, TRUE);

-- ── 3. Crear usuarios con hash MD5 ───────────────────────────
-- Contraseñas:
--   David  → David14
--   Garza  → Elpoderdelamor
--   Trivis → VBM027A

INSERT INTO usuario (id_persona, nombre_usuario, password_hash, estatus)
SELECT id_persona, 'David',  '18b142801c5931681d66d48f282736cd', TRUE
FROM persona WHERE nombre = 'David'  ORDER BY id_persona DESC LIMIT 1;

INSERT INTO usuario (id_persona, nombre_usuario, password_hash, estatus)
SELECT id_persona, 'Garza',  '58d6c3eb1cdb7c77bf2817ab63da3fa1', TRUE
FROM persona WHERE nombre = 'Garza'  ORDER BY id_persona DESC LIMIT 1;

INSERT INTO usuario (id_persona, nombre_usuario, password_hash, estatus)
SELECT id_persona, 'Trivis', '9f64c2b059797dd473153a5ee282fa91', TRUE
FROM persona WHERE nombre = 'Trivis' ORDER BY id_persona DESC LIMIT 1;

-- ── 4. Asignar roles ──────────────────────────────────────────
INSERT INTO usuario_rol (id_usuario, id_rol)
SELECT u.id_usuario, r.id_rol FROM usuario u, rol r
WHERE u.nombre_usuario = 'David'  AND r.nombre_rol = 'admin';

INSERT INTO usuario_rol (id_usuario, id_rol)
SELECT u.id_usuario, r.id_rol FROM usuario u, rol r
WHERE u.nombre_usuario = 'Garza'  AND r.nombre_rol = 'docente';

INSERT INTO usuario_rol (id_usuario, id_rol)
SELECT u.id_usuario, r.id_rol FROM usuario u, rol r
WHERE u.nombre_usuario = 'Trivis' AND r.nombre_rol = 'servicios-escolares';

-- ── 5. Verificar ──────────────────────────────────────────────
SELECT u.nombre_usuario, r.nombre_rol
FROM usuario u
JOIN usuario_rol ur ON u.id_usuario = ur.id_usuario
JOIN rol r          ON ur.id_rol    = r.id_rol
WHERE u.nombre_usuario IN ('David', 'Garza', 'Trivis');
