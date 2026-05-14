<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ACTIVIDAD AE6.1 — Chatbot con Gemini en Laravel
    |--------------------------------------------------------------------------
    | Debes completar los dos métodos de este controlador.
    |
    | Documentación útil:
    |  - Controladores Laravel : https://laravel.com/docs/controllers
    |  - Cliente HTTP Laravel  : https://laravel.com/docs/http-client
    |  - Variables de entorno  : https://laravel.com/docs/configuration
    |  - API de Gemini         : https://ai.google.dev/gemini-api/docs
    */


    /*
    |--------------------------------------------------------------------------
    | MÉTODO 1: index()
    |--------------------------------------------------------------------------
    | Devuelve la vista 'chat' (resources/views/chat.blade.php).
    | La vista ya está creada, solo tienes que retornarla.
    |
    | Pista: return view('nombre-de-la-vista');
    */
    public function index()
    {
        // ✏️ ESCRIBE AQUÍ TU CÓDIGO

    }


    /*
    |--------------------------------------------------------------------------
    | MÉTODO 2: enviar(Request $request)
    |--------------------------------------------------------------------------
    | Este método recibe el mensaje del usuario desde el formulario,
    | lo envía a la API de Gemini y devuelve la respuesta en formato JSON.
    |
    | Pasos que debes implementar:
    |
    |  1. Validar que lleguen los campos 'mensaje' (string, obligatorio)
    |     e 'historial' (array, opcional).
    |     Pista: $request->validate([...]);
    |
    |  2. Leer la API Key de Gemini desde el fichero .env.
    |     La variable se llama GEMINI_API_KEY.
    |     Pista: env('NOMBRE_VARIABLE');
    |
    |  3. Construir la URL del endpoint de Gemini:
    |     https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=TU_KEY
    |
    |  4. Recuperar el historial previo y añadir el nuevo mensaje del usuario
    |     con el formato que necesita Gemini:
    |     [ 'role' => 'user', 'parts' => [['text' => $mensaje]] ]
    |
    |  5. Hacer una petición POST a la URL de Gemini con Http::post()
    |     enviando en el body: ['contents' => $historial]
    |
    |  6. Comprobar si la petición falló con $respuesta->failed()
    |     Si falló, devolver un JSON de error con código 500.
    |
    |  7. Extraer el texto de la respuesta:
    |     $datos['candidates'][0]['content']['parts'][0]['text']
    |
    |  8. Añadir la respuesta del bot al historial con role 'model'.
    |
    |  9. Devolver un JSON con 'respuesta' e 'historial' actualizados.
    |     Pista: return response()->json([...]);
    */
    public function enviar(Request $request)
    {
        // ✏️ PASO 1 — Validar los campos de entrada


        // ✏️ PASO 2 y 3 — Leer API Key y construir la URL


        // ✏️ PASO 4 — Recuperar el historial y añadir el mensaje del usuario


        // ✏️ PASO 5 — Llamar a la API de Gemini con Http::post()


        // ✏️ PASO 6 — Comprobar si la petición falló


        // ✏️ PASO 7 — Extraer el texto de la respuesta


        // ✏️ PASO 8 — Añadir la respuesta del bot al historial


        // ✏️ PASO 9 — Devolver JSON con respuesta e historial

    }
}