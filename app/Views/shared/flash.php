<?php
// Views/shared/flash.php
if (isset($_SESSION['flash'])): ?>
    <div class="flash-message">
         <?= htmlspecialchars($_SESSION['flash']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>