<?php
$titulo = "Gestión de Usuarios";
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

<div class="content-body">
    <div class="container">
        <div class="card-product-main">
            <div class="main-form-content">
                <h3 class="card-title">Usuarios del sistema</h3>
                <p class="card-label">Administra los usuarios y restablece contraseñas cuando sea necesario.</p>

                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['id_usuario']) ?></td>
                                    <td><?= htmlspecialchars($u['nombre'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($u['usuario']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($u['rol'])) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>?r=editar-usuario&id=<?= $u['id_usuario'] ?>" class="btn btn-secondary btn-sm">
                                            Editar / Cambiar contraseña
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>


