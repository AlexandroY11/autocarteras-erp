<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Seguro — AutoCarteras Cali</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/axios@1.2.6/dist/axios.min.js"></script>

    <script src="{!! secure_asset('vendor/webauthn/webauthn.js') !!}"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">

    <style>
        body{
            font-family:'Plus Jakarta Sans',sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4 overflow-hidden">

    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-100/50 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-50 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>

    <div class="w-full max-w-md relative z-10">

        <div class="bg-white rounded-[3rem] shadow-2xl shadow-blue-100 border border-gray-100 p-10">

            <div class="text-center">

                <img src="/logo.png"
                     class="h-20 mx-auto mb-6"
                     alt="AutoCarteras">

                <div class="w-24 h-24 bg-blue-600 rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-blue-200 animate-pulse">

                    <svg class="w-12 h-12 text-white"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="2"
                         stroke="currentColor">

                        <path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 3c1.288 0 2.512.24 3.638.678m8.305 11.066a10.009 10.009 0 00-1.007-3.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>

                </div>

                <h1 class="text-3xl font-black text-gray-900 mb-3">
                    Verificando identidad
                </h1>

                <p class="text-gray-500 font-medium mb-8">
                    Usa tu huella digital o FaceID para acceder al sistema.
                </p>

                <div id="success"
                     class="hidden bg-green-50 text-green-700 p-4 rounded-2xl text-sm font-bold mb-4">
                    Acceso autorizado...
                </div>

                <div id="error"
                     class="hidden bg-red-50 text-red-700 p-4 rounded-2xl text-sm font-bold mb-4">
                </div>

                <div class="bg-blue-50 text-blue-700 p-4 rounded-2xl text-sm font-bold flex items-center justify-center gap-3">

                    <svg class="w-5 h-5 animate-spin"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"></circle>

                        <path class="opacity-75"
                              fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>

                    Esperando confirmación...
                </div>

                <a href="{{ route('login') }}"
                   class="inline-block mt-8 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-red-500 transition-colors">
                    Cancelar acceso
                </a>

            </div>
        </div>
    </div>

<script>
const publicKey = @json($publicKey);

const errorBox = document.getElementById('error');
const successBox = document.getElementById('success');

axios.defaults.headers.common['X-CSRF-TOKEN'] =
    document.querySelector('meta[name="csrf-token"]').content;

function showError(message)
{
    errorBox.classList.remove('hidden');
    errorBox.innerText = message;
}

const webauthn = new WebAuthn((name, message) => {
    showError(message);
});

if (!webauthn.webAuthnSupport()) {
    showError('Este dispositivo no soporta Passkeys.');
}

webauthn.sign(
    publicKey,
    function(data)
    {
        successBox.classList.remove('hidden');

        axios.post("{{ route('webauthn.auth') }}", data)
            .then(function(response){

                if(response.data.callback){
                    window.location.href = response.data.callback;
                } else {
                    window.location.href = "/";
                }

            })
            .catch(function(error){

                console.error(error);

                showError(
                    error?.response?.data?.message
                    ?? 'No fue posible verificar la identidad.'
                );
            });
    }
);
</script>

</body>
</html>