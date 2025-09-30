// Variables globales
let carrito = [];
let tasaDelDia = typeof TASA_DEL_DIA !== 'undefined' ? TASA_DEL_DIA : 36.50;
let descuentoAplicado = 0;

// ==================== FUNCIÓN QUE FALTABA ====================
function obtenerCarrito() {
    return carrito;
}

function formatearMoneda(valor, moneda = "USD") {
    if (moneda === "VES") {
        return parseFloat(valor).toLocaleString("es-VE", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + " Bs";
    } else {
        return parseFloat(valor).toLocaleString("es-VE", {
            style: "currency",
            currency: "USD",
            minimumFractionDigits: 2
        });
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de ventas cargado - Tasa:', tasaDelDia);
    
    // Configurar método de pago
    const metodosPago = document.querySelectorAll('input[name="metodo_pago"]');
    const grupoReferencia = document.getElementById('grupo-referencia');
    
    if (metodosPago && grupoReferencia) {
        metodosPago.forEach(metodo => {
            metodo.addEventListener('change', function() {
                const necesitaReferencia = ['transferencia', 'pago_movil'].includes(this.value);
                grupoReferencia.style.display = necesitaReferencia ? 'block' : 'none';
            });
        });
    }
    
    // Cargar clientes si existe el select - COMENTADO TEMPORALMENTE
    // cargarClientes();
    
    // Configurar búsqueda con Enter
    const buscarInput = document.getElementById('buscar-producto');
    if (buscarInput) {
        buscarInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarProducto();
            }
        });
    }
});

// Función de búsqueda de productos
async function buscarProducto() {
    const busqueda = document.getElementById('buscar-producto').value.trim();
    if (!busqueda) {
        alert('Ingrese un código o nombre de producto');
        return;
    }

    try {
        const response = await fetch(`${BASE_URL}?r=buscar-producto&q=${encodeURIComponent(busqueda)}`);
        
        // Verificar si la respuesta es HTML de error
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Respuesta no JSON:', text.substring(0, 200));
            alert('Error en el servidor. La búsqueda no está disponible.');
            return;
        }
        
        const productos = await response.json();
        mostrarResultados(productos);
    } catch (error) {
        console.error('Error:', error);
        alert('Error al buscar producto');
    }
}

// Mostrar resultados de búsqueda
function mostrarResultados(productos) {
    const resultadosDiv = document.getElementById('resultados-busqueda');
    
    if (!resultadosDiv) {
        console.error('Elemento resultados-busqueda no encontrado');
        return;
    }
    
    if (!productos || productos.length === 0) {
        resultadosDiv.innerHTML = '<div class="alert alert-warning">No se encontraron productos</div>';
        return;
    }

    let html = '';
    productos.forEach(producto => {
        const estaAgotado = producto.cantidad <= 0;
        const nombreSeguro = producto.nombre_producto.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        html += `
            <div class="producto-item ${estaAgotado ? 'producto-agotado' : ''}" 
                onclick="${estaAgotado ? '' : `agregarAlCarrito(${producto.id_producto}, '${nombreSeguro}', ${producto.precio_usd}, ${producto.cantidad})`}">
                <div class="producto-nombre">${producto.nombre_producto}</div>
                <div class="producto-precio">${formatearMoneda(producto.precio_usd)}</div>
                <div class="producto-stock">Stock: ${producto.cantidad} ${estaAgotado ? '❌ Agotado' : '✅ Disponible'}</div>
            </div>
        `;
    });
    
    resultadosDiv.innerHTML = html;
}

// Funciones del carrito
function agregarAlCarrito(id, nombre, precio, stock) {
    if (stock <= 0) {
        alert('Producto agotado');
        return;
    }

    const productoExistente = carrito.find(p => p.id === id);
    
    if (productoExistente) {
        if (productoExistente.cantidad >= stock) {
            alert('No hay suficiente stock disponible');
            return;
        }
        productoExistente.cantidad++;
    } else {
        carrito.push({
            id: id,
            nombre: nombre,
            precio: parseFloat(precio),
            cantidad: 1,
            stock: parseInt(stock)
        });
    }
    
    actualizarCarrito();
    
    // Limpiar búsqueda
    const buscarInput = document.getElementById('buscar-producto');
    const resultadosDiv = document.getElementById('resultados-busqueda');
    if (buscarInput) buscarInput.value = '';
    if (resultadosDiv) resultadosDiv.innerHTML = '';
}

function eliminarDelCarrito(id) {
    carrito = carrito.filter(p => p.id !== id);
    actualizarCarrito();
}

function actualizarCantidad(id, nuevaCantidad) {
    const producto = carrito.find(p => p.id === id);
    if (producto) {
        nuevaCantidad = parseInt(nuevaCantidad);
        if (nuevaCantidad > 0 && nuevaCantidad <= producto.stock) {
            producto.cantidad = nuevaCantidad;
            actualizarCarrito();
        } else if (nuevaCantidad > producto.stock) {
            alert('No hay suficiente stock disponible');
            producto.cantidad = producto.stock;
            actualizarCarrito();
        } else if (nuevaCantidad <= 0) {
            eliminarDelCarrito(id);
        }
    }
}

function vaciarCarrito() {
    if (confirm('¿Estás seguro de vaciar el carrito?')) {
        carrito = [];
        descuentoAplicado = 0;
        actualizarCarrito();
    }
}

// ==================== FUNCIONES DE FLUJO ====================

