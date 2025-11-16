-- ============================================
-- IUVADE - Prueba Técnica
-- Script de creación de esquema y tablas
-- Autor: Sebastian Chacón
-- ============================================

-- Crear esquema
CREATE SCHEMA IF NOT EXISTS prueba;

-- --------------------------------------------
-- TABLA: trabajador  (Ejercicio 1)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS prueba.trabajador(
    tra_ide SERIAL PRIMARY KEY,
    tra_cod INTEGER DEFAULT 0,
    tra_nom VARCHAR(200) DEFAULT '',
    tra_pat VARCHAR(200) DEFAULT '',
    tra_mat VARCHAR(200) DEFAULT '',
    est_ado INTEGER DEFAULT 1
);

-- --------------------------------------------
-- TABLA: venta (Ejercicio 2)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS prueba.venta(
    ven_ide SERIAL PRIMARY KEY,
    ven_ser VARCHAR(5) DEFAULT '',
    ven_num VARCHAR(100) DEFAULT '',
    ven_cli TEXT DEFAULT '',
    ven_mon NUMERIC(14,2)
);

-- --------------------------------------------
-- TABLA: venta_detalle (Ejercicio 2)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS prueba.venta_detalle(
    v_d_ide SERIAL PRIMARY KEY,
    ven_ide INTEGER REFERENCES prueba.venta(ven_ide),
    v_d_pro TEXT DEFAULT '',
    v_d_uni NUMERIC(14,2) DEFAULT 0.00,
    v_d_can NUMERIC(14,2) DEFAULT 0.00,
    v_d_tot NUMERIC(14,2) DEFAULT 0.00,
    est_ado INTEGER DEFAULT 1
);

-- --------------------------------------------
-- TRIGGER opcional (Ejercicio 2)
-- Calcula v_d_tot = cantidad * unitario
-- --------------------------------------------
CREATE OR REPLACE FUNCTION calcular_total_detalle()
RETURNS TRIGGER AS $$
BEGIN
    NEW.v_d_tot := NEW.v_d_can * NEW.v_d_uni;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_calcular_total ON prueba.venta_detalle;

CREATE TRIGGER trg_calcular_total
BEFORE INSERT OR UPDATE ON prueba.venta_detalle
FOR EACH ROW
EXECUTE FUNCTION calcular_total_detalle();