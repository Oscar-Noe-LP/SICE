import mysql.connector
import subprocess
from config import DB_CONFIG


SCHEMA_FILE = "../esquema/SICE.sql"


def reset_database():

    print("Conectando a la base de datos...")

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    try:
        print("Desactivando foreign keys...")
        cursor.execute("SET FOREIGN_KEY_CHECKS = 0")

        print("Eliminando tablas existentes...")

        cursor.execute("SHOW TABLES")
        tables = cursor.fetchall()

        for table in tables:
            cursor.execute(f"DROP TABLE IF EXISTS {table[0]}")

        conn.commit()

        print("Reactivando foreign keys...")
        cursor.execute("SET FOREIGN_KEY_CHECKS = 1")

        print("Base limpiada correctamente")

    except Exception as e:
        print("Error limpiando la base:", e)

    finally:
        cursor.close()
        conn.close()


def create_schema():

    print("Creando esquema de la base de datos...")

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    try:
        with open(SCHEMA_FILE, "r", encoding="utf-8") as f:
            sql = f.read()

        for statement in sql.split(";"):
            stmt = statement.strip()

            if stmt:
                cursor.execute(stmt)

        conn.commit()

        print("Esquema creado correctamente")

    except Exception as e:
        print("Error creando esquema:", e)

    finally:
        cursor.close()
        conn.close()


def run_seeds():

    print("Ejecutando seeds...")

    subprocess.run(["python", "seed_all.py"])


if __name__ == "__main__":
    reset_database()
    create_schema()
    run_seeds()

    print("\nBase de datos completamente reinicializada")
