<?php
$titulo = "Gestión de Usuarios";
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

<div class="content-body">
    <div class="container">
        <!-- Header de la sección -->
        <div class="usuarios-header-section">
            <div class="usuarios-header-content">
                <div class="usuarios-header-icon">
                    <i class="fi fi-rr-users"></i>
                </div>
                <div class="usuarios-header-text">
                    <h2 class="usuarios-main-title">Gestión de Usuarios</h2>
                    <p class="usuarios-subtitle">Administra los usuarios del sistema, edita sus datos y gestiona sus contraseñas</p>
                </div>
                <div class="usuarios-header-action">
                    <a href="<?= BASE_URL ?>?r=crear-usuario" class="btn btn-primary btn-crear-usuario">
                        <i class="fi fi-rr-user-add"></i>
                        <span>Crear Usuario</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="inventario-table-container">
            <?php if (empty($usuarios)): ?>
                <div class="alert alert-info">
                    No hay usuarios registrados en el sistema.
                </div>
            <?php else: ?>
                <table class="inventario-table usuarios-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <span class="usuario-id">#<?= htmlspecialchars($u['id_usuario']) ?></span>
                                </td>
                                <td>
                                    <div class="usuario-username">
                                        <i class="fi fi-rr-at" style="margin-right: 6px; color: var(--texto-claro);"></i>
                                        <?= htmlspecialchars($u['usuario']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="rol-badge rol-<?= htmlspecialchars($u['rol']) ?>">
                                        <i class="fi fi-rr-<?= $u['rol'] === 'administrador' ? 'shield-check' : 'user' ?>"></i>
                                        <?= htmlspecialchars(ucfirst($u['rol'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>?r=editar-usuario&id=<?= $u['id_usuario'] ?>" 
                                       class="btn btn-secondary btn-sm btn-editar-usuario">
                                        <i class="fi fi-rr-edit"></i>
                                        <span>Editar</span>
                                    </a>
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


