// Funciones para manejar formularios de productos
document.addEventListener('DOMContentLoaded', function() {
    inicializarFormularioProductos();
});

function inicializarFormularioProductos() {
    manejarPreciosConFormato();
    manejarUploadImagen();
}

function manejarPreciosConFormato() {
    const precioInputs = document.querySelectorAll('.precio-input');
    
    if (precioInputs.length === 0) {
        console.log('No se encontraron inputs de precio con la clase precio-input');
        return;
    }
    
    // Función para formatear número con coma decimal
    function formatearPrecio(input) {
        let value = input.value.replace(/[^\d,]/g, '');
        
        if (value === '' || value === '0') {
            input.value = '0,00';
            return;
        }
        
        // Si no tiene coma, agregar ,00
        if (!value.includes(',')) {
            value = value + ',00';
        }
        
        // Asegurar 2 decimales
        const partes = value.split(',');
        if (partes[1]) {
            partes[1] = partes[1].padEnd(2, '0').substring(0, 2);
        } else {
            partes[1] = '00';
        }
        
        input.value = partes.join(',');
    }
    
    // Validar input en tiempo real
    function validarInputPrecio(e) {
        const input = e.target;
        let value = input.value;
        
        // Permitir solo números y una coma
        value = value.replace(/[^\d,]/g, '');
        
        // Solo una coma
        const comas = value.split(',').length - 1;
        if (comas > 1) {
            value = value.replace(/,$/, '');
        }
        
        input.value = value;
    }
    
    // Aplicar eventos
    precioInputs.forEach(input => {
        input.addEventListener('input', validarInputPrecio);
        input.addEventListener('blur', function() {
            formatearPrecio(this);
        });
        
        // Formatear al cargar la página
        formatearPrecio(input);
    });
    
    // Procesar antes de enviar el formulario
    const form = document.querySelector('.producto-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            precioInputs.forEach(input => {
                // Convertir 0,00 a 0.00 para el envío
                input.value = input.value.replace(',', '.');
            });
        });
    }
}

function manejarUploadImagen() {
    const checkbox = document.querySelector('input[name="mantener_imagen"]');
    const fileInput = document.getElementById('file-input'); // ← CAMBIADO
    const uploadBtn = document.querySelector('.btn-upload-image');
    
    if (!uploadBtn || !fileInput) {
        console.log('Elementos de upload no encontrados');
        return;
    }
    
    // Checkbox para mantener imagen
    if (checkbox) {
        checkbox.addEventListener('change', function() {
            fileInput.disabled = this.checked;
            console.log('Checkbox cambiado. Input file disabled:', this.checked);
        });
    }
    
    // Botón de subir imagen - SOLO UN EVENTO
    uploadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (!fileInput.disabled) {
            fileInput.click();
            console.log('Abriendo selector de archivos...');
        }
    });
    
    // Evento cuando se selecciona archivo
    fileInput.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            console.log('Imagen seleccionada:', this.files[0].name);
            mostrarVistaPrevia(this.files[0]);
        }
    });
}

function mostrarVistaPrevia(file) {
    if (!file) return;
    
    // Validar tipo de archivo
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('Formato no válido. Use JPG, PNG, GIF o WebP.');
        return;
    }
    
    // Validar tamaño (2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('La imagen es muy grande. Máximo 2MB.');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const imagePreview = document.getElementById('image-preview');
        const currentImage = document.querySelector('.current-image');
        
        if (currentImage) {
            // Si ya hay una imagen, reemplazarla
            currentImage.src = e.target.result;
        } else if (imagePreview) {
            // Si hay contenedor de preview, actualizarlo
            imagePreview.innerHTML = `
                <img src="${e.target.result}" alt="Vista previa" 
                     style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                <div style="font-size: 12px; color: #666; margin-top: 5px;">Vista previa</div>
            `;
        }
        
        // Desmarcar "Mantener imagen actual" si está marcado
        const mantenerCheckbox = document.querySelector('input[name="mantener_imagen"]');
        if (mantenerCheckbox && mantenerCheckbox.checked) {
            mantenerCheckbox.checked = false;
            const fileInput = document.getElementById('file-input');
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

// Hacer funciones disponibles globalmente
window.mostrarVistaPrevia = mostrarVistaPrevia;