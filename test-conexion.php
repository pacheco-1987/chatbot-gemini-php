<?php
/**
 * Script de prueba oficial para Gemini API (Formato AQ. compatible)
 * Ejecútalo desde tu consola: php test-conexion.php
 */

require_once __DIR__ . '/config.php';

echo "🤖 Iniciando prueba de conexión con la credencial unificada Gemini...\n";

// 1. Estructura el prompt mínimo que exige Google
$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Hola Gemini, responde únicamente con la frase: 'Conexión exitosa desde tu propio Backend PHP'."]
            ]
        ]
    ]
];

// 2. Definir URL apuntando al modelo recomendado (Gemini 2.5 Flash)
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=" . GEMINI_API_KEY;

// 3. Inicializar cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

echo "📡 Enviando petición segura a los servidores de Google...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Control de errores de red
if (curl_errno($ch)) {
    echo "❌ Error de cURL: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit;
}

curl_close($ch);

// 4. Procesar la respuesta del servidor
if ($httpCode === 200) {
    $resultado = json_decode($response, true);
    
    // Navegar de forma segura por el JSON nativo de Google
    $textoRespuesta = $resultado['candidates'][0]['content']['parts'][0]['text'] ?? null;
    
    if ($textoRespuesta) {
        echo "\n✅ ¡CONEXIÓN ESTABLECIDA CON ÉXITO!\n";
        echo "💬 Respuesta de la IA: " . trim($textoRespuesta) . "\n";
    } else {
        echo "⚠️ Se conectó, pero el formato de respuesta requiere revisión técnica.\n";
        print_r($resultado);
    }
} else {
    echo "❌ Error al conectar. Código HTTP: " . $httpCode . "\n";
    echo "📄 Detalle devuelto por Google: " . $response . "\n";
}