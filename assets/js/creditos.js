document.addEventListener('DOMContentLoaded', function() {
    const fechaVencimiento = document.querySelector('input[name="fecha_vencimiento"]');
    if (fechaVencimiento) {
        fechaVencimiento.min = new Date().toISOString().split('T')[0];
    }

    document.querySelectorAll('.badge.bg-danger').forEach(badge => {
        badge.closest('tr').classList.add('table-warning');
    });

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});