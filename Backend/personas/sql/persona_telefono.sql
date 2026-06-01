DELIMITER //
CREATE PROCEDURE InsertPersonaTelefono(
    IN p_id_persona INT,
    IN p_telefono VARCHAR(20)
)
BEGIN
    INSERT INTO persona_telefono (id_persona, telefono)
    VALUES (p_id_persona, p_telefono);
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE GetTelefonosByPersona(
    IN p_id_persona INT
)
BEGIN
    SELECT id_telefono, id_persona, telefono
    FROM persona_telefono
    WHERE id_persona = p_id_persona;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE UpdateTelefono(
    IN p_id_telefono INT,
    IN p_telefono VARCHAR(20)
)
BEGIN
    UPDATE persona_telefono
    SET telefono = p_telefono
    WHERE id_telefono = p_id_telefono;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE DeleteTelefono(
    IN p_id_telefono INT
)
BEGIN
    DELETE FROM persona_telefono
    WHERE id_telefono = p_id_telefono;
END //
DELIMITER ;

-- insertar persona para testeo

INSERT INTO persona (
    nombre,
    apellido_paterno,
    apellido_materno,
    curp,
    fecha_nacimiento,
    id_genero,
    estatus
) VALUES
('Juan', 'Pérez', 'López', 'PEPJ800101HDFRRN01', '1980-01-01', 1, TRUE),
('María', 'García', 'Hernández', 'GAHM850505MDFRRN02', '1985-05-05', 2, TRUE),
('Carlos', 'Ramírez', 'Santos', 'RASC900909HDFRRN03', '1990-09-09', 1, TRUE);

INSERT INTO genero (nombre_genero) VALUES
('Masculino'),
('Femenino'),
('No Binario'),
('Otro');

SELECT * FROM persona

SELECT * FROM persona_telefono