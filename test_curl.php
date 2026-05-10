<?php
$url = "http://localhost/trigestion/controllers/VentaController.php?accion=finalizar_compra";
$data = http_build_query(['notas' => 'Prueba cURL']);

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => $data,
        'ignore_errors' => true
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo "Result:\n" . $result;
?>
