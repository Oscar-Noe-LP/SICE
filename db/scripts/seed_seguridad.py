import random


def seed_seguridad(cursor, conn):

    # modulos

    modulos = [
        ("Seguridad", "Gestión de usuarios y permisos"),
        ("Personas", "Información general de personas"),
        ("Recursos Humanos", "Gestión de empleados y docentes"),
        ("Académico", "Carreras, materias y planes"),
        ("Escolar", "Alumnos, inscripciones y calificaciones"),
        ("Eventos", "Eventos académicos y culturales"),
        ("Comité", "Solicitudes y resoluciones académicas"),
    ]

    for m in modulos:
        cursor.execute(
            """
            INSERT INTO modulo (nombre_modulo, descripcion)
            VALUES (%s,%s)
            """,
            m,
        )

    conn.commit()

    # permisos

    permisos = [
        ("crear_usuario", "Crear usuarios"),
        ("editar_usuario", "Editar usuarios"),
        ("eliminar_usuario", "Eliminar usuarios"),
        ("ver_usuario", "Ver usuarios"),
        ("crear_rol", "Crear roles"),
        ("editar_rol", "Editar roles"),
        ("eliminar_rol", "Eliminar roles"),
        ("ver_rol", "Ver roles"),
    ]

    for p in permisos:
        cursor.execute(
            """
            INSERT INTO permiso (nombre_permiso, descripcion)
            VALUES (%s,%s)
            """,
            p,
        )

    conn.commit()

    # ROLES

    roles = [
        ("Administrador", "Control total del sistema"),
        ("Control Escolar", "Gestión de alumnos e inscripciones"),
        ("Docente", "Acceso a materias y calificaciones"),
        ("Alumno", "Acceso a información académica"),
    ]

    for r in roles:
        cursor.execute(
            """
            INSERT INTO rol (nombre_rol, descripcion)
            VALUES (%s,%s)
            """,
            r,
        )

    conn.commit()

    # permisos por modulo

    cursor.execute("SELECT id_permiso FROM permiso")
    permisos_ids = [p[0] for p in cursor.fetchall()]

    cursor.execute("SELECT id_modulo FROM modulo")
    modulos_ids = [m[0] for m in cursor.fetchall()]

    for p in permisos_ids:
        modulo = random.choice(modulos_ids)

        cursor.execute(
            """
            INSERT INTO permiso_modulo (id_permiso,id_modulo)
            VALUES (%s,%s)
            """,
            (p, modulo),
        )

    conn.commit()

    # Roles

    cursor.execute("SELECT id_rol FROM rol")
    roles_ids = [r[0] for r in cursor.fetchall()]

    for rol in roles_ids:
        for permiso in permisos_ids:
            if random.random() > 0.3:  # algunos permisos aleatorios
                cursor.execute(
                    """
                    INSERT INTO rol_permiso (id_rol,id_permiso)
                    VALUES (%s,%s)
                    """,
                    (rol, permiso),
                )

    conn.commit()

    print("Modulo Seguridad sembrado correctamente")
