<?php

error_reporting(E_ALL | E_STRICT);
mb_internal_encoding('UTF-8');

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

foreach ($provincias as $key => $value) {
    echo 'Procesando provincia: ' . $provincias[$key] . '<br>';
    $curlData = 'action=localidades&localidad=none&calle=&altura=&provincia=' . $key;
    // https://www.php.net/manual/es/function.curl-setopt.php
    $options = [
        CURLOPT_HTTPHEADER => [
            'Accept: application/json, text/javascript, */*; q=0.01'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_POST => 1,
        // Si pasamos un array a CURLOPT_POSTFIELDS codificará los datos como multipart/form-data, 
        // pero si pasamos una cadena URL-encoded codificará los datos como application/x-www-form-urlencoded. 
        CURLOPT_POSTFIELDS => $curlData,
        CURLOPT_VERBOSE => 1
    ];
    // CURLOPT_HTTPHEADER Un array de campos a configurar para el header HTTP, en el formato: array('Content-type: text/plain', 'Content-length: 100')
    $url = 'https://www.correoargentino.com.ar/sites/all/modules/custom/ca_forms/api/wsFacade.php';
    $curl = curl_init($url);
    curl_setopt_array($curl, $options);
    $data = curl_exec($curl);
    curl_close($curl);

    $handle = fopen('por-provincia/' . $provincias[$key] . '.json', 'w');
    fwrite($handle, $data);
    fclose($handle);
}

$time = microtime(true) - $time;
echo "<p>Tiempo total de ejecución: " . round($time, 3) . " segundos\n";