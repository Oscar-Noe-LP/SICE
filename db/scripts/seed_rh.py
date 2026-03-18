import random
from faker import Faker

fake = Faker("es_MX")


# generar numero empleado
# ejemplo RH-000123


def generar_numero_empleado(i):
    return f"RH-{i:05d}"


# Seeder principal


def seed_rh(cursor, conn, total_empleados=200):

    # DEPARTAMENTOS

    departamentos = [
        ("Sistemas y Computación", "Área de ingeniería en sistemas"),
        ("Ciencias Básicas", "Matemáticas, física y química"),
        ("Industrial", "Ingeniería industrial"),
        ("Electrónica", "Ingeniería electrónica"),
        ("Administración", "Área administrativa"),
        ("Servicios Escolares", "Control escolar"),
        ("Dirección", "Dirección institucional"),
    ]

    for d in departamentos:
        cursor.execute(
            """
        INSERT INTO departamento
        (nombre,descripcion)
        VALUES (%s,%s)
        """,
            d,
        )

    conn.commit()

    # PUESTOS

    puestos = [
        ("Docente", "Profesor académico"),
        ("Investigador", "Investigación académica"),
        ("Coordinador", "Coordinación académica"),
        ("Administrador", "Gestión administrativa"),
        ("Director", "Dirección institucional"),
        ("Secretaria", "Apoyo administrativo"),
    ]

    for p in puestos:
        cursor.execute(
            """
        INSERT INTO puesto
        (nombre_puesto,descripcion)
        VALUES (%s,%s)
        """,
            p,
        )

    conn.commit()

    # PERSONAS DISPONIBLES

    cursor.execute(
        """
    SELECT id_persona
    FROM persona
    ORDER BY RAND()
    LIMIT %s
    """,
        (total_empleados,),
    )

    personas = [p[0] for p in cursor.fetchall()]

    # obtener puestos
    cursor.execute("SELECT id_puesto FROM puesto")
    puestos_ids = [p[0] for p in cursor.fetchall()]

    # EMPLEADOS

    for i, persona in enumerate(personas):
        numero = generar_numero_empleado(i + 1)

        puesto = random.choice(puestos_ids)

        fecha = fake.date_between(start_date="-15y", end_date="-1y")

        cursor.execute(
            """
        INSERT INTO empleado
        (
            id_persona,
            numero_empleado,
            id_puesto,
            fecha_contratacion
        )
        VALUES (%s,%s,%s,%s)
        """,
            (persona, numero, puesto, fecha),
        )

    conn.commit()

    # DOCENTES

    cursor.execute(
        """
    SELECT id_empleado
    FROM empleado
    ORDER BY RAND()
    LIMIT %s
    """,
        (int(total_empleados * 0.5),),
    )

    empleados_docentes = [e[0] for e in cursor.fetchall()]

    grados = ["Licenciatura", "Maestría", "Doctorado"]

    especialidades = [
        "Inteligencia Artificial",
        "Bases de Datos",
        "Redes",
        "Ingeniería de Software",
        "Electrónica",
        "Matemáticas Aplicadas",
    ]

    for e in empleados_docentes:
        cursor.execute(
            """
        INSERT INTO docente
        (
            id_empleado,
            grado_academico,
            especialidad
        )
        VALUES (%s,%s,%s)
        """,
            (e, random.choice(grados), random.choice(especialidades)),
        )

    conn.commit()

    # ADSCRIPCIONES

    cursor.execute("SELECT id_departamento FROM departamento")
    departamentos_ids = [d[0] for d in cursor.fetchall()]

    cursor.execute("SELECT id_empleado FROM empleado")
    empleados_ids = [e[0] for e in cursor.fetchall()]

    for emp in empleados_ids:
        depto = random.choice(departamentos_ids)

        fecha_inicio = fake.date_between(start_date="-10y", end_date="-1y")

        cursor.execute(
            """
        INSERT INTO adscripcion
        (
            id_empleado,
            id_departamento,
            fecha_inicio
        )
        VALUES (%s,%s,%s)
        """,
            (emp, depto, fecha_inicio),
        )

    conn.commit()

    print("Modulo RH sembrado correctamente")
