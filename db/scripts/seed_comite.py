import random
from faker import Faker

fake = Faker("es_MX")


def seed_comite(cursor, conn):

    # TIPOS DE SOLICITUD

    tipos = [
        "Cambio de carrera",
        "Baja temporal",
        "Baja definitiva",
        "Revalidación de materias",
        "Equivalencia de estudios",
        "Prórroga académica",
        "Revisión de calificación",
        "Solicitud de titulación especial",
    ]

    for t in tipos:
        cursor.execute(
            """
        INSERT INTO tipo_solicitud(nombre_tipo)
        VALUES(%s)
        """,
            (t,),
        )

    conn.commit()

    # PERSONAS DISPONIBLES

    cursor.execute("""
    SELECT id_persona
    FROM persona
    ORDER BY RAND()
    LIMIT 300
    """)

    personas = [p[0] for p in cursor.fetchall()]

    cursor.execute("SELECT id_tipo_solicitud FROM tipo_solicitud")
    tipos_ids = [t[0] for t in cursor.fetchall()]

    # SOLICITUDES

    solicitudes_ids = []

    estados = ["pendiente", "en revisión", "aprobada", "rechazada"]

    for persona in personas:
        cursor.execute(
            """
        INSERT INTO solicitud_comite
        (
            id_persona,
            id_tipo_solicitud,
            descripcion,
            estatus
        )
        VALUES(%s,%s,%s,%s)
        """,
            (
                persona,
                random.choice(tipos_ids),
                fake.text(max_nb_chars=120),
                random.choice(estados),
            ),
        )

        solicitudes_ids.append(cursor.lastrowid)

    conn.commit()

    # SESIONES DE COMITE

    sesiones_ids = []

    for i in range(20):
        fecha = fake.date_between(start_date="-2y", end_date="today")

        cursor.execute(
            """
        INSERT INTO sesion_comite
        (
            fecha,
            descripcion
        )
        VALUES(%s,%s)
        """,
            (fecha, fake.sentence()),
        )

        sesiones_ids.append(cursor.lastrowid)

    conn.commit()

    # RESOLUCIONES

    decisiones = [
        "Solicitud aprobada",
        "Solicitud rechazada",
        "Se requiere información adicional",
        "Solicitud pospuesta para próxima sesión",
    ]

    for solicitud in solicitudes_ids:
        sesion = random.choice(sesiones_ids)

        fecha = fake.date_between(start_date="-1y", end_date="today")

        cursor.execute(
            """
        INSERT INTO resolucion_comite
        (
            id_sesion,
            id_solicitud,
            decision,
            fecha_resolucion
        )
        VALUES(%s,%s,%s,%s)
        """,
            (sesion, solicitud, random.choice(decisiones), fecha),
        )

    conn.commit()

    print("Modulo comité sembrado correctamente")
