<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="/agropay/dashboard.php" class="list-group-item list-group-item-action">
            <i class="bi bi-tree me-2"></i>AgroPay+
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link">
                        <i class="bi bi-person"></i> <?= $_SESSION['usuario_nombre'] ?? 'Invitado' ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="modules/auth/logout.php">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>