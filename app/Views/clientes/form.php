<?php
$esEdicion = isset($cliente) && $cliente;
$titulo = $esEdicion ? 'Editar Cliente' : 'Agregar Nuevo Cliente';
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

<div class="content-body">
    <div class="container">
        <div class="card-product-main formulario-inventario">
            <div class="main-form-content">
                <form method="POST" action="<?= BASE_URL ?>?r=guardar-cliente" class="producto-form">
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <?php endif; ?>
                    
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

                    <div class="form-grid" style="gap: 15px; margin-bottom: 10px;">
                        <!-- Fila 1: Nombre + Apellido -->
                        <div class="form-group">
                            <label class="form-label">Nombre <span style="color: red;">*</span></label>
                            <input type="text" name="nombre" class="form-input" 
                                value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>" 
                                required maxlength="50" placeholder="Nombre del cliente">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Apellido <span style="color: red;">*</span></label>
                            <input type="text" name="apellido" class="form-input" 
                                value="<?= htmlspecialchars($cliente['apellido'] ?? '') ?>" 
                                required maxlength="50" placeholder="Apellido del cliente">
                        </div>

                        <!-- Fila 2: Teléfono con código de país -->
                        <div class="form-group">
                            <label class="form-label">Teléfono <span style="color: red;">*</span></label>
                            <div style="display: flex; gap: 8px; align-items: stretch;">
                                <select name="codigo_pais" id="codigo_pais" class="form-input" style="width: 220px; flex-shrink: 0; cursor: pointer;" required>
                                    <option value="">Seleccione país</option>
                                    <option value="+58" data-flag="🇻🇪">🇻🇪 Venezuela (+58)</option>
                                    <option value="+1" data-flag="🇺🇸">🇺🇸 Estados Unidos (+1)</option>
                                    <option value="+52" data-flag="🇲🇽">🇲🇽 México (+52)</option>
                                    <option value="+57" data-flag="🇨🇴">🇨🇴 Colombia (+57)</option>
                                    <option value="+51" data-flag="🇵🇪">🇵🇪 Perú (+51)</option>
                                    <option value="+56" data-flag="🇨🇱">🇨🇱 Chile (+56)</option>
                                    <option value="+54" data-flag="🇦🇷">🇦🇷 Argentina (+54)</option>
                                    <option value="+55" data-flag="🇧🇷">🇧🇷 Brasil (+55)</option>
                                    <option value="+593" data-flag="🇪🇨">🇪🇨 Ecuador (+593)</option>
                                    <option value="+595" data-flag="🇵🇾">🇵🇾 Paraguay (+595)</option>
                                    <option value="+591" data-flag="🇧🇴">🇧🇴 Bolivia (+591)</option>
                                    <option value="+598" data-flag="🇺🇾">🇺🇾 Uruguay (+598)</option>
                                    <option value="+34" data-flag="🇪🇸">🇪🇸 España (+34)</option>
                                    <option value="+44" data-flag="🇬🇧">🇬🇧 Reino Unido (+44)</option>
                                    <option value="+33" data-flag="🇫🇷">🇫🇷 Francia (+33)</option>
                                    <option value="+49" data-flag="🇩🇪">🇩🇪 Alemania (+49)</option>
                                    <option value="+39" data-flag="🇮🇹">🇮🇹 Italia (+39)</option>
                                    <option value="+86" data-flag="🇨🇳">🇨🇳 China (+86)</option>
                                    <option value="+81" data-flag="🇯🇵">🇯🇵 Japón (+81)</option>
                                    <option value="+91" data-flag="🇮🇳">🇮🇳 India (+91)</option>
                                </select>
                                <input type="text" name="telefono" id="telefono" class="form-input" 
                                    value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>" 
                                    required maxlength="20" placeholder="Número de teléfono" 
                                    style="flex: 1;">
                            </div>
                            <input type="hidden" name="telefono_completo" id="telefono_completo">
                        </div>

                        <!-- Fila 3: Dirección -->
                        <div class="form-group form-group-descripcion">
                            <label class="form-label">Dirección</label>
                            <textarea name="direccion" class="form-input" rows="3" 
                                    maxlength="200" placeholder="Dirección del cliente"><?= htmlspecialchars($cliente['direccion'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions-bottom">
                        <button type="submit" class="btn btn-primary">
                            <i class="fi fi-rr-check"></i> 
                            <?= $esEdicion ? 'Actualizar Cliente' : 'Guardar Cliente' ?>
                        </button>
                        <a href="<?= BASE_URL ?>?r=clientes" class="btn btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codigoPais = document.getElementById('codigo_pais');
    const telefono = document.getElementById('telefono');
    const telefonoCompleto = document.getElementById('telefono_completo');
    
    // Función para actualizar el teléfono completo
    function actualizarTelefonoCompleto() {
        const codigo = codigoPais.value;
        const numero = telefono.value.trim();
        if (codigo && numero) {
            telefonoCompleto.value = codigo + numero;
        } else {
            telefonoCompleto.value = numero || '';
        }
    }
    
    // Lista de códigos de país ordenados de más largo a más corto para mejor detección
    const codigosPais = ['+593', '+595', '+591', '+598', '+58', '+52', '+57', '+51', '+56', '+54', '+55', '+34', '+44', '+33', '+49', '+39', '+86', '+81', '+91', '+1'];
    
    // Función para extraer código de país del teléfono
    function extraerCodigoPais(telefonoStr) {
        if (!telefonoStr || !telefonoStr.startsWith('+')) {
            return { codigo: '+58', numero: telefonoStr }; // Por defecto Venezuela
        }
        
        for (let codigo of codigosPais) {
            if (telefonoStr.startsWith(codigo)) {
                return {
                    codigo: codigo,
                    numero: telefonoStr.substring(codigo.length)
                };
            }
        }
        
        // Si no se encuentra, asumir Venezuela
        return { codigo: '+58', numero: telefonoStr };
    }
    
    // Si hay un teléfono existente, intentar extraer el código
    <?php if (isset($cliente['telefono']) && !empty($cliente['telefono'])): ?>
        const telefonoExistente = '<?= htmlspecialchars($cliente['telefono']) ?>';
        const { codigo, numero } = extraerCodigoPais(telefonoExistente);
        codigoPais.value = codigo;
        telefono.value = numero;
        actualizarTelefonoCompleto();
    <?php else: ?>
        // Si es nuevo cliente, establecer Venezuela por defecto
        codigoPais.value = '+58';
    <?php endif; ?>
    
    // Event listeners
    codigoPais.addEventListener('change', actualizarTelefonoCompleto);
    telefono.addEventListener('input', function() {
        // Solo permitir números
        this.value = this.value.replace(/[^0-9]/g, '');
        actualizarTelefonoCompleto();
    });
    
    // Validar antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!codigoPais.value) {
            e.preventDefault();
            alert('Por favor seleccione un país');
            codigoPais.focus();
            return false;
        }
        actualizarTelefonoCompleto();
    });
});
</script>
