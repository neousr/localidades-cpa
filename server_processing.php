<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit;
}

if (!preg_match('/^[ABCDEFGHJKLMNPQRSTUVWXYZ]{1}$/', $_POST['provincia'])) {
   exit;
}

$curlData = 'action=localidades&localidad=none&calle=&altura=&provincia=' . $_POST['provincia'];

// https://www.php.net/manual/es/function.curl-setopt.php
$options = [
    // CURLOPT_HTTPHEADER Un array de campos a configurar para el header HTTP, en el formato: array('Content-type: text/plain', 'Content-length: 100')
    CURLOPT_HTTPHEADER => [
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:85.0) Gecko/20100101 Firefox/85.0",
        "Accept: application/json, text/javascript, */*; q=0.01",
        "Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3",
        "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
        "X-Requested-With: XMLHttpRequest"
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "", // Todas
    CURLOPT_POST => 1,
    // Si pasamos un array a CURLOPT_POSTFIELDS codificará los datos como multipart/form-data, 
    // pero si pasamos una cadena URL-encoded codificará los datos como application/x-www-form-urlencoded. 
    CURLOPT_POSTFIELDS => $curlData,
    CURLOPT_VERBOSE => 1 // Muestra en detalle lo que esta sucediendo.
];

$curl = curl_init('https://www.correoargentino.com.ar/sites/all/modules/custom/ca_forms/api/wsFacade.php');
curl_setopt_array($curl, $options);
// https://alvinalexander.com/php/how-to-remove-non-printable-characters-in-string-regex/
// $regex = '/[\x00-\x1F\x80-\xFF]/'; // otra alternativa
$response = preg_replace('/[[:^print:]]/', '', curl_exec($curl));
curl_close($curl);

echo $response;