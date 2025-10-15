// Variables globales
let carrito = [];
let tasaDelDia;

function mostrarStockAlertModal(mensaje, titulo = null) {
    const tituloElement = document.getElementById('modal-titulo-stock');
    const mensajeElement = document.getElementById('modal-mensaje-stock');
    
    // Si se proporciona un título, lo usamos; si no, dejamos el actual
    if (titulo !== null) {
        tituloElement.textContent = titulo;
    }
    mensajeElement.textContent = mensaje;
    
    document.getElementById('stock-alert-modal').style.display = 'flex'; 
}

function ocultarStockAlertModal() {
    document.getElementById('stock-alert-modal').style.display = 'none';
}

// Hacer la función de ocultar disponible globalmente para el HTML
window.ocultarStockAlertModal = ocultarStockAlertModal;

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
    const tasaInput = document.getElementById('tasa-actual');
    if (tasaInput) {
        tasaDelDia = parseFloat(tasaInput.value) || 36.50;
    } else {
        tasaDelDia = 36.50;
    }
    
    // Configurar método de pago
    const metodosPago = document.querySelectorAll('input[name="metodo_pago"]');
    const grupoReferencia = document.getElementById('grupo-referencia');
    
    if (metodosPago && grupoReferencia) {
        metodosPago.forEach(metodo => {
            metodo.addEventListener('change', function() {
                const necesitaReferencia = ['transferencia', 'pago_movil', 'tarjeta'].includes(this.value);
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
        mostrarStockAlertModal('Por favor, ingrese un código o nombre de producto para buscar.');
        return;
    }

    try {
        const response = await fetch(`${BASE_URL}?r=buscar-producto&q=${encodeURIComponent(busqueda)}`);
        
        // Verificar si la respuesta es HTML de error
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Respuesta no JSON:', text.substring(0, 200));
            mostrarStockAlertModal('Error en el servidor. La búsqueda no está disponible o el producto no existe.');
            return;
        }
        
        const productos = await response.json();
        mostrarResultados(productos);
    } catch (error) {
        console.error('Error:', error);
        mostrarStockAlertModal('Error al buscar producto.');
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
        
        // Construir URL de la imagen
        const imagenUrl = producto.imagen 
            ? `${BASE_URL}img/productos/${encodeURIComponent(producto.imagen)}`
            : `${BASE_URL}img/productos/default.png`; // Imagen por defecto si no hay imagen

        html += `
            <div class="producto-item ${estaAgotado ? 'producto-agotado' : ''}" 
                onclick="${estaAgotado ? '' : `agregarAlCarrito(${producto.id_producto}, '${nombreSeguro}', ${producto.precio_usd}, ${producto.cantidad})`}">
                
                <!-- Imagen del producto -->
                <div class="producto-imagen">
                    <img src="${imagenUrl}" alt="${producto.nombre_producto}" onerror="this.src='${BASE_URL}img/default-product.png'">
                </div>
                
                <!-- Datos del producto -->
                <div class="producto-info">
                    <div class="producto-nombre">${producto.nombre_producto}</div>
                    <div class="producto-precio">${formatearMoneda(producto.precio_usd)}</div>
                    <div class="producto-stock">Stock: ${producto.cantidad} ${estaAgotado ? '❌ Agotado' : '✅ Disponible'}</div>
                </div>
            </div>
        `;
    });
    
    resultadosDiv.innerHTML = html;
}

// Funciones del carrito
function agregarAlCarrito(id, nombre, precio, stock) {
    if (stock <= 0) {
        mostrarStockAlertModal('Producto agotado. No se puede agregar al carrito.');
        return;
    }

    const productoExistente = carrito.find(p => p.id === id);
    
    if (productoExistente) {
        // Incrementa la cantidad solo si no supera el stock disponible
        if (productoExistente.cantidad + 1 > stock) {
            mostrarStockAlertModal(`No se puede agregar más. Solo quedan ${stock} unidades de "${nombre}".`);
            return;
        }
        productoExistente.cantidad++;
    } else {
        carrito.push({
            id: id,
            nombre: nombre,
            precio: parseFloat(precio),
            cantidad: 1,
            stock: parseInt(stock) // Guardamos el stock original disponible
        });
    }
    
    actualizarCarrito();
    
    // Limpiar búsqueda
    const buscarInput = document.getElementById('buscar-producto');
    const resultadosDiv = document.getElementById('resultados-busqueda');
    if (buscarInput) buscarInput.value = '';
    if (resultadosDiv) resultadosDiv.innerHTML = '';
}

// Modificación también en actualizarCantidad para el mismo chequeo
function actualizarCantidad(id, nuevaCantidad) {
    const producto = carrito.find(p => p.id === id);
    if (producto) {
        nuevaCantidad = parseInt(nuevaCantidad);
        if (isNaN(nuevaCantidad) || nuevaCantidad <= 0) {
            eliminarDelCarrito(id); // Si la cantidad es 0 o inválida, se elimina del carrito
            return;
        }
        
        if (nuevaCantidad > producto.stock) {
            mostrarStockAlertModal(`No hay suficiente stock disponible para "${producto.nombre}". Solo quedan ${producto.stock} unidades.`);
            // Si la cantidad es mayor que el stock, se ajusta al máximo disponible
            producto.cantidad = producto.stock; 
        } else {
            producto.cantidad = nuevaCantidad;
        }
        actualizarCarrito();
    }
}

function vaciarCarrito() {
    if (confirm('¿Estás seguro de vaciar el carrito?')) {
        carrito = [];
        // descuentoAplicado se ha eliminado
        actualizarCarrito();
    }
}

function actualizarResumenPedido() {
    const carritoData = obtenerCarrito(); 
    const resumenContainer = document.getElementById('resumen-pedido');
    const tasa = parseFloat(document.getElementById('tasa-actual').value);
    
    let html = '';
    let subtotal = 0;
    
    carritoData.forEach(item => {
        subtotal += item.precio * item.cantidad;
        html += `
            <div class="producto-item">
                <div class="producto-nombre">${item.nombre}</div>
                <div class="producto-stock">Cantidad: ${item.cantidad} (Stock: ${item.stock})</div>
            </div>
        `;
    });
    
    const iva = subtotal * 0.16;
    const totalUSD = subtotal + iva;
    const totalBS = totalUSD * tasaDelDia;
    
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
        const precioBs = producto.precio * tasaDelDia;
        const subtotalProductoUsd = producto.precio * producto.cantidad;
        
        html += `
            <tr>
                <td>${producto.nombre}</td>
                <td>${formatearMoneda(precioBs, "VES")}</td> 
                <td>${formatearMoneda(producto.precio)}</td> 
                <td>
                    <input type="number" 
                        value="${producto.cantidad}" 
                        min="1" 
                        max="${producto.stock}"
                        class="cantidad-input"
                        onchange="actualizarCantidad(${producto.id}, this.value)">
                </td>
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
    carrito.forEach(producto => {
        subtotalUsd += producto.precio * producto.cantidad;
    });
    const subtotalBs = subtotalUsd * tasaDelDia;
    const ivaUsd = subtotalUsd * 0.16;
    const totalUsd = subtotalUsd + ivaUsd;
    const totalBs = totalUsd * tasaDelDia;

    // Actualizar interfaz
    document.getElementById('subtotal-usd').textContent = formatearMoneda(subtotalUsd);
    document.getElementById('subtotal-bs').textContent = formatearMoneda(subtotalBs, "VES");
    document.getElementById('iva-usd').textContent = formatearMoneda(ivaUsd);
    document.getElementById('total-usd').textContent = formatearMoneda(totalUsd);
    document.getElementById('total-bs').textContent = formatearMoneda(totalBs, "VES");
}

function eliminarDelCarrito(id) {
    carrito = carrito.filter(p => p.id !== id);
    actualizarCarrito();
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
const formVenta = document.getElementById('form-venta');
if (formVenta) {
    formVenta.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (carrito.length === 0) {
            mostrarStockAlertModal('El carrito de compras está vacío. Agregue productos antes de procesar la venta.');
            return;
        }
        
        const formData = new FormData(this);
        formData.append('carrito', JSON.stringify(carrito));
        formData.append('tasa', tasaDelDia);
        
        try {
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData
            });
            
            // Intentar parsear como JSON directamente
            let resultado;
            try {
                resultado = await response.json();
            } catch (jsonError) {
                const text = await response.text();
                console.error('Error al parsear JSON del servidor:', text.substring(0, 500));
                mostrarStockAlertModal('Error inesperado en el servidor. Por favor, intente de nuevo más tarde.');
                return;
            }
            
            if (resultado.success) {
                mostrarStockAlertModal('Venta procesada exitosamente.', '✅ Éxito');
                // Reiniciar todo
                carrito = [];
                this.reset();
                actualizarCarrito();
                volverAlCarrito();
                const grupoReferencia = document.getElementById('grupo-referencia');
                if (grupoReferencia) grupoReferencia.style.display = 'none';
            } else {
                mostrarStockAlertModal('Error al procesar la venta: ' + resultado.error);
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarStockAlertModal('Error de conexión al procesar la venta. Intente de nuevo.');
        }
    });
}

// Hacer funciones disponibles globalmente
window.buscarProducto = buscarProducto;
window.agregarAlCarrito = agregarAlCarrito;
window.eliminarDelCarrito = eliminarDelCarrito;
window.actualizarCantidad = actualizarCantidad;
window.vaciarCarrito = vaciarCarrito;
window.mostrarPasoPago = mostrarPasoPago;
window.volverAlCarrito = volverAlCarrito;
window.obtenerCarrito = obtenerCarrito;