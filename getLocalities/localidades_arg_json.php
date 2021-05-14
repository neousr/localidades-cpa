<?php

/**
 * Este archivo se debe ejecutar de forma independiente para capturar
 * todas las localidades por provincia para Argentina en formato json
 */

error_reporting(E_ALL | E_STRICT);
mb_internal_encoding('UTF-8');
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
    'V' => 'Tierra del Fuego, Antártida e Islas del Atlántico Sur',
    'W' => 'Corrientes',
    'X' => 'Córdoba',
    'Y' => 'Jujuy',
    'Z' => 'Santa Cruz'
];

define('DOCUMENT_ROOT', str_replace('\\', '/', dirname(__FILE__)) . '/por-provincia-json-v2/');

// Siempre eliminará el directorio por-provincia-json y todos los archivos json que contiene
if (file_exists(DOCUMENT_ROOT)) {
    $files = scandir(DOCUMENT_ROOT);
    if ( count($files) > 2 ) {
        // Removemos todos los .json
        array_map('unlink', glob(DOCUMENT_ROOT . '*.json'));
    }
    // Removemos el directorio
    rmdir(DOCUMENT_ROOT);
}

// Creamos el directorio
mkdir(DOCUMENT_ROOT);

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
    CURLOPT_ENCODING => "",
    CURLOPT_POST => 1,
    CURLOPT_VERBOSE => 0 // 1 Muestra en detalle lo que esta sucediendo.
];

foreach ($provincias as $key => $value) {
    if ($key !== 'C') {
        $response = '{"iso_31662":"AR-' . $key . '","provincia":"'. $value . '","localidades":';
        $options[CURLOPT_POSTFIELDS] = 'action=localidades&localidad=none&calle=&altura=&provincia=' . $key;
        $curl = curl_init('https://www.correoargentino.com.ar/sites/all/modules/custom/ca_forms/api/wsFacade.php');
        curl_setopt_array($curl, $options);
        // $regex = '/[\x00-\x1F\x80-\xFF]/'; // '/[^\x20-\x7E]/';
        $response .= preg_replace('/[[:^print:]]/', '', curl_exec($curl)) . '}';
        curl_close($curl);
    
        $fp = fopen(DOCUMENT_ROOT . $value . '.json', 'w');
        fwrite($fp, $response);
        fclose($fp);
        echo "### Listo provincia: " . $value . "\n";
    }
}

$time = microtime(true) - $time;
echo "Tiempo total de ejecución: " . round($time, 3) . " segundos.\n";