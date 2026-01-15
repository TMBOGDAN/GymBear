<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
    <div class="header-container">

        <!-- LOGO -->
        <div class="logo">
            <img src="/resources/images/logo.png" class="logo-img" alt="Gym Bear Logo">
            <span class="logo-text">GymBear</span>
        </div>

        <!-- NAV -->
        <nav>
            <a href="/index.php">Acasa</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/pages/account.php">Contul meu</a>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/pages/admin.php">Manage</a>
                <?php endif; ?>

            <?php else: ?>
                <a href="/pages/login.php">Login</a>
            <?php endif; ?>

            <a href="/pages/about.php">Despre</a>
            <a href="/pages/membership.php">Abonamente</a>
            <a href="/pages/trainers.php">Antrenori</a>
            <a href="/pages/contact.php">Contact</a>
        </nav>

    </div>
</header>
