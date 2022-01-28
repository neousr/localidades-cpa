#!/usr/bin/php
<?php

if (PHP_SAPI !== 'cli') {
    return false;
}

/**
 * Este archivo se debe ejecutar de forma independiente para capturar
 * todas las localidades por provincia para Argentina en formato csv
 */

error_reporting(E_ALL | E_STRICT);
mb_internal_encoding('UTF-8');

define('DEST_DIR', str_replace('\\', '/', __DIR__) . '/por-provincia-csv-v2/');
define('FORM_DATA', 'action=localidades&localidad=none&calle=&altura=&provincia=');
define('REQUEST_URL', 'https://www.correoargentino.com.ar/sites/all/modules/custom/ca_forms/api/wsFacade.php');

echo "### Iniciando captura.\n";
$time = microtime(true);

$provincias = [
    'A' => 'Salta',
    'B' => 'Buenos Aires',
    'C' => 'Ciudad Autónoma de Buenos Aires',
    'D' => 'San Luis',
    'E' => 'Entre Ríos',
    'F' => 'La Rioja',
    'G' => 'Santiago del Estero',
    'H' => 'Chaco',
    'J' => 'San Juan',
    'K' => 'Catamarca',
    'L' => 'La Pampa',
    'M' => 'Mendoza',
    'N' => 'Misiones',
    'P' => 'Formosa',
    'Q' => 'Neuquén',
    'R' => 'Río Negro',
    'S' => 'Santa Fe',
    'T' => 'Tucumán',
    'U' => 'Chubut',
    'V' => 'Tierra del Fuego',
    'W' => 'Corrientes',
    'X' => 'Córdoba',
    'Y' => 'Jujuy',
    'Z' => 'Santa Cruz'
];

// Removemos el directorio de salida si existe en la ruta DEST_DIR y todos los archivos que contiene
remove_directory(DEST_DIR);

// Creamos el directorio de salida
mkdir(DEST_DIR);

// Opciones cURL
$options = [
    // CURLOPT_HTTPHEADER Un array de campos a configurar para el header HTTP, en el formato: array('Content-type: text/plain', 'Content-length: 100')
    CURLOPT_HTTPHEADER => [
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:85.0) Gecko/20100101 Firefox/85.0",
        "Accept: application/json, text/javascript, */*; q=0.01",
        "Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3",
        "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
        "X-Requested-With: XMLHttpRequest"
    ],
    // true para completar silenciosamente en lo que se refiere a las funciones cURL, equivalente a curl -s (silent mode).
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "", // Todas
    CURLOPT_POST => 1,
    CURLOPT_VERBOSE => 0 // 1 Muestra en detalle lo que esta sucediendo.
];

$headers = ['id_localidad', 'nombre', 'partido', 'municipio', 'cp', 'latitud', 'longitud'];

foreach ($provincias as $char => $provincia) {
    // Evitamos solicitar las localidades de CABA
    if ($char !== 'C') {
        $options[CURLOPT_POSTFIELDS] = FORM_DATA . $char;
        $curl = curl_init(REQUEST_URL);
        curl_setopt_array($curl, $options);
        // Removemos carácteres no imprimibles
        $response = preg_replace('/[[:^print:]]/', '', curl_exec($curl));
        curl_close($curl);

        $data = json_decode($response, true);
        $fp = fopen(DEST_DIR . str_replace(' ', '-', $provincia) . '.csv', 'wb');
        fputcsv($fp, $headers);

        foreach ($data as $campos) {
            fputcsv($fp, $campos);
        }
        fclose($fp);
        echo "### Listo provincia: " . $provincia . "\n";
    }
}

$time = microtime(true) - $time;
echo "### Tiempo total: " . round($time, 3) . " segundos.\n";
echo "### Finalizado el: " . date('j/n/Y H:i') . "\n";

function remove_directory($directory) {
    // Siempre eliminará el directorio en la ruta DEST_DIR y todos los archivos que contiene
    if (file_exists($directory)) {
        $files = scandir($directory);
        if ( count($files) > 2 ) {
            // Removemos todos los archivos que contiene el directorio
            array_map('unlink', glob($directory . '*'));
        }
        // Removemos el directorio
        rmdir($directory);
    }
}