function actualizarResumenPedido() {
    const carritoData = obtenerCarrito(); // ← AHORA ESTÁ DEFINIDA
    const resumenContainer = document.getElementById('resumen-pedido');
    const tasa = parseFloat(document.getElementById('tasa-actual').value);
    
    let html = '';
    let subtotal = 0;
    
    carritoData.forEach(item => {
        subtotal += item.precio * item.cantidad;
        html += `
            <div class="producto-item">
                <div class="producto-nombre">${item.nombre}</div>
                <div class="producto-stock">En Stock: ${item.stock}</div>
            </div>
        `;
    });
    
    const iva = subtotal * 0.16;
    const totalUSD = subtotal + iva - descuentoAplicado;
    const totalBS = totalUSD * tasa;
    
    resumenContainer.innerHTML = html;
    document.getElementById('resumen-subtotal').textContent = formatearMoneda(subtotal);
    document.getElementById('resumen-iva').textContent = formatearMoneda(iva);
    document.getElementById('resumen-total-usd').textContent = formatearMoneda(totalUSD);
    document.getElementById('resumen-total-bs').textContent = formatearMoneda(totalBS, "VES");
}

// Mostrar pantalla de pago
function mostrarPasoPago() {
    document.getElementById('paso-carrito').style.display = 'none';
    document.getElementById('paso-pago').style.display = 'grid';
    
    // Actualizar resumen del pedido
    actualizarResumenPedido();
}

// Volver al carrito
function volverAlCarrito() {
    document.getElementById('paso-pago').style.display = 'none';
    document.getElementById('paso-carrito').style.display = 'grid';
}

// Actualizar carrito
function actualizarCarrito() {
    const carritoVacio = document.getElementById('carrito-vacio');
    const contenidoCarrito = document.getElementById('contenido-carrito');
    const btnProcesar = document.getElementById('btn-procesar');
    const itemsCarrito = document.getElementById('items-carrito');

    if (!carritoVacio || !contenidoCarrito || !btnProcesar || !itemsCarrito) {
        console.error('Elementos del carrito no encontrados');
        return;
    }

    if (carrito.length === 0) {
        carritoVacio.style.display = 'block';
        contenidoCarrito.style.display = 'none';
        btnProcesar.disabled = true;
        return;
    }

    carritoVacio.style.display = 'none';
    contenidoCarrito.style.display = 'block';
    btnProcesar.disabled = false;

    let html = '';
    let subtotalUsd = 0;

    carrito.forEach(producto => {
        const subtotalProducto = producto.precio * producto.cantidad;
        subtotalUsd += subtotalProducto;
        
        html += `
            <tr>
                <td>${producto.nombre}</td>
                <td>${formatearMoneda(producto.precio)}</td>
                <td>
                    <input type="number" 
                        value="${producto.cantidad}" 
                        min="1" 
                        max="${producto.stock}"
                        class="cantidad-input"
                        onchange="actualizarCantidad(${producto.id}, this.value)">
                </td>
                <td>${formatearMoneda(subtotalProducto)}</td>
                <td>
                    <button onclick="eliminarDelCarrito(${producto.id})" 
                            class="btn btn-danger btn-sm">
                        ❌
                    </button>
                </td>
            </tr>
        `;
    });

    itemsCarrito.innerHTML = html;

    // Calcular totales
    const ivaUsd = subtotalUsd * 0.16;
    const totalUsd = subtotalUsd + ivaUsd - descuentoAplicado;
    const totalBs = totalUsd * tasaDelDia;

    // Actualizar interfaz
    document.getElementById('subtotal-usd').textContent = formatearMoneda(subtotalUsd);
    document.getElementById('iva-usd').textContent = formatearMoneda(ivaUsd);
    document.getElementById('total-usd').textContent = formatearMoneda(totalUsd);
    document.getElementById('total-bs').textContent = formatearMoneda(totalBs, "VES");
}

// Cargar clientes desde la base de datos - COMENTADO TEMPORALMENTE
async function cargarClientes() {
    try {
        const selectClientes = document.querySelector('select[name="id_cliente"]');
        if (!selectClientes) return;
        
        const response = await fetch(`${BASE_URL}?r=obtener-clientes`);
        const clientes = await response.json();
        
        if (clientes && clientes.length > 0) {
            clientes.forEach(cliente => {
                const option = document.createElement('option');
                option.value = cliente.id_cliente;
                option.textContent = `${cliente.nombre} ${cliente.apellido}`;
                selectClientes.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando clientes:', error);
    }
}

// Procesar venta
document.addEventListener('DOMContentLoaded', function() {
    const formVenta = document.getElementById('form-venta');
    if (formVenta) {
        formVenta.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (carrito.length === 0) {
                alert('El carrito está vacío');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('carrito', JSON.stringify(carrito));
            formData.append('tasa', tasaDelDia);
            formData.append('descuento', descuentoAplicado);
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });
                
                const resultado = await response.json();
                
                if (resultado.success) {
                    alert('Venta procesada exitosamente');
                    // Reiniciar todo
                    carrito = [];
                    descuentoAplicado = 0;
                    this.reset();
                    actualizarCarrito();
                    
                    // Volver al paso del carrito
                    volverAlCarrito();
                    
                    // Ocultar referencia si estaba visible
                    const grupoReferencia = document.getElementById('grupo-referencia');
                    if (grupoReferencia) grupoReferencia.style.display = 'none';
                } else {
                    alert('Error: ' + resultado.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la venta');
            }
        });
    }
});

// Hacer funciones disponibles globalmente
window.buscarProducto = buscarProducto;
window.agregarAlCarrito = agregarAlCarrito;
window.eliminarDelCarrito = eliminarDelCarrito;
window.actualizarCantidad = actualizarCantidad;
window.vaciarCarrito = vaciarCarrito;
window.mostrarPasoPago = mostrarPasoPago;
window.volverAlCarrito = volverAlCarrito;
window.obtenerCarrito = obtenerCarrito;