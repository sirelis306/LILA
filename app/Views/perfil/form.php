<?php
$titulo = 'Mi Perfil';
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

<div class="content-body">
    <div class="container">
        <div class="card-product-main formulario-inventario">
            <div class="main-form-content">
                <form method="POST" action="<?= BASE_URL ?>?r=actualizar-perfil" class="producto-form">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

                    <div class="form-grid" style="gap: 15px; margin-bottom: 10px;">
                        <!-- Información del Usuario -->
                        <div class="form-group form-full-width">
                            <h3 style="margin-bottom: 15px; color: var(--azul-oscuro); border-bottom: 2px solid var(--azul-claro); padding-bottom: 10px;">
                                Información Personal
                            </h3>
                        </div>

                        <!-- Usuario (nombre de usuario) -->
                        <div class="form-group">
                            <label class="form-label">Nombre de Usuario <span style="color: red;">*</span></label>
                            <input type="text" name="usuario" class="form-input" 
                                value="<?= htmlspecialchars($usuario['usuario'] ?? '') ?>" 
                                required maxlength="50" placeholder="Nombre de usuario para iniciar sesión">
                            <small class="form-text-small">Usado para iniciar sesión en el sistema</small>
                        </div>

                    </div>

                    <!-- Sección de Cambio de Contraseña -->
                    <div class="form-grid" style="gap: 15px; margin-top: 30px; margin-bottom: 10px;">
                        <div class="form-group form-full-width">
                            <h3 style="margin-bottom: 15px; color: var(--azul-oscuro); border-bottom: 2px solid var(--azul-claro); padding-bottom: 10px;">
                                Cambiar Contraseña
                            </h3>
                            <p style="color: var(--texto-claro); font-size: 14px; margin-bottom: 15px;">
                                Deja estos campos vacíos si no deseas cambiar tu contraseña
                            </p>
                        </div>

                        <!-- Contraseña Actual -->
                        <div class="form-group">
                            <label class="form-label">Contraseña Actual</label>
                            <input type="password" name="contrasena_actual" class="form-input" 
                                placeholder="Ingresa tu contraseña actual">
                            <small class="form-text-small">Requerida solo si vas a cambiar la contraseña</small>
                        </div>

                        <!-- Nueva Contraseña -->
                        <div class="form-group">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="nueva_contrasena" class="form-input" 
                                placeholder="Nueva contraseña (mínimo 6 caracteres)" minlength="6">
                            <small class="form-text-small">Mínimo 6 caracteres</small>
                        </div>

                        <!-- Confirmar Nueva Contraseña -->
                        <div class="form-group">
                            <label class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" name="confirmar_contrasena" class="form-input" 
                                placeholder="Confirma tu nueva contraseña" minlength="6">
                            <small class="form-text-small">Debe coincidir con la nueva contraseña</small>
                        </div>
                    </div>

                    <div class="form-actions-bottom">
                        <button type="submit" class="btn btn-primary">
                            <i class="fi fi-rr-check"></i> 
                            Actualizar Perfil
                        </button>
                        <a href="<?= BASE_URL ?>?r=<?= currentUser()['rol'] === 'administrador' ? 'admin' : 'empleado' ?>" class="btn btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (currentUser()['rol'] === 'administrador'): ?>
        <!-- Sección de Gestión de Usuarios (solo para administradores) -->
        <div class="card-product-main formulario-inventario" style="margin-top: 30px;">
            <div class="main-form-content">
                <div class="admin-section-usuarios">
                    <div class="admin-section-header">
                        <div class="admin-section-icon">
                            <i class="fi fi-rr-users"></i>
                        </div>
                        <div class="admin-section-title-group">
                            <h3 class="admin-section-title">Gestión de Usuarios</h3>
                            <p class="admin-section-subtitle">Administra usuarios del sistema</p>
                        </div>
                    </div>
                    
                    <div class="admin-section-content">
                        <p class="admin-section-description">
                            Como administrador, puedes gestionar todos los usuarios del sistema, editar sus datos personales, cambiar sus contraseñas y modificar sus roles.
                        </p>
                        
                        <div class="admin-section-features">
                            <div class="admin-feature-item">
                                <i class="fi fi-rr-user-pen"></i>
                                <span>Editar información de usuarios</span>
                            </div>
                            <div class="admin-feature-item">
                                <i class="fi fi-rr-key"></i>
                                <span>Cambiar contraseñas</span>
                            </div>
                            <div class="admin-feature-item">
                                <i class="fi fi-rr-shield-check"></i>
                                <span>Gestionar roles y permisos</span>
                            </div>
                        </div>
                        
                        <div class="admin-section-action">
                            <a href="<?= BASE_URL ?>?r=usuarios" class="btn btn-primary btn-admin-action">
                                <i class="fi fi-rr-users"></i>
                                <span>Ir a Gestión de Usuarios</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>

