<x-app-layout title="Registrar Dispositivo">
    <div class="max-w-md mx-auto pt-10 pb-20">
        <div class="bg-white rounded-[3rem] p-10 border border-gray-100 shadow-2xl text-center relative overflow-hidden">

            {{-- Decoración --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16"></div>

            <div class="relative z-10">

                <div class="w-24 h-24 bg-blue-600 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-blue-200 animate-pulse">
                    <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 3c1.288 0 2.512.24 3.638.678m8.305 11.066a10.009 10.009 0 00-1.007-3.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h2 class="text-3xl font-black text-gray-900 mb-3 tracking-tight">
                    Casi listo...
                </h2>

                <p class="text-gray-500 font-medium mb-10">
                    Usa tu huella digital o FaceID para registrar este dispositivo de forma segura.
                </p>

                <div
                    id="status-box"
                    class="bg-blue-50 text-blue-700 p-4 rounded-2xl text-sm font-bold flex items-center justify-center gap-3"
                >
                    <svg class="w-5 h-5 animate-spin" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>

                    <span id="status-text">
                        Preparando registro...
                    </span>
                </div>

                <div
                    id="error-box"
                    class="hidden mt-4 bg-red-50 text-red-700 p-4 rounded-2xl text-sm font-bold"
                ></div>

                <div class="mt-8">
                    <a
                        href="{{ url('/profile') }}"
                        class="text-xs font-black text-gray-400 uppercase tracking-widest hover:text-red-500 transition-colors"
                    >
                        Cancelar registro
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- Axios --}}
    <script src="https://unpkg.com/axios@1.2.6/dist/axios.min.js"></script>

    {{-- Librería WebAuthn --}}
    <script src="{!! secure_asset('vendor/webauthn/webauthn.js') !!}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const statusText = document.getElementById('status-text');
            const errorBox = document.getElementById('error-box');

            const publicKey = @json($publicKey);

            if (document.querySelector('meta[name="csrf-token"]')) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] =
                    document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            }

            function showError(message) {
                console.error(message);

                errorBox.classList.remove('hidden');
                errorBox.innerText = message;

                statusText.innerText = 'No fue posible registrar el dispositivo';
            }

            const webauthn = new WebAuthn(function(name, message) {
                showError(message);
            });

            if (!webauthn.webAuthnSupport()) {
                showError('Tu navegador o dispositivo no es compatible con Passkeys.');
                return;
            }

            statusText.innerText = 'Esperando confirmación del sensor...';

            webauthn.register(
                publicKey,
                function(data) {

                    statusText.innerText = 'Guardando dispositivo...';

                    axios.post("{{ route('webauthn.store') }}", {
                        ...data,
                        name: "Mi Dispositivo ({{ now()->format('d/m/Y') }})"
                    })
                    .then(function(response) {

                        statusText.innerText = 'Registro completado';

                        if (response.data.callback) {
                            window.location.href = response.data.callback;
                            return;
                        }

                        window.location.href = "{{ url('/profile') }}";
                    })
                    .catch(function(error) {

                        console.error(error);

                        if (error.response?.data?.message) {
                            showError(error.response.data.message);
                        } else {
                            showError('Ocurrió un error al registrar el dispositivo.');
                        }
                    });
                }
            );
        });
    </script>
</x-app-layout>