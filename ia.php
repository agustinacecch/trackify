<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

// 1. Leer datos
$data = json_decode(file_get_contents("php://input"), true);

// 2. Obtener pregunta
$pregunta = isset($data["pregunta"]) ? $data["pregunta"] : "";

// 3. Leer .env
$env = parse_ini_file(__DIR__ . "/.env");

if (!$env) {
    echo json_encode(["respuesta" => "No se pudo leer .env"]);
    exit;
}

// 4. API KEY
$apiKey = isset($env["GROQ_API_KEY"]) ? $env["GROQ_API_KEY"] : "";

if (!$apiKey) {
    echo json_encode(["respuesta" => "Falta la API KEY"]);
    exit;
}

// 5. Endpoint
$url = "https://api.groq.com/openai/v1/chat/completions";

// 6. Modelo
$model = "llama-3.1-8b-instant";

// 7. Body
$body = [
    "model" => $model,
    "messages" => [
        [
            "role" => "system",
            "content" => "Sos un asesor financiero dentro de Trackify.

            Reglas:
            - Respondé SIEMPRE de forma corta y concisa (máximo 3 líneas).
            - No saludes.
            - No des introducciones ni cierres.
            - Andá directo al punto.
            - Usá lenguaje simple.
            - Si das consejos, que sean como máximo 3.
            - Podés usar **negrita** para resaltar ideas clave.

            Ejemplo:

            Reducí gastos innecesarios.
            Definí un monto fijo de ahorro.
            La clave es la **constancia**.

            Nunca des respuestas largas."
        ],
        [
            "role" => "user",
            "content" => $pregunta
        ]
    ]
];

// 8. cURL
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $apiKey
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

// fixes importantes
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);

// Error cURL
if (curl_errno($ch)) {
    echo json_encode([
        "respuesta" => "Error cURL: " . curl_error($ch)
    ]);
    exit;
}

// Código HTTP
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// Error HTTP
if ($httpCode !== 200) {
    echo json_encode([
        "respuesta" => "Error HTTP: " . $httpCode . " | " . $response
    ]);
    exit;
}

// Parsear respuesta
$result = json_decode($response, true);

// Extraer texto
$respuesta = isset($result["choices"][0]["message"]["content"]) 
    ? $result["choices"][0]["message"]["content"] 
    : "Sin respuesta";

// Responder al frontend
echo json_encode([
    "respuesta" => $respuesta
]);