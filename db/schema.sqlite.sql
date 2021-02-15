CREATE TABLE provincia (
    id_provincia INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    nombre VARCHAR(50) NOT NULL,
    codigo_31662 CHAR(4) NOT NULL UNIQUE
);
CREATE TABLE localidad (
    id_localidad INTEGER NOT NULL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    cp SMALLINT NOT NULL,
    id_provincia INTEGER NOT NULL,
    FOREIGN KEY (id_provincia) REFERENCES provincia (id_provincia)
);
CREATE INDEX idx_localidad_id_provincia ON localidad(nombre, id_provincia);