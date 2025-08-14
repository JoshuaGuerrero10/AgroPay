document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    document.querySelectorAll('.monto-formateado').forEach(el => {
        const valor = parseFloat(el.textContent);
        if (!isNaN(valor)) {
            el.textContent = '₡' + valor.toLocaleString('es-CR', {minimumFractionDigits: 2});
        }
    });

    document.getElementById('btnImprimir').addEventListener('click', function() {
        window.print();
    });
});