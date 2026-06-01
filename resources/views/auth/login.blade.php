<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso — AutoCarteras Cali</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center px-4 relative overflow-hidden">
    
    {{-- Decoración de fondo --}}
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-100/50 rounded-full -ml-48 -mt-48 blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-100/50 rounded-full -mr-48 -mb-48 blur-3xl"></div>

    <div class="w-full max-w-md relative z-10">
        <div class="bg-white rounded-[3rem] shadow-2xl shadow-blue-100 p-10 border border-gray-100">
            
            <div class="text-center mb-10">
                <img src="/logo.png" alt="Logo" class="h-16 mx-auto mb-4">
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">AutoCarteras</h1>
                <p class="text-gray-400 font-bold text-xs uppercase tracking-widest mt-1">Sistema de Producción</p>
            </div>

            @if(isset($errors ) && $errors->any())
                <div class="bg-red-50 text-red-700 text-xs font-bold px-4 py-3 rounded-2xl mb-6 border border-red-100 animate-bounce">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4 mb-1">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full bg-gray-50 border-none rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-gray-300"
                        placeholder="tu@correo.com" required autofocus>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4 mb-1">Contraseña</label>
                    <input type="password" name="password"
                        class="w-full bg-gray-50 border-none rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-gray-300"
                        placeholder="••••••••" required>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full bg-blue-700 hover:bg-blue-800 text-white font-black py-5 rounded-[2rem] text-lg transition-all shadow-xl shadow-blue-100 active:scale-[0.98]">
                        Ingresar al Sistema
                    </button>
                </div>

                {{-- Separador visual --}}
                <div class="relative py-4">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-4 text-gray-300 font-black tracking-widest">O también</span></div>
                </div>

                {{-- BOTÓN DE HUELLA CONECTADO --}}
                <div class="pt-2">
                    <form method="POST" action="{{ route('webauthn.auth.options') }}" id="webauthn-login-form">
                        @csrf
                        <button type="submit"
                            class="w-full bg-white border-2 border-gray-100 hover:border-blue-100 text-gray-600 font-bold py-4 rounded-[2rem] transition-all flex items-center justify-center gap-3 shadow-sm active:scale-[0.98]">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 3c1.288 0 2.512.24 3.638.678m8.305 11.066a10.009 10.009 0 00-1.007-3.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Ingresar con Huella / FaceID
                        </button>
                    </form>
                </div>
            </form>
        </div>
        
        <p class="text-center mt-8 text-[10px] font-black text-gray-400 uppercase tracking-widest">
            &copy; {{ date('Y') }} AutoCarteras Cali — Sistema de Producción
        </p>
    </div>
</body>
</html>
