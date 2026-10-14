<?php /** includes/footer.php */ ?>
<?php if (isLoggedIn()): ?>
</main>
</div>
<?php endif; ?>
<footer class="app-footer">
    <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?> · v<?= e(APP_VERSION) ?></p>
</footer>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= BASE_URL ?>/assets/js/<?= e($js) ?>.js"></script>
<?php endforeach; endif; ?>
</body>
</html>
