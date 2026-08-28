<?php
use Core\App; $title = 'Riwayat Bel'; $activeMenu = 'riwayat'; ?>
<div class="page-header">
    <div>
        <h1 class="page-header-title">Riwayat Bel</h1>
        <p class="page-header-sub">Catatan aktivitas bel yang telah berbunyi.</p>
        <span class="badge badge-blue" style="margin-top:0.4rem;"><?= App::icon('clock', 'w-3 h-3') ?> Riwayat otomatis dihapus setelah 24 jam</span>
    </div>
    <div class="page-header-actions">
        <input type="date" id="filterDate" class="form-input" value="<?= htmlspecialchars($filterDate ?? '', ENT_QUOTES) ?>">
        <button type="button" id="btnTerapkan" class="btn btn-sm btn-ghost">Terapkan</button>
        <?php if (!empty($filterDate)): ?>
            <a href="<?= App::url('/admin/riwayat') ?>" class="btn btn-sm btn-ghost">Reset</a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-4 animate-slide-in">
    <div class="stat-card">
        <div class="stat-icon stat-icon-purple"><?= App::icon('clipboard', 'w-6 h-6') ?></div>
        <div class="stat-value"><?= (int) $stats['total'] ?></div>
        <div class="stat-label">Total</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-green"><?= App::icon('check', 'w-6 h-6') ?></div>
        <div class="stat-value"><?= (int) $stats['berhasil'] ?></div>
        <div class="stat-label">Berhasil</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-blue">🤖</div>
        <div class="stat-value"><?= (int) $stats['otomatis'] ?></div>
        <div class="stat-label">Otomatis</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-amber"><?= App::icon('user', 'w-6 h-6') ?></div>
        <div class="stat-value"><?= (int) $stats['manual'] ?></div>
        <div class="stat-label">Manual</div>
    </div>
</div>

<div class="card card-pad">
    <div class="riwayat-card-head">
        <h3>Daftar Riwayat</h3>
        <?php if (!empty($history)): ?>
            <button type="button" id="clearAll" class="btn btn-danger btn-sm" data-date="<?= htmlspecialchars($filterDate ?? '', ENT_QUOTES) ?>">Hapus Semua</button>
        <?php endif; ?>
    </div>
    <?php if (!empty($history)): ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Nama Bel</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th>Mode</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['date'], ENT_QUOTES) ?></td>
                    <td><span class="time-cell"><?= htmlspecialchars($h['time'], ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars($h['schedule_name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($h['bell_type'], ENT_QUOTES) ?></td>
                    <td>
                        <?php if ($h['status'] === 'berhasil'): ?>
                            <span class="badge badge-green">Berhasil</span>
                        <?php else: ?>
                            <span class="badge badge-red">Gagal</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($h['mode'] === 'otomatis'): ?>
                            <span class="badge badge-blue">Otomatis</span>
                        <?php else: ?>
                            <span class="badge badge-amber">Manual</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <button type="button" class="icon-btn icon-btn-sm icon-btn-danger btn-delete" data-id="<?= (int) $h['id'] ?>" data-name="<?= htmlspecialchars($h['schedule_name'], ENT_QUOTES) ?>" title="Hapus"><?= App::icon('trash', 'w-4 h-4') ?></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="empty-state">Belum ada riwayat bel<?= !empty($filterDate) ? ' untuk tanggal ' . htmlspecialchars($filterDate, ENT_QUOTES) : '' ?>.</div>
    <?php endif; ?>
</div>

<style>
.stat-card { text-align: center; }
.riwayat-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
.riwayat-card-head h3 { margin: 0; font-size: 1.05rem; }
.time-cell { font-weight: 600; color: #2563eb; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
.actions-cell { white-space: nowrap; }
</style>

<script src="<?= App::asset('js/riwayat.js') ?>"></script>
