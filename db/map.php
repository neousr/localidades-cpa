<?php
/**
 * Mapea los archivos json a la base de datos SQlite (archivo db.db)
 */
$time = microtime(true);
require_once 'Db.php';
$provincias = [
    1 => 'Buenos Aires',
    2 => 'Catamarca',
    3 => 'Chaco',
    4 => 'Chubut',
    5 => 'Ciudad Autónoma de Buenos Aires',
    6 => 'Córdoba',
    7 => 'Corrientes',
    8 => 'Entre Ríos',
    9 => 'Formosa',
    10 => 'Jujuy',
    11 => 'La Pampa',
    12 => 'La Rioja',
    13 => 'Mendoza',
    14 => 'Misiones',
    15 => 'Neuquén',
    16 => 'Río Negro',
    17 => 'Salta',
    18 => 'San Juan',
    19 => 'San Luis',
    20 => 'Santa Cruz',
    21 => 'Santa Fe',
    22 => 'Santiago del Estero',
    23 => 'Tierra del Fuego, Antártida e Islas del Atlántico Sur',
    24 => 'Tucumán'
];

foreach ($provincias as $id_provincia => $provincia) {
    $json = file_get_contents('../por-provincia-json/' . $provincia . '.json');
    $jsonData = json_decode($json, true);
    try {
        $conn = Db::getInstance();
        // begin the transaction
        $conn->beginTransaction();
        foreach ($jsonData['localidades'] as $value) {
            $id = $value['id'];
            $nombre = $value['nombre'];
            $cp = $value['cp'];
            $conn->exec("INSERT INTO localidad (id_localidad, nombre, cp, id_provincia) 
            VALUES ('$id', '$nombre', '$cp', '$id_provincia');");
        }
        // commit the transaction
        $conn->commit();
        echo "### Inserting " . $provincia . " localities\n";
    } catch (PDOException $e) {
        // roll back the transaction if something failed
        $conn->rollback();
        echo "### Error: " . $e->getMessage();
    }
}
$conn = null;
$time = microtime(true) - $time;
echo "### Complete\n";
echo "### Execution time: " . round($time, 3) . " seconds.";