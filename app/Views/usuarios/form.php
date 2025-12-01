<?php
$esEdicion = isset($usuario) && isset($usuario['id_usuario']);
$titulo = $esEdicion ? "Editar Usuario" : "Crear Nuevo Usuario";
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

<div class="content-body">
    <div class="container">
        <!-- Header del formulario -->
        <div class="usuario-form-header">
            <div class="usuario-form-header-icon">
                <i class="fi fi-rr-<?= $esEdicion ? 'user-pen' : 'user-add' ?>"></i>
            </div>
            <div class="usuario-form-header-text">
                <h2 class="usuario-form-title"><?= $esEdicion ? 'Editar Usuario' : 'Crear Nuevo Usuario' ?></h2>
                <p class="usuario-form-subtitle">
                    <?= $esEdicion ? 'Modifica la información del usuario y gestiona su contraseña' : 'Completa los datos para crear un nuevo usuario en el sistema' ?>
                </p>
            </div>
        </div>

        <div class="card-product-main formulario-inventario">
            <div class="main-form-content">
                <form method="POST" action="<?= BASE_URL ?>?r=<?= $esEdicion ? 'actualizar-usuario' : 'guardar-usuario' ?>" class="usuario-form">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">
                    <?php endif; ?>

                    <!-- Sección: Datos del Usuario -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <i class="fi fi-rr-user"></i>
                            <h3 class="form-section-title">Datos del Usuario</h3>
                        </div>
                        
                        <div class="form-grid" style="gap: 15px; margin-top: 20px;">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fi fi-rr-at" style="margin-right: 6px; color: var(--purpura-vibrante);"></i>
                                    Nombre de Usuario <span class="required-asterisk">*</span>
                                </label>
                                <input type="text" name="usuario" class="form-input"
                                    value="<?= htmlspecialchars($usuario['usuario'] ?? '') ?>"
                                    required maxlength="50" placeholder="Ej: jperez">
                                <small class="form-text-small">Usado para iniciar sesión en el sistema</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fi fi-rr-shield-check" style="margin-right: 6px; color: var(--purpura-vibrante);"></i>
                                    Rol <span class="required-asterisk">*</span>
                                </label>
                                <select name="rol" class="form-input" required>
                                    <option value="">Seleccione un rol</option>
                                    <option value="administrador" <?= ($usuario['rol'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                                    <option value="empleado" <?= ($usuario['rol'] ?? '') === 'empleado' ? 'selected' : '' ?>>Empleado</option>
                                </select>
                                <small class="form-text-small">Define los permisos del usuario en el sistema</small>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Contraseña -->
                    <div class="form-section" style="margin-top: 20px;">
                        <div class="form-section-header">
                            <i class="fi fi-rr-key"></i>
                            <h3 class="form-section-title"><?= $esEdicion ? 'Cambiar Contraseña' : 'Contraseña' ?></h3>
                        </div>
                        
                        <div class="form-section-description">
                            <i class="fi fi-rr-info"></i>
                            <p><?= $esEdicion ? 'Deja estos campos vacíos si no deseas cambiar la contraseña del usuario.' : 'Establece la contraseña inicial para el nuevo usuario. El usuario podrá cambiarla después desde su perfil.' ?></p>
                        </div>
                        
                        <div class="form-grid" style="gap: 15px; margin-top: 20px;">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fi fi-rr-lock" style="margin-right: 6px; color: var(--purpura-vibrante);"></i>
                                    <?= $esEdicion ? 'Nueva Contraseña' : 'Contraseña' ?> 
                                    <?= $esEdicion ? '' : '<span class="required-asterisk">*</span>' ?>
                                </label>
                                <input type="password" name="nueva_contrasena" class="form-input"
                                    placeholder="<?= $esEdicion ? 'Nueva contraseña (mínimo 6 caracteres)' : 'Contraseña (mínimo 6 caracteres)' ?>" 
                                    <?= $esEdicion ? '' : 'required' ?> minlength="6">
                                <small class="form-text-small">Mínimo 6 caracteres</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fi fi-rr-lock" style="margin-right: 6px; color: var(--purpura-vibrante);"></i>
                                    Confirmar <?= $esEdicion ? 'Nueva ' : '' ?>Contraseña 
                                    <?= $esEdicion ? '' : '<span class="required-asterisk">*</span>' ?>
                                </label>
                                <input type="password" name="confirmar_contrasena" class="form-input"
                                    placeholder="Confirma la contraseña" 
                                    <?= $esEdicion ? '' : 'required' ?> minlength="6">
                                <small class="form-text-small">Debe coincidir con la contraseña anterior</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions-bottom">
                        <button type="submit" class="btn btn-primary btn-submit-usuario">
                            <i class="fi fi-rr-check"></i>
                            <span><?= $esEdicion ? 'Guardar Cambios' : 'Crear Usuario' ?></span>
                        </button>
                        <a href="<?= BASE_URL ?>?r=usuarios" class="btn btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>


