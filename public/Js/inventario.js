console.log('Inventario.js cargado');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - Inicializando formulario');
    inicializarFormularioProductos();
});

function inicializarFormularioProductos() {
    manejarPreciosConFormato();
    manejarUploadImagen();
}

function manejarPreciosConFormato() {
    const precioBsInput = document.querySelector('input[name="precio_bs"]');
    const precioUsdInput = document.querySelector('input[name="precio_usd"]');
    const tasaInput = document.getElementById('tasa-del-dia');
    
    console.log('precioBsInput:', precioBsInput);
    console.log('precioUsdInput:', precioUsdInput);
    console.log('tasaInput:', tasaInput);

    if (!precioBsInput || !precioUsdInput || !tasaInput) {
        console.log('No se encontraron todos los inputs de precio o tasa.');
        return;
    }

    let isUpdatingBs = false; // Bandera para evitar bucle en BS
    let isUpdatingUsd = false; // Bandera para evitar bucle en USD

    // Función para formatear número con coma decimal y 2 decimales
    function formatearPrecio(input) {
        let value = input.value.replace(/[^\d,]/g, '');
        
        if (value === '') {
            input.value = '0,00';
            return;
        }
        
        // Si no tiene coma, agregar ,00
        if (!value.includes(',')) {
            value = value + ',00';
        }
        
        // Asegurar 2 decimales
        const partes = value.split(',');
        let parteEntera = partes[0].replace(/^0+(?=\d)/, ''); // Eliminar ceros iniciales si no es '0'
        if (parteEntera === '') parteEntera = '0'; // Asegurar que no quede vacío si solo había ceros
        
        partes[1] = (partes[1] || '00').padEnd(2, '0').substring(0, 2);
        
        input.value = parteEntera + ',' + partes[1];
    }

    // Validar input en tiempo real (solo números y una coma)
    function validarInputPrecio(e) {
        const input = e.target;
        let value = input.value;
        
        // Si el campo está vacío, permitirlo para borrar
        if (value === '') return;
        
        // Permitir solo números y una coma
        value = value.replace(/[^\d,]/g, '');
        
        // Asegurar que solo haya una coma
        const partes = value.split(',');
        if (partes.length > 2) {
            value = partes[0] + ',' + partes.slice(1).join('');
        }
        
        // Limitar a 2 decimales después de la coma
        if (partes.length === 2 && partes[1].length > 2) {
            value = partes[0] + ',' + partes[1].substring(0, 2);
        }
        
        input.value = value;
    }

    // Calcular precios en tiempo real
    function calcularPrecios(sourceInput) {
        const tasa = parseFloat(tasaInput.value) || 36.5;
        
        if (sourceInput === precioUsdInput && !isUpdatingUsd) {
            // Se editó el campo USD
            isUpdatingBs = true; // Establecer bandera para Bs
            const usdRaw = precioUsdInput.value;
            const usdValue = usdRaw ? parseFloat(usdRaw.replace(',', '.')) : 0;
            const nuevoBs = (usdValue * tasa).toFixed(2).replace('.', ',');
            
            if (precioBsInput.value !== nuevoBs) { // Solo actualizar si es diferente para minimizar eventos
                precioBsInput.value = nuevoBs;
            }
            isUpdatingBs = false; // Resetear bandera
            
        } else if (sourceInput === precioBsInput && !isUpdatingBs) {
            // Se editó el campo Bs
            isUpdatingUsd = true; // Establecer bandera para Usd
            const bsRaw = precioBsInput.value;
            const bsValue = bsRaw ? parseFloat(bsRaw.replace(',', '.')) : 0;
            const nuevoUsd = (bsValue / tasa).toFixed(2).replace('.', ',');
            
            if (precioUsdInput.value !== nuevoUsd) { // Solo actualizar si es diferente
                precioUsdInput.value = nuevoUsd;
            }
            isUpdatingUsd = false; // Resetear bandera
        }
    }

    // Aplicar eventos a ambos inputs
    [precioBsInput, precioUsdInput].forEach(input => {
        // Evento 'input' para validación y cálculo en tiempo real
        input.addEventListener('input', function() {
            validarInputPrecio({target: this});
            calcularPrecios(this); // Pasar el input que disparó el evento
        });
        
        // Evento 'blur' para formatear el número final al salir del campo
        input.addEventListener('blur', function() {
            formatearPrecio(this);
        });
        
        // Formatear al cargar la página (para valores iniciales)
        formatearPrecio(input);
    });

    // Escuchar cambios en la tasa del día si fuera interactiva
    if (tasaInput) {
        tasaInput.addEventListener('input', function() {
            // Recalcular ambos si se cambia la tasa y alguno de los campos tiene valor
            if (precioUsdInput.value !== '0,00') {
                calcularPrecios(precioUsdInput);
            } else if (precioBsInput.value !== '0,00') {
                calcularPrecios(precioBsInput);
            }
        });
    }

    // Procesar antes de enviar el formulario
    const form = document.querySelector('.producto-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Convertir 0,00 a 0.00 para el envío
            [precioBsInput, precioUsdInput].forEach(input => {
                input.value = input.value.replace(',', '.');
            });
            const mantenerImagenCheckbox = document.querySelector('input[name="mantener_imagen"]');
            const fileInput = document.getElementById('file-input');
            if (mantenerImagenCheckbox && mantenerImagenCheckbox.checked && fileInput && fileInput.files.length === 0) {
                 fileInput.disabled = true; // Deshabilitar para que no se envíe vacío si no hay nueva imagen
            }
        });
    }
}

