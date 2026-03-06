-- Ver alumnos con su carrera
SELECT * FROM vw_alumnos_carrera;

-- Ver detalle de grupos
SELECT * FROM vw_grupos_detalle;

-- Probar procedure de calificaciones
CALL sp_calificaciones_alumno(1);

-- Probar procedure de alumnos en evento
CALL sp_alumnos_evento(1);