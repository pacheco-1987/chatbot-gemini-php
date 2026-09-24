# 🤖 Chatbot Avanzado con Gemini API & PHP

Asistente conversacional profesional desarrollado en PHP nativo. Integra capacidades de memoria adaptativa y procesamiento multimedia directo utilizando los últimos modelos estables de Google AI Studio.

## 🚀 Características Principales

*   **🧠 Memoria Continua (Ventana Deslizante):** Mantiene el contexto de la conversación recordando los últimos 10 turnos del chat de forma eficiente.
*   **📸 Soporte Multimodal:** Capacidad para enviar, procesar y analizar imágenes codificadas en Base64 de forma nativa.
*   **🛡️ Tolerancia a Fallos (Retry Logic):** Implementación de hasta 3 reintentos automáticos mediante cURL en caso de saturación del servidor.
*   **🔒 Arquitectura Segura:** Separación estricta de variables de entorno mediante archivos `.env` para evitar la filtración de credenciales.

## 🛠️ Requisitos del Sistema

*   PHP 7.4 o superior.
*   Extensión `cURL` habilitada en tu servidor PHP.
*   Una API Key de [Google AI Studio](https://google.com).

## 📦 Instalación y Configuración Local

1. Clona este repositorio o descarga los archivos en tu servidor local (XAMPP, Laragon, MAMP, etc.).
2. Copia el archivo de plantilla `.env.example` y renómbralo como **`.env`**:
   ```bash
   cp .env.example .env
   ```
3. Abre tu nuevo archivo `.env` y coloca tu credencial privada:
   ```text
   GEMINI_API_KEY=Tu_API_Key_Real_De_Google_Aquí
   ```
4. Ejecuta el script de prueba desde tu consola para verificar que la conexión con Google esté operativa:
   ```bash
   php test-conexion.php
   ```

## 📂 Estructura del Proyecto

*   `config.php`: Lector e intérprete robusto del archivo `.env`.
*   `chat-service.php`: Web service central que procesa las peticiones, imágenes e historial.
*   `reiniciar.php`: Script para vaciar la sesión y reiniciar el hilo del chat.
*   `test-conexion.php`: Herramienta de diagnóstico rápido para consola.