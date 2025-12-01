<?php
$flash = $_SESSION['flash'] ?? null; 
unset($_SESSION['flash']); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema LILA - Iniciar Sesión</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-card">
                <div class="login-logo">
                    <img src="<?= BASE_URL ?>img/logo_lila.png" alt="Logo LILA" class="login-img">
                    <div class="login-text-group">
                        <h2>Sistema LILA</h2>
                        <p>Cristalería y Aluminios Hermanos Soler C.A</p>
                    </div>
                </div>
                
                <?php if ($flash): ?>
                    <div class="flash-message"><?= htmlspecialchars($flash) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= BASE_URL ?>?r=login-post" class="login-form">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    
                    <div class="form-group">
                     <input type="text" name="usuario" required class="form-input" placeholder="Usuario">
                    </div>
                    
                    <div class="form-group">
                        <input type="password" name="contrasena" required class="form-input" placeholder="Contraseña">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Iniciar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>