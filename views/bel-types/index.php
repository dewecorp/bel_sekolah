<?php
use Core\App; $title = 'Jenis Bel'; $activeMenu = 'bel'; ?>
<div class="page-header">
    <div>
        <h1 class="page-header-title">Jenis Bel</h1>
        <p class="page-header-sub">Kelola kategori dan jenis bel.</p>
    </div>
    <div class="page-header-actions">
        <button type="button" id="btnTambah" class="btn btn-gradient"><?= App::icon('plus', 'w-4 h-4') ?> Tambah Jenis Bel</button>
    </div>
</div>

<?php
    $total = count($bellTypes);
    $catCounts = [];
    foreach ($categories as $cat) { $catCounts[$cat] = 0; }
    foreach ($bellTypes as $bt) {
        if (isset($catCounts[$bt['category']])) {
            $catCounts[$bt['category']]++;
        }
    }
?>

<div class="tab tabs" id="categoryTabs">
    <button type="button" class="tab-item active" data-category="Semua">Semua (<?= $total ?>)</button>
    <?php foreach ($categories as $cat): ?>
        <button type="button" class="tab-item" data-category="<?= htmlspecialchars($cat, ENT_QUOTES) ?>"><?= htmlspecialchars($cat, ENT_QUOTES) ?> (<?= $catCounts[$cat] ?>)</button>
    <?php endforeach; ?>
</div>

<div class="grid grid-3 animate-slide-in" id="belGrid">
    <?php foreach ($bellTypes as $bt): ?>
        <div class="card card-pad bel-card" data-category="<?= htmlspecialchars($bt['category'], ENT_QUOTES) ?>">
            <div class="bel-card-head">
                <div class="stat-icon stat-icon-amber"><?= App::icon('bell', 'w-6 h-6') ?></div>
                <span class="badge badge-blue"><?= htmlspecialchars($bt['category'], ENT_QUOTES) ?></span>
            </div>
            <h3 class="bel-card-title"><?= htmlspecialchars($bt['name'], ENT_QUOTES) ?></h3>
            <div class="bel-card-actions">
                <button type="button" class="icon-btn icon-btn-sm btn-edit"
                    data-id="<?= (int) $bt['id'] ?>"
                    data-name="<?= htmlspecialchars($bt['name'], ENT_QUOTES) ?>"
                    data-category="<?= htmlspecialchars($bt['category'], ENT_QUOTES) ?>" title="Edit"><?= App::icon('pencil', 'w-4 h-4') ?></button>
                <button type="button" class="icon-btn icon-btn-sm icon-btn-danger btn-delete"
                    data-id="<?= (int) $bt['id'] ?>"
                    data-name="<?= htmlspecialchars($bt['name'], ENT_QUOTES) ?>" title="Hapus"><?= App::icon('trash', 'w-4 h-4') ?></button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if ($total === 0): ?>
        <div class="empty-state" style="grid-column:1/-1;">Belum ada jenis bel.</div>
    <?php endif; ?>
</div>

<div class="modal-backdrop" id="belModal" hidden>
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Jenis Bel</h3>
            <button type="button" class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="modal-body">
            <div class="alert alert-error" id="formError" hidden></div>
            <form id="belForm">
                <div class="form-group">
                    <label class="form-label" for="f_name">Nama</label>
                    <input type="text" id="f_name" class="form-input" placeholder="cth: Bel Masuk Pagi">
                </div>
                <div class="form-group">
                    <label class="form-label" for="f_category">Kategori</label>
                    <select id="f_category" class="form-select">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>"><?= htmlspecialchars($cat, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
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
.bel-card-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem; }
.bel-card-title { margin:0 0 0.75rem; font-size:1.05rem; }
.bel-card-actions { display:flex; gap:0.5rem; }
</style>

<script src="<?= App::asset('js/bel.js') ?>"></script>
