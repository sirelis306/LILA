<?php
$titulo = 'Inventario de Productos';
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

        <!-- Barra de búsqueda y acciones -->
        <div class="search-actions-bar">
            <div class="search-input-group">
                <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" 
                       placeholder="Buscar por nombre o código..." class="form-input">
                <button type="submit" class="search-button">
                    <i class="fi fi-rr-search"></i>
                </button>
            </div>
            
            <a href="<?= BASE_URL ?>?r=form-producto" class="btn-agregar-producto">
                <i class="fi fi-rr-add"></i> Agregar Producto
            </a>
        </div>

        <!-- Tabla de Productos -->
        <div class="inventario-table-container">
            <?php if (empty($productos)): ?>
                <div class="alert alert-info">
                    No hay productos registrados. 
                    <a href="<?= BASE_URL ?>?r=form-producto">Agregar el primer producto</a>
                </div>
            <?php else: ?>
                <table class="inventario-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Tamaño</th>
                            <th>Stock</th>
                            <th>Precio BS</th>
                            <th>Precio USD</th>
                            <th>Código</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td>
                                    <div class="producto-info-cell">
                                        <?php if ($producto['imagen']): ?>
                                            <img src="<?= BASE_URL ?>img/productos/<?= $producto['imagen'] ?>" 
                                                 alt="<?= $producto['nombre_producto'] ?>" 
                                                 class="producto-imagen">
                                        <?php else: ?>
                                            <div class="producto-imagen" style="display: flex; align-items: center; justify-content: center;">
                                                <i class="fi fi-rr-picture" style="color: #ccc;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="producto-nombre-principal">
                                                <?= htmlspecialchars($producto['nombre_producto']) ?>
                                            </div>
                                            <?php if ($producto['descripcion']): ?>
                                                <div class="producto-stock-linea">
                                                    <?= htmlspecialchars($producto['descripcion']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($producto['tamano']) ?></td>
                                <td>
                                    <?php if ($producto['cantidad'] > 0): ?>
                                        <span class="stock-cantidad"><?= $producto['cantidad'] ?> unidades</span>
                                    <?php else: ?>
                                        <span class="stock-agotado">Agotado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="precio-inventario">
                                        <?= number_format($producto['precio_bs'], 2, ',', '.') ?> Bs
                                    </span>
                                </td>
                                <td>
                                    <span class="precio-inventario">
                                        $<?= number_format($producto['precio_usd'], 2) ?>
                                    </span>
                                </td>
                                <td><code><?= $producto['codigo_barras'] ?></code></td>
                                <td>
                                    <div style="display: flex; gap: 10px;">
                                        <a href="<?= BASE_URL ?>?r=form-producto&id=<?= $producto['id_producto'] ?>" 
                                           class="btn btn-sm btn-secondary">
                                            <i class="fi fi-rr-edit"></i> Editar
                                        </a>
                                        <a href="<?= BASE_URL ?>?r=eliminar-producto&id=<?= $producto['id_producto'] ?>" 
                                           class="btn-eliminar" 
                                           onclick="return confirm('¿Estás seguro de eliminar <?= htmlspecialchars($producto['nombre_producto']) ?>?')">
                                            <i class="fi fi-rr-trash"></i> Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>