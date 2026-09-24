<?php
/**
 * Servicio para vaciar el historial de chat de la sesión
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Vaciar el arreglo del historial
$_SESSION['chat_history'] = [];

echo json_encode([
    'status' => 'success',
    'message' => 'Historial reiniciado correctamente.'
]);
exit;