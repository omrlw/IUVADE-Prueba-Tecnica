# IUVADE - Prueba Técnica

**Desarrollador:** Sebastian Chacón

Este repositorio contiene la solución a los **dos módulos CRUD**
solicitados en la prueba técnica de IUVADE:

-   **Ejercicio 1:** Gestión de Trabajadores
-   **Ejercicio 2:** Módulo de Ventas + Detalles

------------------------------------------------------------------------

## 🛠️ Tecnologías Utilizadas

-   **Apache 2**
-   **PHP 8**
-   **PostgreSQL**
-   **ExtJS 4** (incluido en `ejemplo.zip`)
-   **HTML + JavaScript**
-   **PDO (PHP) para conexión a PostgreSQL**

------------------------------------------------------------------------

## 📦 Cómo ejecutar el proyecto

### Clonar el repositorio

    git clone https://github.com/omrlw/IUVADE-Prueba-Tecnica.git
    cd IUVADE-Prueba-Tecnica

### Iniciar PostgreSQL

#### Windows

1.  Abre **Servicios**
2.  Busca `postgresql-x.x`
3.  Clic derecho → **Iniciar**

#### Linux

    sudo systemctl start postgresql

#### MacOS (Homebrew)

    brew services start postgresql
------------------------------------------------------------------------
### Crear usuario y base de datos

Ingresa a PostgreSQL:

    psql -U postgres "USER"

Ejecuta:

    CREATE USER sebas WITH PASSWORD '12345';
    ALTER USER sebas WITH SUPERUSER;
    CREATE DATABASE crud_db OWNER sebas;
    \q

------------------------------------------------------------------------

### Cargar el esquema (tablas + trigger)

El archivo está en:\
`SQL/prueba_tecnica.sql`

Este script crea esquema, tablas y trigger.

#### TablePlus
    File > Import > From SQL\prueba_tecnica.sql
#### Terminal

    psql -U sebas -h localhost -d crud_db -f SQL/prueba_tecnica.sql

------------------------------------------------------------------------

### Conectar en TablePlus (opcional)

-   **Host:** localhost
-   **User:** sebas
-   **Database:** crud_db

------------------------------------------------------------------------

### Iniciar Apache + PHP

#### Windows (XAMPP / WAMP)

Colocar el proyecto en:

    htdocs/IUVADE-Prueba-Tecnica/

#### Linux

    sudo systemctl start apache2
    sudo systemctl restart apache2

#### MacOS

    brew services start httpd

------------------------------------------------------------------------

### Abrir el proyecto 🚀

#### Ejercicio 1

    http://localhost:8080/IUVADE-Prueba-Tecnica/Ejercicio1/

#### Ejercicio 2

    http://localhost:8080/IUVADE-Prueba-Tecnica/Ejercicio2/

------------------------------------------------------------------------
### Estructura del proyecto

IUVADE-Prueba-Tecnica

    │
    ├── Ejercicio1/       (CRUD de trabajadores)
    Ejercicio2/       (Ventas + detalles)
    ├── extjs/            (librerías proporcionadas)
    ├── resources/        
    ├── SQL/              (scripts de base de datos)
    └── README.md