function manejarUploadImagen() {
    const checkbox = document.querySelector('input[name="mantener_imagen"]');
    const fileInput = document.getElementById('file-input');
    const uploadBtn = document.querySelector('.btn-upload-image');
    
    if (!uploadBtn || !fileInput) {
        console.log('Elementos de upload no encontrados');
        return;
    }
    
    // Checkbox para mantener imagen
    if (checkbox) {
        checkbox.addEventListener('change', function() {
            uploadBtn.style.opacity = this.checked ? '0.5' : '1';
            uploadBtn.style.pointerEvents = this.checked ? 'none' : 'auto';
            fileInput.disabled = this.checked; // Deshabilitar input file si se mantiene la imagen
        });
    }
    
    // Botón de subir imagen
    uploadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        // Si el checkbox está marcado o el input file está deshabilitado, no permitir subir
        if (checkbox && checkbox.checked || fileInput.disabled) {
            return;
        }
        fileInput.click();
    });
    
    // Evento cuando se selecciona archivo
    fileInput.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            console.log('Imagen seleccionada:', this.files[0].name);
            mostrarVistaPrevia(this.files[0]);
            // Desmarcar checkbox si existe y el input file se ha activado
            if (checkbox && fileInput && !fileInput.disabled) {
                checkbox.checked = false;
                uploadBtn.style.opacity = '1';
                uploadBtn.style.pointerEvents = 'auto';
            }
        }
    });
    // Inicializar estado del botón al cargar (si el checkbox está marcado)
    if (checkbox && checkbox.checked) {
        uploadBtn.style.opacity = '0.5';
        uploadBtn.style.pointerEvents = 'none';
        fileInput.disabled = true;
    }
}

function mostrarVistaPrevia(file) {
    if (!file) return;
    
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('Formato no válido. Use JPG, PNG, GIF o WebP.');
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        alert('La imagen es muy grande. Máximo 2MB.');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const imagePreview = document.getElementById('image-preview');
        const currentImageContainer = document.getElementById('current-image-container');
        
        // Ocultar el placeholder
        if (imagePreview) {
            imagePreview.style.display = 'none';
        }
        
        // Crear o actualizar la imagen
        let currentImage = currentImageContainer.querySelector('.current-image');
        if (!currentImage) {
            currentImage = document.createElement('img');
            currentImage.className = 'current-image';
            currentImage.style.width = '100px';
            currentImage.style.height = '100px';
            currentImage.style.objectFit = 'cover';
            currentImage.style.borderRadius = '8px';
            currentImage.style.marginBottom = '20px';
            currentImageContainer.appendChild(currentImage);
        }
        
        currentImage.src = e.target.result;
        currentImage.alt = "Vista previa";
        
        // Manejar el checkbox de mantener imagen
        const mantenerCheckbox = document.querySelector('input[name="mantener_imagen"]');
        const fileInput = document.getElementById('file-input');
        const uploadBtn = document.querySelector('.btn-upload-image');

        if (mantenerCheckbox) {
            mantenerCheckbox.checked = false;
            if (uploadBtn) {
                uploadBtn.style.opacity = '1';
                uploadBtn.style.pointerEvents = 'auto';
            }
            if (fileInput) fileInput.disabled = false;
        }
        
        console.log('Vista previa mostrada correctamente');
    };
    
    reader.onerror = function(error) {
        console.error('Error al leer archivo:', error);
        alert('Error al cargar la imagen');
    };
    
    reader.readAsDataURL(file);
}

// Función para inicializar la vista de imagen al cargar la página
function inicializarVistaImagen() {
    const currentImage = document.querySelector('#current-image-container .current-image');
    const imagePreview = document.getElementById('image-preview');
    
    // Si hay una imagen actual, ocultar el placeholder
    if (currentImage && imagePreview) {
        imagePreview.style.display = 'none';
    }
}

// Modificar la función inicializadora para incluir la inicialización de imagen
function inicializarFormularioProductos() {
    manejarPreciosConFormato();
    manejarUploadImagen();
    inicializarVistaImagen(); // ← Agregar esta línea
}

window.mostrarVistaPrevia = mostrarVistaPrevia;