import os
import mysql.connector
import subprocess
from config import DB_CONFIG

# ==========================================
# RUTAS ABSOLUTAS
# ==========================================

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

SCHEMA_FILE = os.path.join(BASE_DIR, "../esquema/SICE.sql")
SEED_FILE = os.path.join(BASE_DIR, "seed_all.py")


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
            table_name = table[0]
            print(f"Eliminando tabla: {table_name}")
            cursor.execute(f"DROP TABLE IF EXISTS `{table_name}`")

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
        print(f"Leyendo archivo SQL:\n{SCHEMA_FILE}")

        with open(SCHEMA_FILE, "r", encoding="utf-8") as f:
            sql = f.read()

        # Ejecutar cada sentencia SQL
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

    try:
        print(f"Ejecutando archivo:\n{SEED_FILE}")

        subprocess.run(
            ["python", SEED_FILE],
            check=True
        )

        print("Seeds ejecutados correctamente")

    except subprocess.CalledProcessError as e:
        print("Error ejecutando seeds:", e)

    except Exception as e:
        print("Error general:", e)


if __name__ == "__main__":

    reset_database()
    create_schema()
    run_seeds()

    print("\nBase de datos completamente reinicializada")