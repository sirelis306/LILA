<?php
$titulo = "Editar Usuario";
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

<div class="content-body">
    <div class="container">
        <div class="card-product-main formulario-inventario">
            <div class="main-form-content">
                <form method="POST" action="<?= BASE_URL ?>?r=actualizar-usuario">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">

                    <div class="form-grid" style="gap: 15px; margin-bottom: 10px;">
                        <div class="form-group form-full-width">
                            <h3 style="margin-bottom: 15px; color: var(--azul-oscuro); border-bottom: 2px solid var(--azul-claro); padding-bottom: 10px;">
                                Datos del Usuario
                            </h3>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-input"
                                value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>"
                                maxlength="100" placeholder="Nombre completo del usuario">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nombre de Usuario <span style="color: red;">*</span></label>
                            <input type="text" name="usuario" class="form-input"
                                value="<?= htmlspecialchars($usuario['usuario']) ?>"
                                required maxlength="50" placeholder="Nombre de usuario para iniciar sesión">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Rol</label>
                            <select name="rol" class="form-input">
                                <option value="administrador" <?= $usuario['rol'] === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                                <option value="empleado" <?= $usuario['rol'] === 'empleado' ? 'selected' : '' ?>>Empleado</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid" style="gap: 15px; margin-top: 30px; margin-bottom: 10px;">
                        <div class="form-group form-full-width">
                            <h3 style="margin-bottom: 15px; color: var(--azul-oscuro); border-bottom: 2px solid var(--azul-claro); padding-bottom: 10px;">
                                Cambiar Contraseña
                            </h3>
                            <p style="color: var(--texto-claro); font-size: 14px; margin-bottom: 15px;">
                                Deja estos campos vacíos si no deseas cambiar la contraseña del usuario.
                            </p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="nueva_contrasena" class="form-input"
                                placeholder="Nueva contraseña (mínimo 6 caracteres)" minlength="6">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" name="confirmar_contrasena" class="form-input"
                                placeholder="Confirma la nueva contraseña" minlength="6">
                        </div>
                    </div>

                    <div class="form-actions-bottom">
                        <button type="submit" class="btn btn-primary">
                            <i class="fi fi-rr-check"></i>
                            Guardar Cambios
                        </button>
                        <a href="<?= BASE_URL ?>?r=usuarios" class="btn btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>


