# Tutorial: Cómo correr las seeds y migraciones en Railway

> **Objetivo:** Poblar la base de datos de Railway con la estructura correcta y datos de prueba.  
> Este tutorial cubre **dos métodos**:
> - **Método A** — Usando el CLI de Railway (recomendado para migraciones)
> - **Método B** — Usando los scripts de Python localmente apuntando a Railway

---

## ¿Qué pasa si no corres las migraciones primero?

Las seeds y scripts de Python dependen de que ciertas columnas existan (por ejemplo `clave` en `rol`, `modulo`, `permiso`). Si alguien corrió partes del SQL a mano en Railway y otros no, la BD queda a medias y los scripts fallan con errores raros. **Siempre migrar primero.**

---

## MÉTODO A — Laravel Artisan en Railway (migraciones + seeds de Laravel)

### Paso 1: Obtener las credenciales de Railway

1. Entra a [railway.app](https://railway.app) e inicia sesión
2. Abre tu proyecto → haz clic en el servicio de **MySQL**
3. Ve a la pestaña **"Variables"** (o "Connect")
4. Copia estos valores:

   | Variable       | Ejemplo de valor                |
   |----------------|---------------------------------|
   | `MYSQLHOST`    | `containers-us-west-XX.railway.app` |
   | `MYSQLPORT`    | `6543`                          |
   | `MYSQLDATABASE`| `railway`                       |
   | `MYSQLUSER`    | `root`                          |
   | `MYSQLPASSWORD`| `AbCdEfGhIj12345`               |

### Paso 2: Configurar el `.env` del Backend en Railway

1. En Railway, ve al servicio de tu **Backend (PHP/Laravel)**
2. Pestaña **"Variables"** → agrega o actualiza:

```
DB_CONNECTION=mysql
DB_HOST=containers-us-west-XX.railway.app   ← el MYSQLHOST
DB_PORT=6543                                ← el MYSQLPORT
DB_DATABASE=railway                         ← el MYSQLDATABASE
DB_USERNAME=root                            ← el MYSQLUSER
DB_PASSWORD=AbCdEfGhIj12345               ← el MYSQLPASSWORD
```

> **Importante:** Estas variables ya deben estar en Railway si el backend está funcionando. Solo verifica que sean correctas.

### Paso 3: Correr las migraciones vía Railway CLI

#### Opción 3a — Desde la terminal de Railway (más fácil)

1. En Railway, abre el servicio de Backend
2. Haz clic en **"Shell"** (ícono de terminal en la parte superior)
3. Escribe:

```bash
php artisan migrate --force
```

4. Espera a que termine. Debes ver algo así:
```
Running migrations...
  2024_01_01_000001_create_rol_table ............. 247ms DONE
  2026_05_09_000001_add_fields_to_alumno_table ... 89ms  DONE
  2026_05_10_100000_add_clave_to_seguridad_tables  312ms DONE
```

5. Si ya estaba migrado antes y solo quieres las nuevas:
```bash
php artisan migrate --force --pretend   # ver qué correría sin ejecutar
php artisan migrate --force             # ejecutar las nuevas
```

#### Opción 3b — Desde tu computadora con el CLI de Railway

1. Instala el CLI:
```bash
npm install -g @railway/cli
```

2. Inicia sesión:
```bash
railway login
```

3. Entra a la carpeta del backend del proyecto:
```bash
cd Desktop/SICE-nuevo/Backend
```

4. Vincula con tu proyecto de Railway:
```bash
railway link
```
_(te pedirá elegir el proyecto y el entorno)_

5. Corre las migraciones:
```bash
railway run php artisan migrate --force
```

### Paso 4: Correr los seeders de Laravel (datos catálogo base)

En la misma terminal (Railway Shell o CLI local):

```bash
php artisan db:seed --force
```

Esto ejecuta:
- `DatosInicialesSeeder` → géneros, nivel carrera, departamentos, puestos, módulos, permisos, periodos
- `RolesSeeder` → roles del sistema

> Si solo quieres correr un seeder específico:
> ```bash
> php artisan db:seed --class=DatosInicialesSeeder --force
> ```

---

## MÉTODO B — Scripts de Python apuntando a Railway

Los scripts de Python (`scripts/`) insertan datos de prueba masivos (3000 personas, 500 usuarios, etc.).

### Requisitos previos

- Python 3.9 o superior
- El **Método A ya fue ejecutado** (migraciones corridas)
- Railway tiene habilitado el **acceso TCP público** al MySQL

### Paso 1: Habilitar acceso público al MySQL en Railway

1. En Railway → servicio MySQL → pestaña **"Settings"**
2. Busca **"Public Networking"** → habilitar (toggle ON)
3. Copia el **host público** y **puerto público** que aparecen (son diferentes a los internos)

### Paso 2: Crear entorno virtual de Python

```bash
cd Desktop/SICE-nuevo/scripts

# Windows
python -m venv venv
venv\Scripts\activate

# Mac/Linux
python3 -m venv venv
source venv/bin/activate
```

### Paso 3: Instalar dependencias

```bash
pip install mysql-connector-python faker
```

### Paso 4: Configurar las credenciales de Railway en el `.env` del Backend

Los scripts leen las credenciales **de `Backend/.env` automáticamente** (via `config.py`).

Edita `Backend/.env` temporalmente con los datos **públicos** de Railway:

```env
DB_HOST=containers-us-west-XX.railway.app   ← host PÚBLICO
DB_PORT=6543                                ← puerto PÚBLICO
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=AbCdEfGhIj12345
```

> **Nota:** Después de terminar, regresa los valores a tu BD local si usas el backend en local.

### Paso 5: Correr los scripts

```bash
# Desde la carpeta scripts/
cd Desktop/SICE-nuevo/scripts

# Correr todo (puede tardar 2-5 minutos)
python seed_all.py

# O módulo por módulo (si algo falla, así ubicas cuál)
python -c "from seed_seguridad import seed_seguridad; ..."  
# mejor usar seed_all.py directamente
```

Verás en consola:
```
Conectando a la base de datos...

--- MODULO 1: SEGURIDAD ---
Modulo Seguridad sembrado correctamente

--- MODULO 2: PERSONAS ---
...
Base de datos poblada correctamente
Conexion cerrada
```

---

## Errores comunes y cómo resolverlos

### ❌ "Table 'rol' doesn't have column 'clave'"
**Causa:** Las migraciones no se corrieron, o `correcionid.sql` no se ejecutó.  
**Solución:** Corre primero el Método A (`php artisan migrate --force`).

### ❌ "Access denied for user 'root'@..." o "Can't connect"
**Causa:** Las credenciales en `.env` son las internas de Railway, no las públicas.  
**Solución:** Usa el host/puerto **público** (el de "Public Networking"), no el interno.

### ❌ "1062 Duplicate entry" en las seeds de Laravel
**Causa:** Ya se corrieron antes. Las seeds de Laravel usan `updateOrInsert`, es seguro volver a correr.  
**Solución:** Ignorar el warning, o revisar si ya están los datos.

### ❌ "Foreign key constraint fails" en los scripts de Python
**Causa:** Se está tratando de insertar datos en orden incorrecto (ej. usuarios antes que personas).  
**Solución:** Usa siempre `seed_all.py` (respeta el orden correcto).

### ❌ Las migraciones ya corrieron pero algo está roto
**Causa:** Alguien corrió SQL manual en Railway y dejó columnas duplicadas o datos inconsistentes.  
**Solución:** 
```bash
php artisan migrate:status   # ver cuáles ya corrieron
php artisan migrate --force  # solo corre las pendientes
```

---

## Orden correcto de todo el proceso (resumen)

```
1. php artisan migrate --force          ← estructura de tablas
2. php artisan db:seed --force          ← catálogos base (géneros, roles, módulos...)
3. python seed_all.py                   ← datos de prueba masivos (opcional, solo en dev)
```

---

## ¿Cómo saber si todo quedó bien?

En la Shell de Railway o con un cliente MySQL (TablePlus, DBeaver, MySQL Workbench):

```sql
-- Verificar módulos con clave asignada
SELECT id_modulo, nombre_modulo, clave FROM modulo;

-- Verificar roles
SELECT id_rol, nombre_rol, clave FROM rol;

-- Verificar permisos
SELECT id_permiso, nombre_permiso, clave FROM permiso;

-- Contar personas insertadas
SELECT COUNT(*) FROM persona;
```

Si `clave` tiene valores como `ADMIN`, `DOCENTE`, `SEGURIDAD`... todo está correcto. ✅

---

## Conectar con TablePlus / DBeaver / Workbench (para ver la BD visualmente)

1. Crea una nueva conexión **MySQL**
2. Usa los datos **públicos** de Railway:
   - Host: `containers-us-west-XX.railway.app`
   - Port: `6543` (el público)
   - Database: `railway`
   - User: `root`
   - Password: _(el de Railway)_
3. Conecta → puedes explorar tablas, correr SQL, etc.
