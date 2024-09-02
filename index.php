<?php
$apiToken = "7335946518:AAFZOez3RlitHhFBbT1tOWCrlFYcYkT5sU4";
$apiUrl = "https://api.telegram.org/bot$apiToken/";

$content = file_get_contents("php://input");
$update = json_decode($content, true);

$chatId = $update['message']['chat']['id'];
$message = $update['message']['text'];

function sendMessage($chatId, $text) {
    global $apiUrl;
    file_get_contents($apiUrl . "sendMessage?chat_id=$chatId&text=" . urlencode($text));
}

if ($message == "/start") {
    sendMessage($chatId, "Fumando Ando, Activo. | /cmds para ver los comandos.");
} elseif ($message == "/cmds") {
    $commands = "API's RIPCEDUBI:\n/credi {dni}\n/fisca {dni} {sexo}\n/ping\n/me";
    sendMessage($chatId, $commands);
} elseif (strpos($message, "/credi") === 0) {
    $parts = explode(" ", $message);
    if (isset($parts[1])) {
        $dni = $parts[1];
        sendMessage($chatId, "🔍 Buscando información...");
        $response = file_get_contents("https://clientes.credicuotas.com.ar/v1/onboarding/resolvecustomers/$dni");
        $data = json_decode($response, true);
        if (isset($data[0])) {
            $result = "CUIT: " . $data[0]['cuit'] . "\n" .
                      "Nombre Completo: " . $data[0]['nombrecompleto'] . "\n" .
                      "DNI: " . $data[0]['dni'] . "\n" .
                      "Fecha de Nacimiento: " . $data[0]['fechanacimiento'] . "\n" .
                      "Sexo: " . $data[0]['sexo'];
            sendMessage($chatId, $result);
        } else {
            sendMessage($chatId, "No se encontraron resultados para el DNI proporcionado.");
        }
    } else {
        sendMessage($chatId, "Por favor, proporciona un DNI después del comando /credi.");
    }
} elseif (strpos($message, "/fisca") === 0) {
    $parts = explode(" ", $message);
    if (isset($parts[1]) && isset($parts[2])) {
        $dni = $parts[1];
        $sexo = $parts[2];
        sendMessage($chatId, "🔍 Buscando información...");
        $response = file_get_contents("https://fiscalizar.seguridadvial.gob.ar/api/licencias?numeroDocumento=$dni&sexo=$sexo");
        $data = json_decode($response, true);
        if (isset($data[0])) {
            $count = 1;
            foreach ($data as $license) {
                $result = "Licencia $count:\n" .
                          "Nombre: " . $license['nombre'] . " " . $license['apellido'] . "\n" .
                          "DNI: " . $license['numeroDocumento'] . "\n" .
                          "Fecha de Nacimiento: " . $license['fechaNacimiento'] . "\n" .
                          "Fecha de Emisión: " . $license['fechaEmision'] . "\n" .
                          "Fecha de Vencimiento: " . $license['fechaVencimiento'] . "\n" .
                          "Provincia: " . $license['provincia'] . "\n" .
                          "Localidad: " . $license['localidad'] . "\n" .
                          "Clases: " . $license['clasesCodigos'];
                sendMessage($chatId, $result);
                $count++;
            }
        } else {
            sendMessage($chatId, "No se encontraron licencias para el DNI y sexo proporcionados.");
        }
    } elseif (isset($parts[1]) && !isset($parts[2])) {
        sendMessage($chatId, "Por favor, proporciona también el sexo (M/F) después del DNI.");
    } elseif (!isset($parts[1]) && isset($parts[2])) {
        sendMessage($chatId, "Por favor, proporciona también el DNI después del comando.");
    } else {
        sendMessage($chatId, "Formato incorrecto. Usa: /fisca {dni} {sexo}");
    }
} else {
    sendMessage($chatId, "Comando no reconocido.");
}
