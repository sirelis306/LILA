// Variables globales
let carrito = [];
let tasaDelDia;

function mostrarStockAlertModal(mensaje, titulo = null) {
    const tituloElement = document.getElementById('modal-titulo-stock');
    const mensajeElement = document.getElementById('modal-mensaje-stock');
    
    if (titulo !== null) {
        tituloElement.textContent = titulo;
    }
    mensajeElement.textContent = mensaje;
    
    document.getElementById('stock-alert-modal').style.display = 'flex'; 
}

function ocultarStockAlertModal() {
    document.getElementById('stock-alert-modal').style.display = 'none';
}

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

document.addEventListener('DOMContentLoaded', function() {
    const tasaInput = document.getElementById('tasa-actual');
    if (tasaInput) {
        tasaDelDia = parseFloat(tasaInput.value) || 36.50;
    } else {
        tasaDelDia = 36.50;
    }
    
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
    
    const buscarInput = document.getElementById('buscar-producto');
    if (buscarInput) {
        buscarInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarProducto();
            }
        });
    }
    const btnDescargaModal = document.getElementById('btn-descargar-pdf-modal');
    if (btnDescargaModal) {
        btnDescargaModal.addEventListener('click', () => {
            if (docGlobalParaDescarga) {
                docGlobalParaDescarga.save(nombreArchivoGlobal);
                ocultarPdfModal(); 
            }
        });
    }
});

