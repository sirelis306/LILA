<?php
$esEdicion = isset($producto) && $producto;
$titulo = $esEdicion ? 'Editar Producto' : 'Agregar Nuevo Producto';
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

<div class="content-body">
    <div class="container">    
        </div>

        <div class="card-product-main formulario-inventario">
            <!-- Panel Lateral para Imagen -->
            <div class="sidebar-image-upload">
                <div class="image-placeholder" id="image-preview">
                    <i class="fi fi-rr-camera"></i>
                </div>
                
                <?php if ($esEdicion && !empty($producto['imagen'])): ?>
                    <img src="<?= BASE_URL ?>img/productos/<?= $producto['imagen'] ?>" 
                         alt="Imagen actual" class="current-image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;">
                <?php endif; ?>

                <label for="file-input" class="btn-upload-image">
                    <i class="fi fi-rr-upload"></i> Subir Imagen
                </label>

                <?php if ($esEdicion && !empty($producto['imagen'])): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="mantener_imagen" value="1" checked>
                        <span>Mantener imagen actual</span>
                    </label>
                    <input type="hidden" name="imagen_actual" value="<?= $producto['imagen'] ?>">
                <?php endif; ?>

                <small class="form-text-small">Formatos: JPG, PNG (Máx. 2MB)</small>
            </div>

            <!-- Contenido Principal del Formulario -->
            <div class="main-form-content">
                <form method="POST" action="<?= BASE_URL ?>?r=guardar-producto" enctype="multipart/form-data" class="producto-form">
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                    <?php endif; ?>

                    <input type="file" name="imagen" id="file-input" accept="image/*" style="display: none;">
                    <?php if ($esEdicion && !empty($producto['imagen'])): ?>
                        <input type="hidden" name="imagen_actual" value="<?= $producto['imagen'] ?>">
                    <?php endif; ?>

                    <div class="form-grid" style="gap: 15px; margin-bottom: 10px;">
                        <!-- Fila 1: Nombre + Código -->
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="form-label">Nombre del Producto *</label>
                            <input type="text" name="nombre_producto" class="form-input" 
                                value="<?= htmlspecialchars($producto['nombre_producto'] ?? '') ?>" 
                                required maxlength="50">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Código de Barras *</label>
                            <input type="text" name="codigo_barras" class="form-input" 
                                value="<?= htmlspecialchars($producto['codigo_barras'] ?? '') ?>" 
                                required maxlength="20">
                        </div>

                        <!-- Fila 2: Descripción (ocupa 2 columnas) -->
                        <div class="form-group form-group-descripcion">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-input" rows="2" 
                                    maxlength="300"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                        </div>

                        <!-- Fila 3: Tamaño + Stock -->
                        <div class="form-group">
                            <label class="form-label">Tamaño</label>
                            <input type="text" name="tamano" class="form-input" 
                                value="<?= htmlspecialchars($producto['tamano'] ?? '') ?>" 
                                maxlength="50" placeholder="Ej: 300ml, 1L, Grande, etc.">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Stock Inicial *</label>
                            <input type="number" name="cantidad" class="form-input" 
                                value="<?= $producto['cantidad'] ?? '0' ?>" 
                                min="0" required>
                            <small class="form-text-small">Cantidad disponible en inventario</small>
                        </div>

                        <!-- Fila 4: Precios CON CLASE precio-input -->
                        <div class="form-group">
                            <label class="form-label">Precio en Bolívares *</label>
                            <input type="text" name="precio_bs" class="form-input precio-input" 
                                value="<?= isset($producto['precio_bs']) ? number_format($producto['precio_bs'], 2, ',', '.') : '0,00' ?>" 
                                required placeholder="0,00">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Precio en Dólares *</label>
                            <input type="text" name="precio_usd" class="form-input precio-input" 
                                value="<?= isset($producto['precio_usd']) ? number_format($producto['precio_usd'], 2, ',', '.') : '0,00' ?>" 
                                required placeholder="0,00">
                        </div>
                    </div>

                    <input type="hidden" id="tasa-del-dia" value="<?= $tasaHoy['tasa'] ?? '36.5' ?>">

                    <div class="form-actions-bottom">
                        <button type="submit" class="btn btn-primary">
                            <i class="fi fi-rr-check"></i> 
                            <?= $esEdicion ? 'Actualizar Producto' : 'Guardar Producto' ?>
                        </button>
                        <a href="<?= BASE_URL ?>?r=inventario" class="btn btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>js/inventario.js"></script> 

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>