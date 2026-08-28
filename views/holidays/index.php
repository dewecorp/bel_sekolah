<?php
use Core\App; $title = 'Hari Libur'; $activeMenu = 'libur'; ?>
<div class="page-header">
    <div>
        <h1 class="page-header-title">Hari Libur</h1>
        <p class="page-header-sub">Kelola hari libur dan tanggal merah sekolah.</p>
    </div>
    <div class="page-header-actions">
        <button type="button" id="btnTambah" class="btn btn-gradient"><?= App::icon('plus', 'w-4 h-4') ?> Tambah Hari Libur</button>
    </div>
</div>

<?php
    $today = date('Y-m-d');
    $upcoming = [];
    $past = [];
    foreach ($holidays as $h) {
        if ($h['date'] < $today) {
            $past[] = $h;
        } else {
            $upcoming[] = $h;
        }
    }
?>

<div class="section-title">Akan Datang</div>
<div class="grid grid-3 animate-slide-in">
    <?php foreach ($upcoming as $h): ?>
        <div class="card card-pad"
            data-id="<?= (int) $h['id'] ?>"
            data-date="<?= htmlspecialchars($h['date'], ENT_QUOTES) ?>"
            data-name="<?= htmlspecialchars($h['name'], ENT_QUOTES) ?>"
            data-description="<?= htmlspecialchars($h['description'], ENT_QUOTES) ?>">
            <div class="holiday-card-head">
                <div class="stat-icon stat-icon-purple"><?= App::icon('calendar-days', 'w-6 h-6') ?></div>
                <span class="badge badge-purple"><?= htmlspecialchars($h['date'], ENT_QUOTES) ?></span>
            </div>
            <h3 class="holiday-card-title"><?= htmlspecialchars($h['name'], ENT_QUOTES) ?></h3>
            <p class="text-muted"><?= htmlspecialchars($h['description'], ENT_QUOTES) ?></p>
            <div class="holiday-card-actions">
                <button type="button" class="icon-btn icon-btn-sm btn-edit"
                    data-id="<?= (int) $h['id'] ?>"
                    data-date="<?= htmlspecialchars($h['date'], ENT_QUOTES) ?>"
                    data-name="<?= htmlspecialchars($h['name'], ENT_QUOTES) ?>"
                    data-description="<?= htmlspecialchars($h['description'], ENT_QUOTES) ?>" title="Edit"><?= App::icon('pencil', 'w-4 h-4') ?></button>
                <button type="button" class="icon-btn icon-btn-sm icon-btn-danger btn-delete"
                    data-id="<?= (int) $h['id'] ?>" data-name="<?= htmlspecialchars($h['name'], ENT_QUOTES) ?>" title="Hapus"><?= App::icon('trash', 'w-4 h-4') ?></button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($upcoming)): ?>
        <div class="empty-state" style="grid-column:1/-1;">Belum ada hari libur mendatang.</div>
    <?php endif; ?>
</div>

<div class="section-title">Riwayat Libur</div>
<?php if (!empty($past)): ?>
<div class="card card-pad" style="opacity:.6;">
    <?php foreach ($past as $h): ?>
        <div class="holiday-row"
            data-id="<?= (int) $h['id'] ?>"
            data-date="<?= htmlspecialchars($h['date'], ENT_QUOTES) ?>"
            data-name="<?= htmlspecialchars($h['name'], ENT_QUOTES) ?>"
            data-description="<?= htmlspecialchars($h['description'], ENT_QUOTES) ?>">
            <span class="holiday-row-date"><?= htmlspecialchars($h['date'], ENT_QUOTES) ?></span>
            <span class="holiday-row-name"><?= htmlspecialchars($h['name'], ENT_QUOTES) ?></span>
            <span class="holiday-row-actions">
                <button type="button" class="icon-btn icon-btn-sm btn-edit"
                    data-id="<?= (int) $h['id'] ?>"
                    data-date="<?= htmlspecialchars($h['date'], ENT_QUOTES) ?>"
                    data-name="<?= htmlspecialchars($h['name'], ENT_QUOTES) ?>"
                    data-description="<?= htmlspecialchars($h['description'], ENT_QUOTES) ?>" title="Edit"><?= App::icon('pencil', 'w-4 h-4') ?></button>
                <button type="button" class="icon-btn icon-btn-sm icon-btn-danger btn-delete"
                    data-id="<?= (int) $h['id'] ?>" data-name="<?= htmlspecialchars($h['name'], ENT_QUOTES) ?>" title="Hapus"><?= App::icon('trash', 'w-4 h-4') ?></button>
            </span>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">Belum ada riwayat libur.</div>
<?php endif; ?>

<div class="modal-backdrop" id="liburModal" hidden>
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Hari Libur</h3>
            <button type="button" class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="modal-body">
            <div class="alert alert-error" id="formError" hidden></div>
            <form id="liburForm">
                <div class="form-group">
                    <label class="form-label" for="f_date">Tanggal</label>
                    <input type="date" id="f_date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="f_name">Nama</label>
                    <input type="text" id="f_name" class="form-input" placeholder="cth: Hari Kemerdekaan RI" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="f_description">Keterangan</label>
                    <input type="text" id="f_description" class="form-input" placeholder="cth: Libur nasional">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" id="btnBatal">Batal</button>
            <button type="button" class="btn btn-primary" id="btnSave">Simpan</button>
        </div>
    </div>
</div>

<style>
.section-title { margin: 1.5rem 0 0.75rem; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
.holiday-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
.holiday-card-title { margin: 0 0 0.5rem; font-size: 1.05rem; }
.holiday-card-actions { display: flex; gap: 0.5rem; margin-top: 0.75rem; }
.holiday-row { display: flex; align-items: center; gap: 1rem; padding: 0.6rem 0; border-bottom: 1px solid #e2e8f0; }
.holiday-row:last-child { border-bottom: none; }
.holiday-row-date { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.85rem; color: #64748b; white-space: nowrap; }
.holiday-row-name { font-weight: 500; flex: 1; }
.holiday-row-actions { display: flex; gap: 0.5rem; white-space: nowrap; }
</style>

<script src="<?= App::asset('js/holidays.js') ?>"></script>
