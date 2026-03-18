import random
from faker import Faker

fake = Faker("es_MX")


# numero de control
# formato:
# 22660231


def generar_numero_control(anio, contador):

    return f"{anio}66{contador:04d}"


# Seeder principal


def seed_control_escolar(cursor, conn, total_alumnos=1500):

    # PERSONAS DISPONIBLES

    cursor.execute(
        """
    SELECT id_persona
    FROM persona
    ORDER BY RAND()
    LIMIT %s
    """,
        (total_alumnos,),
    )

    personas = [p[0] for p in cursor.fetchall()]

    cursor.execute("SELECT id_carrera FROM carrera")
    carreras = [c[0] for c in cursor.fetchall()]

    # ALUMNOS

    contador = 1

    for persona in personas:
        fecha_ingreso = fake.date_between(start_date="-5y", end_date="-1y")

        anio = str(fecha_ingreso.year)[2:]

        numero_control = generar_numero_control(anio, contador)

        semestre = random.randint(1, 9)

        cursor.execute(
            """
        INSERT INTO alumno
        (
            id_persona,
            numero_control,
            id_carrera,
            fecha_ingreso,
            semestre_actual
        )
        VALUES (%s,%s,%s,%s,%s)
        """,
            (persona, numero_control, random.choice(carreras), fecha_ingreso, semestre),
        )

        contador += 1

    conn.commit()

    # INSCRIPCIONES

    cursor.execute("SELECT id_alumno FROM alumno")
    alumnos = [a[0] for a in cursor.fetchall()]

    cursor.execute("SELECT id_grupo FROM grupo")
    grupos = [g[0] for g in cursor.fetchall()]

    for alumno in alumnos:
        materias = random.sample(grupos, random.randint(4, 6))

        for grupo in materias:
            fecha = fake.date_between(start_date="-2y", end_date="today")

            estatus = random.choice(["inscrito", "aprobado", "reprobado"])

            cursor.execute(
                """
            INSERT IGNORE INTO inscripcion
            (
                id_alumno,
                id_grupo,
                fecha_inscripcion,
                estatus
            )
            VALUES (%s,%s,%s,%s)
            """,
                (alumno, grupo, fecha, estatus),
            )

    conn.commit()

    # EVALUACIONES

    cursor.execute("SELECT id_grupo FROM grupo")
    grupos = [g[0] for g in cursor.fetchall()]

    evaluaciones_por_grupo = {}

    for grupo in grupos:
        evaluaciones = [("Parcial 1", 30), ("Parcial 2", 30), ("Parcial 3", 40)]

        ids = []

        for e in evaluaciones:
            cursor.execute(
                """
            INSERT INTO evaluacion
            (
                id_grupo,
                nombre,
                porcentaje
            )
            VALUES (%s,%s,%s)
            """,
                (grupo, e[0], e[1]),
            )

            ids.append(cursor.lastrowid)

        evaluaciones_por_grupo[grupo] = ids

    conn.commit()

    # CALIFICACIONES

    cursor.execute("""
    SELECT id_inscripcion,id_grupo
    FROM inscripcion
    """)

    inscripciones = cursor.fetchall()

    for ins in inscripciones:
        id_ins = ins[0]
        grupo = ins[1]

        evaluaciones = evaluaciones_por_grupo.get(grupo, [])

        for ev in evaluaciones:
            cal = round(random.uniform(50, 100), 2)

            cursor.execute(
                """
            INSERT IGNORE INTO calificacion
            (
                id_inscripcion,
                id_evaluacion,
                calificacion
            )
            VALUES (%s,%s,%s)
            """,
                (id_ins, ev, cal),
            )

    conn.commit()

    print("Modulo control escolar sembrado correctamente")
