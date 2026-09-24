<?php
/**
 * Web Service Avanzado para Gemini API
 * Soporta: Historial (Ventana Deslizante), Multimodalidad (Imágenes Base64) y Retry Logic.
 */
// Iniciar sesión antes de cualquier salida de texto para almacenar el historial
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config.php';

// Inicializar el contenedor del historial en la sesión si no existe
if (!isset($_SESSION['chat_history'])) {$_SESSION['chat_history'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {$mensajeUsuario = $_POST['mensaje'] ?? '';$tieneImagen = isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK;

    if (empty(trim($mensajeUsuario)) && !$tieneImagen) {
        echo json_encode(['status' => 'error', 'message' => 'Debes enviar un mensaje o una imagen.']);
        exit;
    }

    // 1. Estructurar la parte del texto que envió el usuario
    $nuevasPartes = [];
    if (!empty(trim($mensajeUsuario))) {
        $nuevasPartes[] = ["text" => $mensajeUsuario];
    }

    // 2. Si el usuario subió una imagen, procesarla a Base64 e incrustarla
    if ($tieneImagen) {
        $rutaTemporal = $_FILES['imagen']['tmp_name'];
        $tipoMime = $_FILES['imagen']['type'];
        
        // Validar que realmente sea una imagen
        if (strpos($tipoMime, 'image/') === 0) {
            $contenidoBinario = file_get_contents($rutaTemporal);
            $base64Data = base64_encode($contenidoBinario);
            
            // Estructura oficial inlineData que exige Gemini para archivos multimedia
            $nuevasPartes[] = [
                "inlineData" => [
                    "mimeType" => $tipoMime,
                    "data" => $base64Data
                ]
            ];
        }
    }

    // 3. Agregar el nuevo turno del usuario al historial persistente de la sesión
    $_SESSION['chat_history'][] = [
        "role" => "user",
        "parts" => $nuevasPartes
    ];

    // --- IMPLEMENTACIÓN DE LA VENTANA DESLIZANTE ---
    // Mantenemos únicamente los últimos 10 turnos (mensajes) para no inflar costos ni saturar el contexto
    $maxMensajesContexto = 10;
    if (count($_SESSION['chat_history']) > $maxMensajesContexto) {
        // Corta el arreglo conservando solo los últimos N elementos
        $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], - $maxMensajesContexto);
    }

    // 4. Preparar el payload final con todo el historial acumulado
    $payload = [
        "contents" => $_SESSION['chat_history']
    ];

    // Endpoint oficial unificado y estable
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=" . GEMINI_API_KEY;

    // --- LOGICA DE TOLERANCIA A FALLOS (REINTENTOS) ---
    $maxReintentos = 3;
    $respuestaExitosa = false;
    $response = '';
    $httpCode = 0;

    for ($intento = 1; $intento <= $maxReintentos; $intento++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $respuestaExitosa = true;
            break;
        }

        if ($intento < $maxReintentos) {
            usleep(1000000); // Esperar 1 segundo antes de reintentar
        }
    }

    // --- PROCESAR RESPUESTA ---
    if ($respuestaExitosa) {
        $resultado = json_decode($response, true);
        $textoIA = $resultado['candidates'][0]['content']['parts'][0]['text'] ?? 'No se pudo procesar tu solicitud.';

        // Guardamos también la respuesta del modelo en el historial para mantener la coherencia del hilo
        $_SESSION['chat_history'][] = [
            "role" => "model",
            "parts" => [
                ["text" => $textoIA]
            ]
        ];

        echo json_encode([
            'status' => 'success',
            'respuesta' => trim($textoIA)
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Servidor ocupado (HTTP ' . $httpCode . '). Inténtalo de nuevo.'
        ]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);