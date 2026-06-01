<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso — AutoCarteras Cali</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/axios@1.2.6/dist/axios.min.js"></script>
    <script src="{!! secure_asset('vendor/webauthn/webauthn.js') !!}"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4 relative overflow-hidden">

    {{-- Background --}}
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-100/50 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-blue-50 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-md">

        <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] p-10 border border-gray-100">

            {{-- LOGO --}}
            <div class="text-center mb-10">
                <img src="/logo.png" class="h-20 mx-auto mb-4" alt="AutoCarteras">
                <h1 class="text-2xl font-black text-gray-900">Bienvenido</h1>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Sistema de Producción
                </p>
            </div>

            {{-- ERROR --}}
            @if(isset($errors) && $errors->any())
                <div class="bg-red-50 border border-red-100 text-red-600 text-xs font-bold px-4 py-3 rounded-2xl mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- EMAIL + PASSWORD (NORMAL LOGIN) --}}
            <form method="POST" action="/login" class="space-y-6">
                @csrf

                <div>
                    <label class="text-xs font-black text-gray-400 uppercase">Correo</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full bg-gray-50 border-none rounded-2xl px-6 py-4 text-sm font-bold"
                        placeholder="nombre@empresa.com"
                    >
                </div>

                <div>
                    <label class="text-xs font-black text-gray-400 uppercase">Contraseña</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full bg-gray-50 border-none rounded-2xl px-6 py-4 text-sm font-bold"
                        placeholder="••••••••"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full bg-blue-700 hover:bg-blue-800 text-white font-black py-5 rounded-[2rem]"
                >
                    Ingresar
                </button>
            </form>

            {{-- DIVISOR --}}
            <div class="relative py-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-100"></div>
                </div>

                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-white px-4 text-gray-300 font-black tracking-widest">
                        O también
                    </span>
                </div>
            </div>

            {{-- WEBAUTHN BUTTON --}}
            <button
                type="button"
                onclick="startWebauthn()"
                class="w-full bg-white border-2 border-gray-100 hover:border-blue-100 text-gray-600 font-bold py-4 rounded-[2rem] flex items-center justify-center gap-3"
            >
                🔐 Ingresar con Huella / FaceID
            </button>

        </div>

        <p class="text-center mt-8 text-xs font-bold text-gray-400 uppercase">
            &copy; {{ date('Y') }} AutoCarteras Cali
        </p>

    </div>

<script>
axios.defaults.headers.common['X-CSRF-TOKEN'] =
    document.querySelector('meta[name="csrf-token"]').content;

function startWebauthn() {

    const email = document.getElementById('email').value;

    if (!email) {
        alert('Ingresa tu correo primero');
        return;
    }

    axios.post("{{ route('webauthn.auth.options') }}", {
        email: email,
        _token: document.querySelector('meta[name="csrf-token"]').content
    })
    .then(function(res) {

        const publicKey = res.data;

        const webauthn = new WebAuthn();

        webauthn.sign(publicKey, function(data) {

            axios.post("{{ route('webauthn.auth') }}", data)
                .then(function(response) {

                    window.location.href =
                        response.data.callback ?? "/home";

                })
                .catch(function(error) {

                    console.error(error);

                    alert(
                        error?.response?.data?.message ??
                        'Error en autenticación'
                    );

                });

        });

    })
    .catch(function(error) {

        console.error(error);

        alert('No se pudo iniciar WebAuthn');
    });
}
</script>

</body>
</html>