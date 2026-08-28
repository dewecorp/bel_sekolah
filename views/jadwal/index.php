<?php
use Core\App; $title = 'Manajemen Jadwal Bel'; $activeMenu = 'jadwal'; ?>
<div class="page-header">
    <div>
        <h1 class="page-header-title">Manajemen Jadwal Bel</h1>
        <p class="page-header-sub">Atur jadwal bel berbasis hari dan waktu.</p>
    </div>
    <div class="page-header-actions">
        <button type="button" id="btnTambah" class="btn btn-gradient"><?= App::icon('plus', 'w-4 h-4') ?> Tambah Jadwal</button>
    </div>
</div>

<div class="tab tabs" id="dayTabs">
    <button type="button" class="tab-item active" data-day="Semua">Semua</button>
    <?php foreach ($days as $day): ?>
        <button type="button" class="tab-item" data-day="<?= htmlspecialchars($day, ENT_QUOTES) ?>"><?= htmlspecialchars($day, ENT_QUOTES) ?></button>
    <?php endforeach; ?>
</div>

<div id="jadwalGroups" class="animate-slide-in">
    <?php foreach ($days as $day): ?>
        <?php
            $count = 0;
            foreach ($schedules as $s) {
                if ($s['day'] === $day) { $count++; }
            }
        ?>
        <div class="card card-pad mb-2" data-group-day="<?= htmlspecialchars($day, ENT_QUOTES) ?>">
            <div class="group-header">
                <h3><?= htmlspecialchars($day, ENT_QUOTES) ?></h3>
                <span class="badge"><?= $count ?> jadwal</span>
            </div>
            <?php if ($count > 0): ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Jam</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $s): ?>
                            <?php if ($s['day'] !== $day) { continue; } ?>
                            <tr
                                data-id="<?= (int) $s['id'] ?>"
                                data-day="<?= htmlspecialchars($s['day'], ENT_QUOTES) ?>"
                                data-time="<?= htmlspecialchars($s['time'], ENT_QUOTES) ?>"
                                data-name="<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>"
                                data-bell="#<?= (int) $s['bell_type_id'] ?>"
                            >
                                <td>
                                    <span class="time-cell"><?= htmlspecialchars($s['time'], ENT_QUOTES) ?></span>
                                </td>
                                <td>
                                    <div class="schedule-name"><?= htmlspecialchars($s['name'], ENT_QUOTES) ?></div>
                                    <div class="schedule-type"><?= htmlspecialchars($s['bell_type_name'] ?? '', ENT_QUOTES) ?></div>
                                </td>
                                <td>
                                    <?php if ($s['is_active']): ?>
                                        <span class="badge badge-green">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-red">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <button type="button" class="btn btn-sm btn-toggle <?= $s['is_active'] ? 'btn-toggle-on' : 'btn-toggle-off' ?>" data-action="toggle">
                                        <?= $s['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>
<button type="button" class="icon-btn icon-btn-sm btn-edit" data-action="edit" title="Edit"><?= App::icon('pencil', 'w-4 h-4') ?></button>
                        <button type="button" class="icon-btn icon-btn-sm icon-btn-danger btn-delete" data-action="delete" title="Hapus"><?= App::icon('trash', 'w-4 h-4') ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="empty-state">Belum ada jadwal untuk hari <?= htmlspecialchars($day, ENT_QUOTES) ?>.</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal-backdrop" id="jadwalModal" hidden>
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Jadwal Baru</h3>
            <button type="button" class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="modal-body">
            <div class="alert alert-error" id="formError" hidden></div>
            <form id="jadwalForm">
                <div class="form-group">
                    <label class="form-label" for="f_day">Hari</label>
                    <select id="f_day" class="form-select" required>
                        <?php foreach ($days as $day): ?>
                            <option value="<?= htmlspecialchars($day, ENT_QUOTES) ?>"><?= htmlspecialchars($day, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="f_time">Waktu</label>
                    <input type="time" id="f_time" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="f_name">Nama Bel</label>
                    <input type="text" id="f_name" class="form-input" placeholder="cth: Bel Masuk Pagi" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="f_bell_type_id">Jenis Bel</label>
                    <select id="f_bell_type_id" class="form-select" required>
                        <option value="">-- Pilih Jenis Bel --</option>
                        <?php foreach ($bellTypes as $bt): ?>
                            <option value="<?= (int) $bt['id'] ?>"><?= htmlspecialchars($bt['name'], ENT_QUOTES) ?> (<?= htmlspecialchars($bt['category'], ENT_QUOTES) ?>)</option>
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
.mb-2 { margin-bottom: 1rem; }
.group-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
.group-header h3 { margin: 0; font-size: 1.05rem; }
.time-cell { font-weight: 600; color: #2563eb; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
.schedule-name { font-weight: 500; }
.schedule-type { font-size: 0.8rem; color: #64748b; }
.actions-cell { white-space: nowrap; }
.btn-toggle-on { background: #16a34a; color: #fff; }
.btn-toggle-off { background: #64748b; color: #fff; }
.tab-item { padding: 0.5rem 1rem; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; border-radius: 0.375rem; }
.tab-item.active { background: #2563eb; color: #fff; border-color: #2563eb; }
.modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 1000; }
.modal-backdrop.open { display: flex; align-items: center; justify-content: center; }
.modal-backdrop[hidden] { display: none !important; }
.modal-backdrop:not([hidden]) { display: flex; align-items: center; justify-content: center; }
.modal { background: #fff; border-radius: 0.75rem; width: 100%; max-width: 480px; max-height: 90vh; box-shadow: 0 20px 40px rgba(0,0,0,0.2); display: flex; flex-direction: column; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; }
.modal-header h3 { margin: 0; }
.modal-close { background: none; border: none; font-size: 1.5rem; line-height: 1; cursor: pointer; color: #64748b; }
.modal-body { padding: 1.25rem; overflow-y: auto; flex: 1; min-height: 0; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; }
.alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1rem; font-size: 0.875rem; display: none; }
body.modal-open { overflow: hidden; }
</style>

<script src="<?= App::asset('js/jadwal.js') ?>"></script>
