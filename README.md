# IUVADE-Prueba-Tecnica

Desarrollador: **Sebastian Chacon**

Este repositorio contiene la solución a los **dos ejercicios CRUD** solicitados en la prueba técnica:

- **Ejercicio 1:** Módulo de Trabajadores  
- **Ejercicio 2:** Módulo de Ventas y Detalles 

Tecnologías usadas:

- **Apache 2**
- **PHP 8**
- **PostgreSQL**
- **ExtJS 4** (proveído en el archivo ejemplo.zip)
- **HTML / JS**
- **PDO para conexión a PostgreSQL**

---

## 🗄️ Instalación de la base de datos

En el directorio `/SQL` se incluye el archivo:
SQL/prueba_tecnica.sql
Este script crea:

- Esquema `prueba`
- Tabla `prueba.trabajador`
- Tabla `prueba.venta`
- Tabla `prueba.venta_detalle`
- Trigger para calcular `v_d_tot = v_d_can * v_d_uni`

### Ejecutar desde TablePlus

1. Abrir conexión PostgreSQL  
2. `File > Import > From SQL`  
3. Seleccionar `prueba_tecnica.sql`

---
## Estructura del proyecto

IUVADE-Prueba-Tecnica

    │
    ├── Ejercicio1/       (CRUD de trabajadores)
    Ejercicio2/       (Ventas + detalles)
    ├── extjs/            (librerías proporcionadas)
    ├── resources/        
    ├── SQL/              (scripts de base de datos)
    └── README.md