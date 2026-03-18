import random
from faker import Faker

fake = Faker("es_MX")


# generar clave materia
# ejemplo: SIS101


def generar_clave(i):

    prefijos = ["SIS", "MAT", "IND", "ELE", "ADM"]

    return f"{random.choice(prefijos)}{100 + i}"


# Seeder principal


def seed_academico(cursor, conn):

    # NIVELES

    niveles = ["Licenciatura", "Maestría", "Doctorado"]

    for n in niveles:
        cursor.execute(
            """
        INSERT INTO nivel_carrera(nombre_nivel)
        VALUES(%s)
        """,
            (n,),
        )

    conn.commit()

    # DEPARTAMENTOS DISPONIBLES

    cursor.execute("SELECT id_departamento FROM departamento")
    departamentos = [d[0] for d in cursor.fetchall()]

    cursor.execute("SELECT id_nivel FROM nivel_carrera")
    niveles = [n[0] for n in cursor.fetchall()]

    # CARRERAS

    carreras = [
        "Ingeniería en Sistemas Computacionales",
        "Ingeniería Industrial",
        "Ingeniería Electrónica",
        "Ingeniería Mecánica",
        "Ingeniería en Gestión Empresarial",
    ]

    for c in carreras:
        cursor.execute(
            """
        INSERT INTO carrera
        (nombre,id_departamento,id_nivel)
        VALUES(%s,%s,%s)
        """,
            (c, random.choice(departamentos), random.choice(niveles)),
        )

    conn.commit()

    # PLANES

    cursor.execute("SELECT id_carrera FROM carrera")
    carreras_ids = [c[0] for c in cursor.fetchall()]

    for carrera in carreras_ids:
        cursor.execute(
            """
        INSERT INTO plan_estudio
        (
            id_carrera,
            nombre_plan,
            anio_vigencia,
            total_creditos
        )
        VALUES(%s,%s,%s,%s)
        """,
            (carrera, "Plan 2020", 2020, 260),
        )

    conn.commit()

    # MATERIAS

    materias_ids = []

    for i in range(80):
        clave = generar_clave(i)

        nombre = fake.word().capitalize() + " Avanzado"

        creditos = random.randint(4, 8)

        ht = random.randint(2, 4)
        hp = random.randint(2, 4)

        cursor.execute(
            """
        INSERT INTO materia
        (
            clave,
            nombre,
            creditos,
            horas_teoria,
            horas_practica
        )
        VALUES(%s,%s,%s,%s,%s)
        """,
            (clave, nombre, creditos, ht, hp),
        )

        materias_ids.append(cursor.lastrowid)

    conn.commit()

    # PLAN_MATERIA

    cursor.execute("SELECT id_plan FROM plan_estudio")
    planes = [p[0] for p in cursor.fetchall()]

    for plan in planes:
        materias_plan = random.sample(materias_ids, 40)

        semestre = 1

        for m in materias_plan:
            cursor.execute(
                """
            INSERT INTO plan_materia
            (id_plan,id_materia,semestre)
            VALUES(%s,%s,%s)
            """,
                (plan, m, semestre),
            )

            semestre = semestre + 1 if semestre < 9 else 1

    conn.commit()

    # PRERREQUISITOS

    for i in range(20):
        m1 = random.choice(materias_ids)
        m2 = random.choice(materias_ids)

        if m1 != m2:
            cursor.execute(
                """
            INSERT IGNORE INTO prerrequisito
            (id_materia,id_materia_prerrequisito)
            VALUES(%s,%s)
            """,
                (m1, m2),
            )

    conn.commit()

    # PERIODOS

    periodos = [
        ("2024-1", "2024-01-15", "2024-06-30"),
        ("2024-2", "2024-08-10", "2024-12-20"),
        ("2025-1", "2025-01-15", "2025-06-30"),
    ]

    for p in periodos:
        cursor.execute(
            """
        INSERT INTO periodo
        (nombre_periodo,fecha_inicio,fecha_fin)
        VALUES(%s,%s,%s)
        """,
            p,
        )

    conn.commit()

    # EDIFICIOS

    edificios = ["A", "B", "C", "Laboratorios", "Administrativo"]

    for e in edificios:
        cursor.execute(
            """
        INSERT INTO edificio(nombre_edificio)
        VALUES(%s)
        """,
            (e,),
        )

    conn.commit()

    # AULAS

    cursor.execute("SELECT id_edificio FROM edificio")
    edificios_ids = [e[0] for e in cursor.fetchall()]

    aulas_ids = []

    for e in edificios_ids:
        for i in range(1, 6):
            cursor.execute(
                """
            INSERT INTO aula
            (id_edificio,nombre,capacidad)
            VALUES(%s,%s,%s)
            """,
                (e, f"Aula {i}", random.randint(25, 50)),
            )

            aulas_ids.append(cursor.lastrowid)

    conn.commit()

    # GRUPOS

    cursor.execute("SELECT id_docente FROM docente")
    docentes = [d[0] for d in cursor.fetchall()]

    cursor.execute("SELECT id_periodo FROM periodo")
    periodos = [p[0] for p in cursor.fetchall()]

    for i in range(120):
        materia = random.choice(materias_ids)
        docente = random.choice(docentes)
        periodo = random.choice(periodos)
        aula = random.choice(aulas_ids)

        cursor.execute(
            """
        INSERT INTO grupo
        (
            id_materia,
            id_docente,
            id_periodo,
            id_aula,
            clave_grupo,
            capacidad
        )
        VALUES(%s,%s,%s,%s,%s,%s)
        """,
            (materia, docente, periodo, aula, f"G{i + 1}", random.randint(25, 40)),
        )

    conn.commit()

    print("Modulo academico sembrado correctamente")