async function buscarProducto() {
    const busqueda = document.getElementById('buscar-producto').value.trim();
    if (!busqueda) {
        mostrarStockAlertModal('Por favor, ingrese un código o nombre de producto para buscar.');
        return;
    }

    try {
        const response = await fetch(`${BASE_URL}?r=buscar-producto&q=${encodeURIComponent(busqueda)}`);
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
        const imagenUrl = producto.imagen 
            ? `${BASE_URL}img/productos/${encodeURIComponent(producto.imagen)}`
            : `${BASE_URL}img/productos/default.png`;

        html += `
            <div class="producto-item ${estaAgotado ? 'producto-agotado' : ''}" 
                onclick="${estaAgotado ? '' : `agregarAlCarrito(${producto.id_producto}, '${nombreSeguro}', ${producto.precio_usd}, ${producto.cantidad})`}">
                <div class="producto-imagen">
                    <img src="${imagenUrl}" alt="${producto.nombre_producto}" onerror="this.src='${BASE_URL}img/default-product.png'">
                </div>
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

function agregarAlCarrito(id, nombre, precio, stock) {
    if (stock <= 0) {
        mostrarStockAlertModal('Producto agotado. No se puede agregar al carrito.');
        return;
    }

    const productoExistente = carrito.find(p => p.id === id);
    if (productoExistente) {
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
            stock: parseInt(stock)
        });
    }
    
    actualizarCarrito();
    
    const buscarInput = document.getElementById('buscar-producto');
    const resultadosDiv = document.getElementById('resultados-busqueda');
    if (buscarInput) buscarInput.value = '';
    if (resultadosDiv) resultadosDiv.innerHTML = '';
}

function actualizarCantidad(id, nuevaCantidad) {
    const producto = carrito.find(p => p.id === id);
    if (producto) {
        nuevaCantidad = parseInt(nuevaCantidad);
        if (isNaN(nuevaCantidad) || nuevaCantidad <= 0) {
            eliminarDelCarrito(id);
            return;
        }
        if (nuevaCantidad > producto.stock) {
            mostrarStockAlertModal(`No hay suficiente stock disponible para "${producto.nombre}". Solo quedan ${producto.stock} unidades.`);
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
        actualizarCarrito();
    }
}

function validarDatosParaPDF() {
    const carrito = obtenerCarrito();
    if (carrito.length === 0) {
        return { valido: false, mensaje: 'El carrito está vacío' };
    }
    
    if (document.getElementById('paso-pago').style.display !== 'none') {
        const metodoPagoSeleccionado = document.querySelector('input[name="metodo_pago"]:checked');
        if (!metodoPagoSeleccionado) {
            return { valido: false, mensaje: 'Seleccione un método de pago' };
        }
        
        const metodo = metodoPagoSeleccionado.value;
        const referenciaInput = document.querySelector('input[name="referencia_pago"]');
        if (['transferencia', 'pago_movil'].includes(metodo)) {
            if (!referenciaInput || !referenciaInput.value.trim()) {
                return { valido: false, mensaje: 'Ingrese la referencia de pago' };
            }
        }
    }
    
    return { valido: true };
}

function actualizarResumenPedido() {
    const carritoData = obtenerCarrito(); 
    const resumenContainer = document.getElementById('resumen-pedido');
    if (!resumenContainer) {
        console.error('No se encontró el contenedor del resumen');
        return;
    }
    
    const tasa = parseFloat(document.getElementById('tasa-actual').value) || tasaDelDia;
    let html = '';
    let subtotalUSD = 0;
    
    if (carritoData.length === 0) {
        html = '<div class="alert alert-warning">No hay productos en el carrito</div>';
    } else {
        carritoData.forEach(item => {
            const subtotalItemUSD = item.precio * item.cantidad;
            const subtotalItemBS = subtotalItemUSD * tasa;
            subtotalUSD += subtotalItemUSD;
            
            html += `
                <div class="producto-resumen" style="border-bottom: 1px solid #eee; padding: 8px 0;">
                    <div style="font-weight: bold;">${item.nombre}</div>
                    <div style="font-size: 0.9em; color: #666;">
                        ${item.cantidad} x $${item.precio.toFixed(2)} = 
                        $${subtotalItemUSD.toFixed(2)} (${subtotalItemBS.toFixed(2)} Bs)
                    </div>
                </div>
            `;
        });
    }
    
    const ivaUSD = subtotalUSD * 0.16;
    const totalUSD = subtotalUSD + ivaUSD;
    const totalBS = totalUSD * tasa;
    const subtotalBS = subtotalUSD * tasa;
    
    resumenContainer.innerHTML = html;
    
    const elementos = {
        'resumen-subtotal-usd': formatearMoneda(subtotalUSD),
        'resumen-subtotal-bs': formatearMoneda(subtotalBS, "VES"),
        'resumen-iva-usd': formatearMoneda(ivaUSD),
        'resumen-total-usd': formatearMoneda(totalUSD),
        'resumen-total-bs': formatearMoneda(totalBS, "VES")
    };
    
    Object.keys(elementos).forEach(id => {
        const elemento = document.getElementById(id);
        if (elemento) {
            elemento.textContent = elementos[id];
        } else {
            console.error(`Elemento no encontrado: ${id}`);
        }
    });
}

function mostrarPasoPago() {
    const pasoCarrito = document.getElementById('paso-carrito');
    const pasoPago = document.getElementById('paso-pago');
    if (pasoCarrito) pasoCarrito.style.display = 'none';
    if (pasoPago) pasoPago.style.display = 'grid';
    actualizarResumenPedido();
}

function volverAlCarrito() {
    document.getElementById('paso-pago').style.display = 'none';
    document.getElementById('paso-carrito').style.display = 'grid';
}

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

    carrito.forEach(producto => {
        subtotalUsd += producto.precio * producto.cantidad;
    });
    const subtotalBs = subtotalUsd * tasaDelDia;
    const ivaUsd = subtotalUsd * 0.16;
    const totalUsd = subtotalUsd + ivaUsd;
    const totalBs = totalUsd * tasaDelDia;

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

function obtenerDatosParaFactura() {
    const carritoData = obtenerCarrito();
    const tasa = parseFloat(document.getElementById('tasa-actual').value);
    
    let subtotalUSD = 0;
    carritoData.forEach(item => {
        subtotalUSD += item.precio * item.cantidad;
    });
    
    const ivaUSD = subtotalUSD * 0.16;
    const totalUSD = subtotalUSD + ivaUSD;
    const totalBS = totalUSD * tasa;
    
    return {
        carrito: carritoData,
        subtotalUSD: subtotalUSD,
        ivaUSD: ivaUSD,
        totalUSD: totalUSD,
        totalBS: totalBS,
        tasa: tasa
    };
}

async function procesarVentaCompleta() {
    // VALIDACIONES
    if (carrito.length === 0) {
        mostrarStockAlertModal('El carrito está vacío.');
        return;
    }

    const metodoPago = document.querySelector('input[name="metodo_pago"]:checked');
    if (!metodoPago) {
        mostrarStockAlertModal('Seleccione un método de pago.');
        return;
    }

    const metodo = metodoPago.value;
    const referenciaInput = document.querySelector('input[name="referencia_pago"]');
    if (['transferencia', 'pago_movil'].includes(metodo) && (!referenciaInput || !referenciaInput.value.trim())) {
        mostrarStockAlertModal('Ingrese la referencia de pago.');
        return;
    }

    const clienteSelect = document.querySelector('select[name="id_cliente"]');
    const clienteNombre = clienteSelect?.options[clienteSelect.selectedIndex]?.text?.trim() || 'Cliente ocasional';
    const datosCalculo = obtenerDatosParaFactura();

    const datosFacturaParaPDF = {
        numeroFactura: 'ERROR_NO_ASIGNADO', 
        fecha: new Date().toLocaleDateString('es-ES'),
        cliente: {
            nombre: clienteNombre,
            idCliente: clienteSelect?.value || 'N/A'
        },
        items: carrito.map(item => ({
            nombre: item.nombre,
            cantidad: item.cantidad,
            precio: item.precio
        })),
        subtotalUSD: datosCalculo.subtotalUSD || 0,
        ivaUSD: datosCalculo.ivaUSD || 0,
        totalUSD: datosCalculo.totalUSD || 0,
        subtotalBS: (datosCalculo.subtotalUSD || 0) * (datosCalculo.tasa || 0),
        totalBS: datosCalculo.totalBS || 0,
        tasa: datosCalculo.tasa || 0,
        metodoPago: metodo,
        referencia: referenciaInput?.value || 'N/A'
    };

    const formData = new FormData();
    formData.append('csrf', document.querySelector('input[name="csrf"]').value);
    formData.append('tasa', tasaDelDia);
    formData.append('metodo_pago', metodo);
    formData.append('referencia_pago', referenciaInput?.value || '');
    formData.append('id_cliente', clienteSelect?.value || '');
    formData.append('carrito', JSON.stringify(carrito));

    try {
        mostrarStockAlertModal('Procesando venta...', '⏳ Enviando');
        
        const response = await fetch(BASE_URL + '?r=procesar-venta', {
            method: 'POST',
            body: formData
        });

        const resultado = await response.json();

        // --- 3. REACCIÓN a la respuesta del servidor ---
        if (resultado.success && resultado.id_venta) {
    
            datosFacturaParaPDF.numeroFactura = resultado.id_venta;
            
            try {
                generadorFactura.generarFactura(datosFacturaParaPDF);
            } catch (pdfError) {
                console.error('Error generando PDF:', pdfError);
                alert('¡Venta guardada! Pero hubo un error al generar el PDF.');
            }
            
            mostrarStockAlertModal('Venta procesada exitosamente.', '✅ Éxito');
            carrito = [];
            actualizarCarrito();
            volverAlCarrito();
            const form = document.getElementById('form-venta');
            if (form) form.reset();
            const grupoReferencia = document.getElementById('grupo-referencia');
            if (grupoReferencia) grupoReferencia.style.display = 'none';

        } else {
            // El servidor reportó un error
            mostrarStockAlertModal(`Error: ${resultado.error || 'No se pudo guardar la venta.'}`, '❌ Error');
        }
    } catch (error) {
        console.error('Error al enviar venta:', error);
        mostrarStockAlertModal('Error de conexión. La venta no se guardó.', '❌ Error');
    }
}

async function descargarFacturaHistorial(idVenta) {
    // Mostrar modal de "Cargando"
    mostrarStockAlertModal('Generando PDF...', '⏳ Espere por favor');

    try {
        // Buscar los datos de la factura en el servidor
        const response = await fetch(`${BASE_URL}?r=get-datos-factura-json&id=${idVenta}`);
        
        if (!response.ok) {
            throw new Error('Error al obtener los datos de la factura.');
        }

        const datosFactura = await response.json();

        if (datosFactura.error) {
            throw new Error(datosFactura.error);
        }

        // Llamar al generador de PDF
        if (typeof generadorFactura !== 'undefined') {
            generadorFactura.generarFactura(datosFactura);
        } else {
            alert('Error: El generador de PDF no está cargado.');
        }

    } catch (error) {
        console.error('Error:', error);
        mostrarStockAlertModal(error.message, '❌ Error');
        
    } finally {
        // Ocultar el modal de "Cargando" 
        setTimeout(() => {
            if (typeof ocultarStockAlertModal !== 'undefined') {
                ocultarStockAlertModal();
            }
        }, 500); 
    }
}

// --- Variables y Funciones Globales para el Modal PDF ---
let docGlobalParaDescarga = null;
let nombreArchivoGlobal = 'factura.pdf';

/*Muestra el modal con la vista previa del PDF.*/
function mostrarPdfEnModal(doc, nombreArchivo) {
    // Genera el PDF como un string de datos
    const pdfDataUri = doc.output('datauristring');
    
    const modal = document.getElementById('pdf-preview-modal');
    const iframe = document.getElementById('pdf-iframe');
    
    if (modal && iframe) {
        iframe.src = pdfDataUri; 
        modal.style.display = 'flex'; 
    }

    // Guarda el objeto 'doc' y el nombre para el botón de descarga
    docGlobalParaDescarga = doc;
    nombreArchivoGlobal = nombreArchivo;
}

function ocultarPdfModal() {
    const modal = document.getElementById('pdf-preview-modal');
    const iframe = document.getElementById('pdf-iframe');
    
    if (modal && iframe) {
        modal.style.display = 'none';
        iframe.src = '';
    }
    docGlobalParaDescarga = null;
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
window.validarDatosParaPDF = validarDatosParaPDF;
window.obtenerDatosParaFactura = obtenerDatosParaFactura;
window.procesarVentaCompleta = procesarVentaCompleta;