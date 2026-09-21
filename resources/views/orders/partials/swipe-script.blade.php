{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let activeSwipe = null;
    document.querySelectorAll('[data-swipeable]' ).forEach(wrap => {
        const card = wrap.querySelector('[data-card]');
        const canAdvance = wrap.dataset.canAdvance === '1';
        const bg   = wrap.querySelector('[data-bg]');
        const form = wrap.querySelector('[data-form]');
        const confirmText = wrap.dataset.confirmText || '¿Confirmar esta acción?';

        function resetCard() {
            card.style.transform = 'translateX(0)';
            bg.style.opacity = 0;
            bg.style.backgroundColor = '#3B6D11';
        }
        let startX = 0, curX = 0, dragging = false, movedDistance = 0;
        const CLICK_THRESHOLD = 10; // Pixeles para diferenciar un click de un swipe

        bg.style.opacity = 0;
        bg.style.transition = 'background-color 0.3s ease-in-out, opacity 0.3s ease-in-out';

        const THRESHOLD = 0.38;

        const handleMouseMove = (e) => onMove(e.clientX);
        const handleMouseUp = () => onEnd();

        function onStart(x, event) {

            if (!canAdvance) {
                return;
            }

            if (activeSwipe && activeSwipe !== wrap) {
                return;
            }

            activeSwipe = wrap;

            startX = x;
            curX = 0;
            movedDistance = 0;
            dragging = true;

            card.style.transition = 'none';

            if (event?.preventDefault) {
                event.preventDefault();
            }

            document.addEventListener('mousemove', handleMouseMove);
            document.addEventListener('mouseup', handleMouseUp);
        }

        function onMove(x) {
            if (activeSwipe !== wrap) return;
            if (!dragging) return;
            const deltaX = x - startX;
            curX = Math.max(0, deltaX);
            movedDistance = Math.abs(deltaX); // Actualizar la distancia movida

            const ratio = Math.min(curX / wrap.offsetWidth, 1);
            card.style.transform = `translateX(${curX}px)`;
            bg.style.opacity = Math.min(ratio * 2.5, 1);
            bg.style.backgroundColor = ratio >= THRESHOLD ? '#27500A' : '#3B6D11';
        }

        function onEnd() {
            if (activeSwipe !== wrap) return;

            activeSwipe = null;

            if (!dragging) return;
            dragging = false;

            // Eliminar listeners de mousemove y mouseup del document
            document.removeEventListener('mousemove', handleMouseMove);
            document.removeEventListener('mouseup', handleMouseUp);

            const ratio = curX / wrap.offsetWidth;
            card.style.transition = 'transform 0.35s cubic-bezier(.25,.46,.45,.94)';

            if (movedDistance < CLICK_THRESHOLD) {
                // Si la distancia movida es menor que el umbral, se considera un click
                // No hacemos nada aquí, permitimos que el evento click del <a> se propague
                card.style.transform = 'translateX(0)';
                bg.style.opacity = 0;
                bg.style.backgroundColor = '#3B6D11';
                return;
            }

            // Si es un swipe, prevenimos la navegación del <a>
            // Esto se maneja con un listener en el <a> que previene el default si isSwiping es true

            if (ratio >= THRESHOLD) {
                if (navigator.vibrate) navigator.vibrate(40);
                card.style.transform = `translateX(${wrap.offsetWidth}px)`;

                if (!form) {
                    resetCard();
                    curX = 0;
                    return;
                }

                Swal.fire({
                    title: confirmText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1d4ed8',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, confirmar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then(result => {
                    if (result.isConfirmed) {
                        form.submit();
                    } else {
                        resetCard();
                    }
                });
            } else {
                resetCard();
            }
            curX = 0;
        }

        // Eventos de ratón
        wrap.addEventListener('mousedown',  e => onStart(e.clientX, e));

        // Eventos táctiles
        wrap.addEventListener('touchstart', e => onStart(e.touches[0].clientX, e), { passive: true });
        wrap.addEventListener('touchmove',  e => onMove(e.touches[0].clientX),  { passive: true });
        wrap.addEventListener('touchend',   () => onEnd());

        // La navegación es manual (card ya no es un <a>): si el movimiento
        // total no llegó al umbral, fue un tap real, no un swipe.
        card.addEventListener('click', () => {
            if (movedDistance < CLICK_THRESHOLD && card.dataset.href) {
                window.location.href = card.dataset.href;
            }
        });
    });
</script>
