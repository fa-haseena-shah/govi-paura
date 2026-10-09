    </main>
  </div>
</div>

<!-- Bootstrap JS bundle (for dropdowns/modals/collapse used in some pages) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (!empty($needsChart)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<?php endif; ?>

<?php if (!empty($needsMap)): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= BASE_URL ?>/assets/js/leaflet-map.js"></script>
<?php endif; ?>

<script src="<?= BASE_URL ?>/assets/js/api.js"></script>
<script src="<?= BASE_URL ?>/assets/js/i18n.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<?php if (!empty($portal)): ?>
<script src="<?= BASE_URL ?>/assets/js/<?= htmlspecialchars($portal) ?>.js"></script>
<?php endif; ?>
<?php if (!empty($pageScript)): ?>
<script src="<?= BASE_URL ?>/assets/js/<?= htmlspecialchars($pageScript) ?>.js"></script>
<?php endif; ?>
</body>
</html>
