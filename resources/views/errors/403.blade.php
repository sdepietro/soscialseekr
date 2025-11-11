<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 | Acceso no autorizado</title>
    <style>
        :root{
            --bg: #0b0f14;
            --panel: #121821;
            --panel-2: #0f141b;
            --text: #e6edf3;
            --muted: #9aa7b3;
            --danger: #ff6b6b;
            --accent: #3ea6ff;
            --border: #1e2630;
            --shadow: 0 10px 30px rgba(0,0,0,.35);
            --radius: 16px;
        }
        * { box-sizing: border-box; }
        html, body {
            height: 100%;
            margin: 0;
            background: radial-gradient(1200px 800px at 20% 10%, #0d1320 0%, var(--bg) 55%) fixed;
            color: var(--text);
            font: 16px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji";
        }
        .wrap {
            min-height: 100%;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 720px;
            background: linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 28px;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 14px;
            background: rgba(255, 107, 107, .12);
            border: 1px solid rgba(255, 107, 107, .25);
        }
        .code {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: .04em;
            color: var(--danger);
        }
        .title {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }
        .subtitle {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }
        .msg {
            margin: 18px 0 6px;
            padding: 14px 16px;
            background: rgba(62, 166, 255, .08);
            border: 1px solid rgba(62, 166, 255, .25);
            border-radius: 12px;
            color: var(--text);
            word-wrap: break-word;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }
        .btn {
            appearance: none;
            border: 1px solid var(--border);
            background: #0e141b;
            color: var(--text);
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            transition: transform .04s ease, background .2s ease, border-color .2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn:hover { background: #111925; border-color: #273242; }
        .btn:active { transform: translateY(1px); }
        .btn-primary {
            background: linear-gradient(180deg, #1a73e8, #155bb8);
            border-color: #2a71d6;
        }
        .btn-primary:hover { background: linear-gradient(180deg, #2a7ff0, #1a63c6); }
        .hint {
            margin-top: 10px;
            font-size: 13px;
            color: var(--muted);
        }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
        .footer {
            margin-top: 22px;
            border-top: 1px dashed var(--border);
            padding-top: 14px;
            font-size: 12px;
            color: var(--muted);
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="wrap">
    <main class="card" role="main" aria-labelledby="err-title">
        <div class="header">
            <div class="badge" aria-hidden="true">
                <!-- simple lock icon -->
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <rect x="5" y="10" width="14" height="10" rx="2" stroke="#ff6b6b" stroke-width="1.5"/>
                    <path d="M8 10V8a4 4 0 1 1 8 0v2" stroke="#ff6b6b" stroke-width="1.5" stroke-linecap="round"/>
                    <circle cx="12" cy="15" r="1.6" fill="#ff6b6b"/>
                </svg>
            </div>
            <div>
                <div class="code">403</div>
                <h1 class="title" id="err-title">Acceso no autorizado</h1>
                <p class="subtitle">No contás con los permisos necesarios para ver esta página.</p>
            </div>
        </div>

        <div class="msg mono">
            {{ $message ?? 'This action is unauthorized.' }}
        </div>

        <div class="actions">
            <button class="btn" onclick="history.back()">
                ← Volver
            </button>
            <a href="{{ url('/') }}" class="btn btn-primary">
                Ir al inicio
            </a>
        </div>

        <div class="hint">
            Si creés que esto es un error, contactá a un administrador y compartí qué estabas intentando hacer.
        </div>

        <div class="footer">
            <span>Protegido por control de permisos.</span>
            @auth
                <span>Usuario: <strong class="mono">{{ auth()->user()->email }}</strong></span>
            @endauth
        </div>
    </main>
</div>
</body>
</html>
