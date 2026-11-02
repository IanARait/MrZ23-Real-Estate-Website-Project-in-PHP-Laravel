<?php
require_once 'header.php';

$pdo = getConnection();
$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $keys = ['site_name','site_email','site_phone','currency_symbol','currency_code','commission_rate','items_per_page','maintenance_mode','theme_color','google_maps_key','footer_text'];
    foreach ($keys as $key) {
        $val = $_POST[$key] ?? '';
        if ($key === 'maintenance_mode') $val = isset($_POST[$key]) ? '1' : '0';
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$val, $key]);
    }
    $msg = 'Settings saved successfully.';
}

$settings = [];
$stmt = $pdo->query("SELECT * FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Settings</h1>
</div>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <form method="post" class="card border-0 shadow-sm">
            <div class="card-body">
                <input type="hidden" name="save" value="1">
                <h6 class="text-muted border-bottom pb-2 mb-3">General Settings</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="<?= sanitize($settings['site_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site Email</label>
                        <input type="email" name="site_email" class="form-control" value="<?= sanitize($settings['site_email']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site Phone</label>
                        <input type="text" name="site_phone" class="form-control" value="<?= sanitize($settings['site_phone']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Currency Symbol</label>
                        <input type="text" name="currency_symbol" class="form-control" value="<?= sanitize($settings['currency_symbol']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Currency Code</label>
                        <input type="text" name="currency_code" class="form-control" value="<?= sanitize($settings['currency_code']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Commission Rate (%)</label>
                        <input type="number" step="0.01" name="commission_rate" class="form-control" value="<?= sanitize($settings['commission_rate']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Items Per Page</label>
                        <input type="number" name="items_per_page" class="form-control" value="<?= sanitize($settings['items_per_page']) ?>">
                    </div>
                </div>

                <h6 class="text-muted border-bottom pb-2 mb-3">Appearance</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Theme Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="theme_color" class="form-control form-control-color" value="<?= sanitize($settings['theme_color']) ?>">
                            <span><?= sanitize($settings['theme_color']) ?></span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Footer Text</label>
                        <input type="text" name="footer_text" class="form-control" value="<?= sanitize($settings['footer_text']) ?>">
                    </div>
                </div>

                <h6 class="text-muted border-bottom pb-2 mb-3">Integrations</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label">Google Maps API Key</label>
                        <input type="text" name="google_maps_key" class="form-control" value="<?= sanitize($settings['google_maps_key']) ?>">
                    </div>
                </div>

                <h6 class="text-muted border-bottom pb-2 mb-3">Maintenance</h6>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="maintenance_mode" class="form-check-input" id="mmSwitch" value="1" <?= $settings['maintenance_mode'] == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="mmSwitch">Maintenance Mode</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Settings</button>
            </div>
        </form>
    </div>
</div>
<?php require_once 'footer.php'; ?>
