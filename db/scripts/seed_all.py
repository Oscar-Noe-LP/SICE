import mysql.connector
from config import DB_CONFIG

from seed_seguridad import seed_seguridad
from seed_personas import seed_personas
from seed_usuarios import seed_usuarios
from seed_rh import seed_rh
from seed_academico import seed_academico
from seed_controlescolar import seed_control_escolar
from seed_eventos import seed_eventos
from seed_comite import seed_comite


def main():

    print("Conectando a la base de datos...")

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    try:
        print("\n--- MODULO 1: SEGURIDAD ---")
        seed_seguridad(cursor, conn)

        print("\n--- MODULO 2: PERSONAS ---")
        seed_personas(cursor, conn, 3000)

        print("\n--- MODULO 3: USUARIOS ---")
        seed_usuarios(cursor, conn, 500)

        print("\n--- MODULO 4: RECURSOS HUMANOS ---")
        seed_rh(cursor, conn, 200)

        print("\n--- MODULO 5: GESTION ACADEMICA ---")
        seed_academico(cursor, conn)

        print("\n--- MODULO 6: CONTROL ESCOLAR ---")
        seed_control_escolar(cursor, conn, 1500)

        print("\n--- MODULO 7: EVENTOS ---")
        seed_eventos(cursor, conn)

        print("\n--- MODULO 8: COMITE ---")
        seed_comite(cursor, conn)

        print("\nBase de datos poblada correctamente")

    except Exception as e:
        print("\nError durante el seeding:")
        print(e)

    finally:
        cursor.close()
        conn.close()
        print("Conexion cerrada")


if __name__ == "__main__":
    main()
