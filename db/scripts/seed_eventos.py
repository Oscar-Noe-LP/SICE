import random
from faker import Faker

fake = Faker("es_MX")


def seed_eventos(cursor, conn):

    # TIPOS DE EVENTO

    tipos = [
        "Conferencia",
        "Taller",
        "Hackathon",
        "Congreso",
        "Evento cultural",
        "Competencia académica",
        "Seminario",
        "Feria tecnológica",
    ]

    for t in tipos:
        cursor.execute(
            """
        INSERT INTO tipo_evento(nombre_tipo)
        VALUES(%s)
        """,
            (t,),
        )

    conn.commit()

    # TIPOS DISPONIBLES

    cursor.execute("SELECT id_tipo_evento FROM tipo_evento")
    tipos_ids = [t[0] for t in cursor.fetchall()]

    # EVENTOS

    eventos_ids = []

    for i in range(50):
        nombre = fake.sentence(nb_words=4)

        fecha = fake.date_between(start_date="-2y", end_date="+6m")

        cursor.execute(
            """
        INSERT INTO evento
        (
            nombre_evento,
            id_tipo_evento,
            fecha,
            descripcion
        )
        VALUES(%s,%s,%s,%s)
        """,
            (nombre, random.choice(tipos_ids), fecha, fake.text(max_nb_chars=120)),
        )

        eventos_ids.append(cursor.lastrowid)

    conn.commit()

    # ALUMNOS DISPONIBLES

    cursor.execute("SELECT id_alumno FROM alumno")
    alumnos = [a[0] for a in cursor.fetchall()]

    # PARTICIPACIONES

    for evento in eventos_ids:
        participantes = random.sample(alumnos, random.randint(20, 80))

        for alumno in participantes:
            cursor.execute(
                """
            INSERT INTO participacion_evento
            (
                id_evento,
                id_alumno,
                constancia_emitida
            )
            VALUES(%s,%s,%s)
            """,
                (evento, alumno, random.choice([True, False])),
            )

    conn.commit()

    print("Modulo eventos sembrado correctamente")
