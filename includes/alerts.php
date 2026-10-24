<?php
/**
 * GlobeTrek Adventures - Dynamic Alert Renderer
 */
require_once __DIR__ . '/../config/helpers.php';
$flash = get_flash();
?>
<?php if ($flash): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
    <div style="display:flex; align-items:center; gap:0.6rem;">
      <?php if ($flash['type'] === 'success'): ?>
        <i class="fa-solid fa-circle-check" style="font-size:1.2rem;"></i>
      <?php elseif ($flash['type'] === 'danger'): ?>
        <i class="fa-solid fa-circle-xmark" style="font-size:1.2rem;"></i>
      <?php elseif ($flash['type'] === 'warning'): ?>
        <i class="fa-solid fa-triangle-exclamation" style="font-size:1.2rem;"></i>
      <?php else: ?>
        <i class="fa-solid fa-circle-info" style="font-size:1.2rem;"></i>
      <?php endif; ?>
      <span><?= htmlspecialchars($flash['message']) ?></span>
    </div>
    <button type="button" class="alert-close" aria-label="Close">&times;</button>
  </div>
<?php endif; ?>
