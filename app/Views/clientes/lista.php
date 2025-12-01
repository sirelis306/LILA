<?php
$titulo = 'Gestión de Clientes';
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

<div class="content-body">
    <div class="container">
        <!-- Barra de búsqueda y acciones -->
        <div class="search-actions-bar">
            <form method="GET" action="<?= BASE_URL ?>" class="search-form">
                <input type="hidden" name="r" value="clientes">

                <div class="form-group-container" style="flex-grow: 1;">
                    <input type="text" 
                        name="q" 
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" 
                        placeholder="Buscar por nombre, apellido, teléfono o dirección" 
                        class="form-input">
                    <button type="submit" class="search-button">
                        <i class="fi fi-rr-search"></i>
                    </button>
                </div>
            </form>
            
            <a href="<?= BASE_URL ?>?r=form-cliente" class="btn-agregar-producto">
                <i class="fi fi-rr-add"></i> Agregar Cliente
            </a>
        </div>

        <!-- Tabla de Clientes -->
        <div class="inventario-table-container">
            <?php if (empty($clientes)): ?>
                <div class="alert alert-info">
                    No hay clientes registrados. 
                    <a href="<?= BASE_URL ?>?r=form-cliente">Agregar el primer cliente</a>
                </div>
            <?php else: ?>
                <table class="inventario-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?= htmlspecialchars($cliente['id_cliente']) ?></td>
                                <td>
                                    <div class="producto-nombre-principal">
                                        <?= htmlspecialchars($cliente['nombre']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($cliente['apellido']) ?></td>
                                <td>
                                    <?php 
                                    $telefono = htmlspecialchars($cliente['telefono']);
                                    // Si el teléfono no empieza con +, agregar código de Venezuela por defecto
                                    if (!empty($telefono) && substr($telefono, 0, 1) !== '+') {
                                        $telefono = '+58' . $telefono;
                                    }
                                    ?>
                                    <a href="tel:<?= $telefono ?>" style="color: var(--purpura-vibrante); text-decoration: none; font-weight: 500;">
                                        <i class="fi fi-rr-phone-call" style="margin-right: 5px;"></i>
                                        <?= $telefono ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="producto-stock-linea">
                                        <?= htmlspecialchars($cliente['direccion']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 10px;">
                                        <a href="<?= BASE_URL ?>?r=form-cliente&id=<?= $cliente['id_cliente'] ?>" 
                                           class="btn btn-sm btn-secondary">
                                            <i class="fi fi-rr-edit"></i> Editar
                                        </a>
                                        <a href="<?= BASE_URL ?>?r=eliminar-cliente&id=<?= $cliente['id_cliente'] ?>" 
                                           class="btn-eliminar" 
                                           onclick="return confirm('¿Estás seguro de eliminar a <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?>?')">
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

