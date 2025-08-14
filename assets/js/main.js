document.addEventListener('DOMContentLoaded', function() {
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(t => new bootstrap.Tooltip(t));
    
    const mostrarNotificacion = function(mensaje, tipo = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        notification.style.zIndex = '1100';
        notification.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => notification.remove(), 5000);
    };
    
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 1500);
            }
        });
    });
    
    document.querySelectorAll('a[onclick*="confirm"]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm-message') || '¿Estás seguro?')) {
                e.preventDefault();
            }
        });
    });
    
    if (localStorage.getItem('notificacion')) {
        const notificacion = JSON.parse(localStorage.getItem('notificacion'));
        mostrarNotificacion(notificacion.mensaje, notificacion.tipo);
        localStorage.removeItem('notificacion');
    }
    
    window.mostrarNotificacion = mostrarNotificacion;
});