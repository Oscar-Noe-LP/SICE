from faker import Faker
import random
import string

fake = Faker("es_MX")

# Generador de CURP falsa (solo formato correcto)


def generar_curp(nombre, ap_pat, ap_mat, fecha):

    letras = (ap_pat[:1] + ap_mat[:1] + nombre[:1]).upper()

    fecha_str = fecha.strftime("%y%m%d")

    estado = random.choice(
        [
            "AS",
            "BC",
            "BS",
            "CC",
            "CL",
            "CM",
            "CS",
            "CH",
            "DF",
            "DG",
            "GT",
            "GR",
            "HG",
            "JC",
            "MC",
            "MN",
            "MS",
            "NT",
            "NL",
            "OC",
            "PL",
            "QT",
            "QR",
            "SP",
            "SL",
            "SR",
            "TC",
            "TS",
            "TL",
            "VZ",
            "YN",
            "ZS",
        ]
    )

    sexo = random.choice(["H", "M"])

    random_chars = "".join(random.choices(string.ascii_uppercase + string.digits, k=6))

    curp = f"{letras}{fecha_str}{sexo}{estado}{random_chars}"

    return curp[:18]


# Seeder principal


def seed_personas(cursor, conn, total=2000):

    # GENEROS

    generos = ["Masculino", "Femenino", "No especificado"]

    for g in generos:
        cursor.execute(
            """
            INSERT IGNORE INTO genero(nombre_genero)
            VALUES(%s)
            """,
            (g,),
        )

    conn.commit()

    # obtener ids de genero
    cursor.execute("SELECT id_genero FROM genero")
    generos_ids = [g[0] for g in cursor.fetchall()]

    # PERSONAS

    for i in range(total):
        nombre = fake.first_name()
        ap_pat = fake.last_name()
        ap_mat = fake.last_name()

        fecha_nac = fake.date_between(start_date="-60y", end_date="-17y")

        genero = random.choice(generos_ids)

        curp = generar_curp(nombre, ap_pat, ap_mat, fecha_nac)

        cursor.execute(
            """
        INSERT INTO persona
        (
            nombre,
            apellido_paterno,
            apellido_materno,
            curp,
            fecha_nacimiento,
            id_genero
        )
        VALUES (%s,%s,%s,%s,%s,%s)
        """,
            (nombre, ap_pat, ap_mat, curp, fecha_nac, genero),
        )

        id_persona = cursor.lastrowid

        # TELEFONO

        telefono = fake.phone_number()

        cursor.execute(
            """
        INSERT INTO persona_telefono
        (id_persona, telefono)
        VALUES (%s,%s)
        """,
            (id_persona, telefono),
        )

        # CORREO

        correo = fake.email()

        cursor.execute(
            """
        INSERT INTO persona_correo
        (id_persona, correo)
        VALUES (%s,%s)
        """,
            (id_persona, correo),
        )

        # DIRECCION

        direccion = fake.address()

        cursor.execute(
            """
        INSERT INTO persona_direccion
        (id_persona, direccion)
        VALUES (%s,%s)
        """,
            (id_persona, direccion),
        )

        if i % 200 == 0:
            conn.commit()

    conn.commit()

    print(f"{total} personas generadas correctamente")
