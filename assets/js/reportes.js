document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    document.querySelectorAll('.btn-export').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const formato = this.dataset.formato;
            const url = new URL(window.location.href);
            url.searchParams.set('formato', formato);
            
            const spinner = document.createElement('span');
            spinner.className = 'spinner-border spinner-border-sm';
            this.appendChild(spinner);
            
            window.location.href = url.toString();
        });
    });
    
    const formFiltros = document.querySelector('form[method="get"]');
    if (formFiltros) {
        formFiltros.addEventListener('submit', function(e) {
            const desde = this.querySelector('input[name="desde"]');
            const hasta = this.querySelector('input[name="hasta"]');
            
            if (desde && hasta && desde.value && hasta.value) {
                if (new Date(desde.value) > new Date(hasta.value)) {
                    e.preventDefault();
                    alert('La fecha "Desde" no puede ser mayor que la fecha "Hasta"');
                    desde.focus();
                }
            }
        });
    }
    
    function actualizarTotales() {
        document.querySelectorAll('.monto-aplicar').forEach(input => {
            input.addEventListener('change', function() {
                let total = 0;
                document.querySelectorAll('.monto-aplicar').forEach(i => {
                    total += parseFloat(i.value) || 0;
                });
                document.getElementById('total-aplicado').textContent = total.toFixed(2);
            });
        });
    }
    actualizarTotales();
    
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }
});