<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Chatbot con Gemini</title>

  {{-- Token CSRF: Laravel lo necesita en cada petición POST --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet"/>

  <style>
    :root {
      --bg:       #0d0f17;
      --surface:  #161926;
      --border:   #2a2f45;
      --accent:   #6c63ff;
      --accent2:  #a78bfa;
      --user-bg:  #1e2a4a;
      --bot-bg:   #1a1f30;
      --text:     #e2e8f0;
      --muted:    #64748b;
      --radius:   14px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg); color: var(--text);
      font-family: 'Inter', sans-serif;
      height: 100vh; display: flex;
      align-items: center; justify-content: center;
    }
    .chat-wrapper {
      width: 100%; max-width: 680px; height: 92vh;
      display: flex; flex-direction: column;
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 20px; overflow: hidden;
      box-shadow: 0 0 60px rgba(108,99,255,.15);
    }
    header {
      padding: 18px 24px; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; gap: 12px; background: var(--bg);
    }
    .dot {
      width:10px; height:10px; border-radius:50%;
      background: var(--accent); box-shadow: 0 0 8px var(--accent);
      animation: pulse 2s infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    header h1 { font-family:'Space Mono',monospace; font-size:1rem; color:var(--accent2); }
    header span { font-size:.75rem; color:var(--muted); margin-left:auto; }

    #chat-box {
      flex:1; overflow-y:auto; padding:20px 24px;
      display:flex; flex-direction:column; gap:14px; scroll-behavior:smooth;
    }
    #chat-box::-webkit-scrollbar { width:6px; }
    #chat-box::-webkit-scrollbar-thumb { background:var(--border); border-radius:10px; }

    .message {
      max-width:80%; padding:12px 16px; border-radius:var(--radius);
      font-size:.92rem; line-height:1.6; white-space:pre-wrap;
      word-break:break-word; animation:fadeUp .25s ease;
    }
    @keyframes fadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
    .message.user {
      align-self:flex-end; background:var(--user-bg);
      border:1px solid #2d3a6a; border-bottom-right-radius:4px;
    }
    .message.bot {
      align-self:flex-start; background:var(--bot-bg);
      border:1px solid var(--border); border-bottom-left-radius:4px;
    }
    .message.typing span {
      display:inline-block; width:7px; height:7px;
      background:var(--muted); border-radius:50%; margin:0 2px;
      animation:bounce .9s infinite;
    }
    .message.typing span:nth-child(2){animation-delay:.15s}
    .message.typing span:nth-child(3){animation-delay:.30s}
    @keyframes bounce { 0%,80%,100%{transform:translateY(0)} 40%{transform:translateY(-6px)} }

    .input-area {
      padding:16px 24px; border-top:1px solid var(--border);
      display:flex; gap:10px; background:var(--bg);
    }
    #user-input {
      flex:1; background:var(--surface); border:1px solid var(--border);
      border-radius:10px; padding:12px 16px; color:var(--text);
      font-family:'Inter',sans-serif; font-size:.92rem;
      resize:none; outline:none; transition:border-color .2s;
    }
    #user-input:focus { border-color:var(--accent); }
    #user-input::placeholder { color:var(--muted); }
    #send-btn {
      background:var(--accent); border:none; border-radius:10px;
      padding:0 20px; color:#fff; font-weight:600; font-size:.9rem;
      cursor:pointer; transition:background .2s,transform .1s;
    }
    #send-btn:hover  { background:var(--accent2); }
    #send-btn:active { transform:scale(.96); }
    #send-btn:disabled { background:var(--border); cursor:not-allowed; }
  </style>
</head>
<body>

<div class="chat-wrapper">
  <header>
    <div class="dot"></div>
    <h1>Gemini Chat</h1>
    <span>Laravel + Gemini 2.0 Flash</span>
  </header>

  <div id="chat-box"></div>

  <div class="input-area">
    <textarea id="user-input" rows="1" placeholder="Escribe tu mensaje… (Enter para enviar)"></textarea>
    <button id="send-btn">Enviar</button>
  </div>
</div>

<script>
  // ── Referencias al DOM ──
  const chatBox  = document.getElementById('chat-box');
  const input    = document.getElementById('user-input');
  const sendBtn  = document.getElementById('send-btn');

  // ── Historial de la conversación (vive en el navegador) ──
  let historial = [];

  // ── CSRF Token (Laravel lo requiere en peticiones POST) ──
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  // ─────────────────────────────────────────────────
  // Agrega un mensaje a la pantalla
  // ─────────────────────────────────────────────────
  function agregarMensaje(texto, autor) {
    const div = document.createElement('div');
    div.classList.add('message', autor);
    div.textContent = texto;
    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;
    return div;
  }

  // ─────────────────────────────────────────────────
  // Muestra la animación "escribiendo..."
  // ─────────────────────────────────────────────────
  function mostrarEscribiendo() {
    const div = document.createElement('div');
    div.classList.add('message', 'bot', 'typing');
    div.innerHTML = '<span></span><span></span><span></span>';
    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;
    return div;
  }

  // ─────────────────────────────────────────────────
  // Envía el mensaje al controlador de Laravel
  // ─────────────────────────────────────────────────
  async function enviarMensaje() {
    const texto = input.value.trim();
    if (!texto) return;

    agregarMensaje(texto, 'user');
    input.value = '';
    sendBtn.disabled = true;

    const burbujaEscribiendo = mostrarEscribiendo();

    try {
      // Petición POST a /chat (definida en routes/web.php)
      const res = await fetch('{{ route("chat.enviar") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,   // obligatorio en Laravel
        },
        body: JSON.stringify({
          mensaje:   texto,
          historial: historial,   // enviamos el historial completo
        }),
      });

      const datos = await res.json();

      burbujaEscribiendo.remove();

      if (datos.error) {
        agregarMensaje('❌ ' + datos.error, 'bot');
      } else {
        agregarMensaje(datos.respuesta, 'bot');
        // Actualizamos el historial con lo que devuelve el servidor
        historial = datos.historial;
      }

    } catch (err) {
      burbujaEscribiendo.remove();
      agregarMensaje('❌ Error de red. Revisa que el servidor esté corriendo.', 'bot');
      console.error(err);
    }

    sendBtn.disabled = false;
    input.focus();
  }

  // ── Eventos ──
  sendBtn.addEventListener('click', enviarMensaje);
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      enviarMensaje();
    }
  });

  // ── Mensaje de bienvenida ──
  agregarMensaje('¡Hola! Soy tu asistente con Gemini. ¿En qué te puedo ayudar? 🤖', 'bot');
</script>
</body>
</html>