document.addEventListener('DOMContentLoaded', function() {
    const productosBody = document.getElementById('productosBody');
    const template = document.getElementById('productoTemplate');
    const btnAgregar = document.getElementById('agregarProducto');
    const totalPedido = document.getElementById('totalPedido');
    
    btnAgregar.click();
    
    btnAgregar.addEventListener('click', function() {
        const clone = template.content.cloneNode(true);
        const newRow = productosBody.appendChild(clone);
        actualizarEventos();
    });
    
    productosBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('eliminar-producto')) {
            e.target.closest('tr').remove();
            calcularTotal();
        }
    });
    
    function actualizarEventos() {
        document.querySelectorAll('.producto-select').forEach(select => {
            select.addEventListener('change', function() {
                updateProductRow(this);
            });
        });
        
        document.querySelectorAll('.cantidad').forEach(input => {
            input.addEventListener('change', function() {
                updateProductRow(this);
            });
        });
    }
    
    function updateProductRow(element) {
        const row = element.closest('tr');
        const select = row.querySelector('.producto-select');
        const cantidadInput = row.querySelector('.cantidad');
        
        if (select.value) {
            const precio = select.options[select.selectedIndex].dataset.precio;
            row.querySelector('.precio-unitario').textContent = `₡${parseFloat(precio).toFixed(2)}`;
            
            if (cantidadInput.value && cantidadInput.value > 0) {
                calcularSubtotal(row);
            }
        }
    }
    
    function calcularSubtotal(row) {
        const precio = parseFloat(row.querySelector('.precio-unitario').textContent.replace('₡', ''));
        const cantidad = parseFloat(row.querySelector('.cantidad').value);
        
        if (!isNaN(precio) && !isNaN(cantidad)) {
            const subtotal = precio * cantidad;
            row.querySelector('.subtotal').textContent = `₡${subtotal.toFixed(2)}`;
            calcularTotal();
        }
    }
    
    function calcularTotal() {
        let total = 0;
        document.querySelectorAll('#productosBody tr').forEach(row => {
            if (row.querySelector('.producto-select').value) {
                const subtotal = parseFloat(row.querySelector('.subtotal').textContent.replace('₡', ''));
                if (!isNaN(subtotal)) {
                    total += subtotal;
                }
            }
        });
        totalPedido.textContent = `₡${total.toFixed(2)}`;
    }
    
    document.getElementById('formPedido').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const productosData = [];
        let hasErrors = false;
        
        document.querySelectorAll('#productosBody tr').forEach((row, index) => {
            const select = row.querySelector('.producto-select');
            const cantidad = row.querySelector('.cantidad').value;
            const precio = row.querySelector('.precio-unitario').textContent.replace('₡', '');
            
            if (!select.value || !cantidad || !precio) {
                row.style.border = '1px solid red';
                hasErrors = true;
                return;
            }
            
            productosData.push({
                id: select.value,
                cantidad: cantidad,
                precio: precio
            });
        });
        
        if (hasErrors || productosData.length === 0) {
            alert('Por favor complete todos los productos correctamente');
            return;
        }
        
        productosData.forEach((producto, index) => {
            addHiddenInput(this, `productos[${index}][id]`, producto.id);
            addHiddenInput(this, `productos[${index}][cantidad]`, producto.cantidad);
            addHiddenInput(this, `productos[${index}][precio]`, producto.precio);
        });
        
        console.log('Datos a enviar:', {
            cliente_id: this.cliente_id.value,
            productos: productosData
        });
        
        this.submit();
    });
    
    function addHiddenInput(form, name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }
});