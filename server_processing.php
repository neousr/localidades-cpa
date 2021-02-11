<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit;
}

$regex = '/^[ABCDEFGHJKLMNPQRSTUVWXYZ]{1}$/';
if (!preg_match($regex, $_POST['provincia'])) {
   exit;
}

$provincia = $_POST['provincia'];

$curlData = 'action=localidades&localidad=none&calle=&altura=&provincia=' . $provincia;

// https://www.php.net/manual/es/function.curl-setopt.php
$options = [
    // CURLOPT_HTTPHEADER Un array de campos a configurar para el header HTTP, en el formato: array('Content-type: text/plain', 'Content-length: 100')
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

$url = 'https://www.correoargentino.com.ar/sites/all/modules/custom/ca_forms/api/wsFacade.php';
$curl = curl_init($url);
curl_setopt_array($curl, $options);
$response = curl_exec($curl);
curl_close($curl);

echo $response;