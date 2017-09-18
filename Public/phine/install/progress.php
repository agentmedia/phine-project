<?php
require_once __DIR__ . '/../../../LoadPhine.php';
use Phine\Framework\Progress\Json\Reporter;

$reporter = new Reporter(__DIR__ . '/progress.json');
$status = $reporter->GetStatus();
$progress = $status ? round(100* $status->progress / $status->progressCount, 2): 0.0;
$description = ($status && isset($status->progressDescription)) ? $status->progressDescription : '';
?>
<h4>
    Installation Status
</h4>
<div class="panel">
    <p>
    <?php if ($description): ?>
        <?php echo htmlspecialchars($description); ?>
    <?php else: ?>
    <span class="fa fa-spinner fa-spin"></span> Initializing, please wait &hellip;
    <?php endif ?>
    </p>
    <div class="progress-panel">
        <div class="progress-bar" style="width: <?php echo $progress?>%"></div>
    </div>
</div>
