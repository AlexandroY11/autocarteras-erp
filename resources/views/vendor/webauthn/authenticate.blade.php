<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación — AutoCarteras</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-[3rem] p-10 border border-gray-100 shadow-2xl text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16"></div>

            <div class="relative z-10">
                <div class="w-20 h-20 bg-gray-900 rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-xl">
                    <svg class="w-10 h-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.333 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>

                <h2 class="text-2xl font-black text-gray-900 mb-2">Confirmar Identidad</h2>
                <p class="text-gray-500 font-medium mb-10">Usa tu huella o FaceID para ingresar al sistema de forma segura.</p>

                {{-- FORMULARIO CRUCIAL PARA LA LIBRERÍA --}}
                <form method="POST" action="{{ route('webauthn.auth' ) }}" id="webauthn-auth-form">
                    @csrf
                    <input type="hidden" name="auth" id="webauthn-auth-data">
                    
                    <div class="bg-gray-900 text-white p-5 rounded-2xl flex items-center justify-center gap-4 shadow-lg cursor-pointer" onclick="document.getElementById('webauthn-auth-form').submit()">
                        <svg class="w-6 h-6 text-blue-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 3c1.288 0 2.512.24 3.638.678m8.305 11.066a10.009 10.009 0 00-1.007-3.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-bold">Toca el sensor...</span>
                    </div>
                </form>

                <div class="mt-8">
                    <a href="/login" class="text-xs font-black text-gray-400 uppercase tracking-widest hover:text-blue-600 transition-colors">
                        Volver al login con contraseña
                    </a>
                </div>
            </div>
        </div>
    </div>
    @include('webauthn::client_script')
</body>
</html>
