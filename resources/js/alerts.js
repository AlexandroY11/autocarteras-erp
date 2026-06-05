import Swal from 'sweetalert2';

// Mixin base con estilos personalizados para los Toasts
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

window.showAlert = {
    // ÉXITO: Fondo verde suave, texto oscuro
    success(message) {
        Toast.fire({
            icon: 'success',
            title: message,
            background: '#f0fdf4',
            color: '#166534',     
            iconColor: '#22c55e'  
        });
    },

    // ERROR: Fondo rojo suave, texto oscuro
    error(message, title = '') {
        Toast.fire({
            icon: 'error',
            title: title ? `<b>${title}</b><br>${message}` : message,
            background: '#fef2f2', 
            color: '#991b1b',      
            iconColor: '#ef4444'   
        });
    },

    // ADVERTENCIA: Fondo naranja/amarillo suave
    warning(message) {
        Toast.fire({
            icon: 'warning',
            title: message,
            background: '#fffbeb', 
            color: '#92400e',      
            iconColor: '#f59e0b'   
        });
    },

    // INFO: Fondo azul suave
    info(message) {
        Toast.fire({
            icon: 'info',
            title: message,
            background: '#eff6ff', 
            color: '#1e40af',      
            iconColor: '#3b82f6'   
        });
    },

    // MODAL DE ELIMINACIÓN (Mantenemos el modal porque es crítico)
    delete(formId, message = 'Esta acción no se puede deshacer') {
        Swal.fire({
            title: '¿Confirmar eliminación?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1d4ed8', 
            cancelButtonColor: '#ef4444',  
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) document.getElementById(formId).submit();
        });
    },

    confirm(event, message = '¿Confirmar acción?', confirmText = 'Sí, confirmar') {
        event.preventDefault();
        const form = event.target;
        Swal.fire({
            title: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1d4ed8',
            cancelButtonColor: '#6b7280',
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    },
};
