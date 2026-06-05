<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido/a — Configura tu contraseña</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Inter', Arial, sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07); }
        .header { background: #0f172a; padding: 32px 40px; text-align: center; }
        .header h1 { color: #f8fafc; font-size: 22px; font-weight: 800; margin: 0; letter-spacing: -0.5px; }
        .header p { color: #94a3b8; font-size: 13px; margin: 6px 0 0; }
        .body { padding: 40px; }
        .saludo { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
        .texto { font-size: 15px; color: #475569; line-height: 1.7; margin-bottom: 20px; }
        .btn-wrap { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background: #6366f1; color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 10px; font-size: 15px; font-weight: 700; letter-spacing: 0.2px; }
        .btn:hover { background: #4f46e5; }
        .expiry { background: #fef9c3; border: 1px solid #fde047; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #713f12; margin-bottom: 28px; }
        .link-fallback { font-size: 12px; color: #94a3b8; word-break: break-all; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 40px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">

        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Sistema de gestión comercial</p>
        </div>

        <div class="body">
            <p class="saludo">¡Bienvenido/a, {{ $user->nombres }}!</p>

            <p class="texto">
                Tu cuenta ha sido creada en el sistema. Para acceder por primera vez,
                necesitas configurar tu contraseña personal haciendo clic en el botón de abajo.
            </p>

            <div class="expiry">
                ⏳ Este enlace es válido por <strong>7 días</strong>. Si expira, contacta al administrador
                para que te envíe uno nuevo.
            </div>

            <div class="btn-wrap">
                <a href="{{ $setPasswordUrl }}" class="btn">
                    Configurar mi contraseña
                </a>
            </div>

            <p class="texto">
                Si no esperabas este correo o no solicitaste una cuenta, puedes ignorarlo sin problema.
            </p>

            <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">

            <p class="link-fallback">
                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                {{ $setPasswordUrl }}
            </p>
        </div>

        <div class="footer">
            © {{ date('Y') }} {{ config('app.name') }} — Este es un correo automático, no lo respondas.
        </div>

    </div>
</body>
</html>
