        </div>
    </div>
</div>
<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> AgroPay+. Todos los derechos reservados.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/agropay/assets/js/main.js"></script>
<?php if (isset($js_extra)): ?>
<script src="/agropay/assets/js/<?= $js_extra ?>"></script>
<?php endif; ?>
</body>
</html>