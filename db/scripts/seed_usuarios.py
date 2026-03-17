import random
import hashlib
from faker import Faker

fake = Faker("es_MX")

# generar username


def generar_username(nombre, ap_pat):

    base = (nombre[0] + ap_pat).lower()
    numero = random.randint(1, 999)

    return f"{base}{numero}"


# hash fake


def generar_password_hash():

    password = fake.password(length=10)
    hash_obj = hashlib.sha256(password.encode())

    return hash_obj.hexdigest()


# Seeder principal


def seed_usuarios(cursor, conn, total=500):

    # PERSONAS DISPONIBLES

    cursor.execute(
        """
    SELECT id_persona,nombre,apellido_paterno
    FROM persona
    ORDER BY RAND()
    LIMIT %s
    """,
        (total,),
    )

    personas = cursor.fetchall()

    # CREAR USUARIOS

    for p in personas:
        id_persona = p[0]
        nombre = p[1]
        ap_pat = p[2]

        username = generar_username(nombre, ap_pat)
        password_hash = generar_password_hash()

        ultimo_acceso = fake.date_time_between(start_date="-30d", end_date="now")

        cursor.execute(
            """
        INSERT INTO usuario
        (
            id_persona,
            nombre_usuario,
            password_hash,
            ultimo_acceso
        )
        VALUES (%s,%s,%s,%s)
        """,
            (id_persona, username, password_hash, ultimo_acceso),
        )

    conn.commit()

    # ASIGNAR ROLES

    cursor.execute("SELECT id_usuario FROM usuario")
    usuarios = [u[0] for u in cursor.fetchall()]

    cursor.execute("SELECT id_rol FROM rol")
    roles = [r[0] for r in cursor.fetchall()]

    for usuario in usuarios:
        cantidad = random.randint(1, 2)

        roles_asignados = random.sample(roles, cantidad)

        for rol in roles_asignados:
            cursor.execute(
                """
            INSERT INTO usuario_rol
            (id_usuario,id_rol)
            VALUES (%s,%s)
            """,
                (usuario, rol),
            )

    conn.commit()

    # BITACORA

    cursor.execute("SELECT id_modulo FROM modulo")
    modulos = [m[0] for m in cursor.fetchall()]

    acciones = [
        "Inicio de sesión",
        "Creó un registro",
        "Editó información",
        "Eliminó un registro",
        "Consultó información",
        "Actualizó datos",
    ]

    for i in range(2000):
        usuario = random.choice(usuarios)
        modulo = random.choice(modulos)
        accion = random.choice(acciones)

        ip = fake.ipv4()

        cursor.execute(
            """
        INSERT INTO bitacora
        (
            id_usuario,
            id_modulo,
            accion,
            direccion_ip
        )
        VALUES (%s,%s,%s,%s)
        """,
            (usuario, modulo, accion, ip),
        )

    conn.commit()

    print("Modulo usuarios sembrado correctamente")
