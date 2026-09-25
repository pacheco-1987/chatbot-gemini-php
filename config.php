<?php
/**
 * Configuración global del proyecto
 * Lector robusto para archivos .env en entornos Windows/Linux
 */

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die("Error: El archivo .env no existe en la raíz del proyecto.\n");
}

// Leer el archivo eliminando saltos de línea invisibles (\r o \n)
$lineas = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lineas as $linea) {
    // 1. Limpiar caracteres invisibles de codificación (como el BOM de Windows)
    $linea = preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $linea);
    $linea = trim($linea);
    
    // Ignorar líneas vacías o comentarios
    if (empty($linea) || strpos($linea, '#') === 0) {
        continue;
    }
    
    // Buscar la posición del primer signo '='
    $posicionIgual = strpos($linea, '=');
    if ($posicionIgual === false) {
        continue;
    }
    
    // Extraer clave y valor de forma exacta
    $nombre = trim(substr($linea, 0, $posicionIgual));
    $valor = trim(substr($linea, $posicionIgual + 1));
    
    // Remover comillas si existen
    $valor = trim($valor, '"\'');
    
    // Registrar con máxima compatibilidad
    putenv("$nombre=$valor");
    $_ENV[$nombre] = $valor;
    $_SERVER[$nombre] = $valor;
}

// Definir la constante global usando la superglobal $_ENV para asegurar compatibilidad
$apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?? null;
define('GEMINI_API_KEY', $apiKey);

// Validación real y profesional
if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'TU_API_KEY_AQUI' || empty(GEMINI_API_KEY)) { 
    die("Error: Por favor configura una API Key válida en tu archivo .env\n"); 
}