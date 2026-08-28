<?php
use Core\App; $title = 'Pengaturan'; $activeMenu = 'pengaturan'; ?>
<div class="page-header">
    <div>
        <h1 class="page-header-title">Pengaturan</h1>
        <p class="page-header-sub">Konfigurasi sistem bel sekolah.</p>
    </div>
</div>

<div id="alertContainer"></div>

<div class="grid">
    <div class="card card-pad">
        <h3 class="section-title">Status Sistem</h3>
        <div class="form-group">
            <label class="form-label">Bel Otomatis</label>
            <label class="switch">
                <input type="checkbox" id="system_toggle" <?= (int) $settings['system_active'] === 1 ? 'checked' : '' ?>>
                <span class="switch-slider"></span>
            </label>
            <input type="hidden" id="f_system_active" value="<?= (int) $settings['system_active'] === 1 ? 1 : 0 ?>">
        </div>
        <div class="alert <?= (int) $settings['system_active'] === 1 ? 'alert-success' : 'alert-danger' ?>" id="systemStatus">
            <span>Sistem bel otomatis <?= (int) $settings['system_active'] === 1 ? 'AKTIF' : 'NONAKTIF' ?></span>
        </div>
    </div>

    <div class="card card-pad">
        <h3 class="section-title">Informasi Sekolah</h3>
        <div class="form-group">
            <label class="form-label" for="f_school_logo">Logo Sekolah</label>
            <?php $logoUrl = App::logoUrl(); ?>
            <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                <div class="brand-logo <?= $logoUrl ? '' : 'brand-logo-placeholder' ?>" id="logoPreviewBox" style="width:64px;height:64px;">
                    <?php if ($logoUrl): ?>
                        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="width:100%;height:100%;object-fit:contain;">
                    <?php else: ?>
                        <span style="color:#94a3b8;display:inline-flex;"><?= App::icon('photo', 'w-6 h-6') ?></span>
                    <?php endif; ?>
                </div>
                <div style="flex:1;min-width:220px;">
                    <input type="file" id="f_school_logo" class="form-input" accept="image/png,image/jpeg,image/gif,image/webp">
                    <p class="text-muted" style="font-size:0.75rem;margin-top:0.375rem;">PNG, JPG, GIF, atau WebP — maks 2MB. Logo tampil di navbar dashboard.</p>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="f_school_name">Nama Sekolah</label>
            <input type="text" id="f_school_name" class="form-input" value="<?= htmlspecialchars($settings['school_name'] ?? '', ENT_QUOTES) ?>">
        </div>
        <div class="form-group">
            <label class="form-label" for="f_school_address">Alamat Sekolah</label>
            <input type="text" id="f_school_address" class="form-input" value="<?= htmlspecialchars($settings['school_address'] ?? '', ENT_QUOTES) ?>">
        </div>
    </div>

    <div class="card card-pad">
        <h3 class="section-title">Pengaturan Sistem</h3>
        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-label" for="f_timezone">Zona Waktu</label>
                <select id="f_timezone" class="form-select">
                    <option value="Asia/Jakarta" <?= $settings['timezone'] === 'Asia/Jakarta' ? 'selected' : '' ?>>Asia/Jakarta (WIB)</option>
                    <option value="Asia/Makassar" <?= $settings['timezone'] === 'Asia/Makassar' ? 'selected' : '' ?>>Asia/Makassar (WITA)</option>
                    <option value="Asia/Jayapura" <?= $settings['timezone'] === 'Asia/Jayapura' ? 'selected' : '' ?>>Asia/Jayapura (WIT)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="f_time_format">Format Jam</label>
                <select id="f_time_format" class="form-select">
                    <option value="24" <?= $settings['time_format'] === '24' ? 'selected' : '' ?>>24 Jam</option>
                    <option value="12" <?= $settings['time_format'] === '12' ? 'selected' : '' ?>>12 Jam</option>
                </select>
            </div>
            <div class="form-group">
                <label class="volume-label" for="f_volume">Volume Default <span id="volLabel"><?= round((float) $settings['default_volume'] * 100) ?>%</span></label>
                <input type="range" id="f_volume" min="0" max="1" step="0.1" value="<?= (float) $settings['default_volume'] ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="f_duration">Durasi Bel (detik)</label>
                <input type="number" id="f_duration" class="form-input" min="1" max="300" value="<?= (int) $settings['bell_duration'] ?>">
            </div>
        </div>
    </div>

    <div class="card card-pad">
        <div class="section-title" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0;">
            <span>Ubah Password</span>
            <button type="button" class="btn btn-sm btn-ghost" id="togglePass">Tampilkan</button>
        </div>
        <div id="passFields" class="hidden" style="margin-top:1rem;">
            <div class="form-group">
                <label class="form-label" for="f_old_pass">Password Lama</label>
                <input type="password" id="f_old_pass" class="form-input" autocomplete="current-password">
            </div>
            <div class="form-group">
                <label class="form-label" for="f_new_pass">Password Baru</label>
                <input type="password" id="f_new_pass" class="form-input" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label" for="f_confirm_pass">Konfirmasi Password Baru</label>
                <input type="password" id="f_confirm_pass" class="form-input" autocomplete="new-password">
            </div>
        </div>
    </div>

    <div style="text-align:right;">
        <button type="button" id="btnSave" class="btn btn-primary btn-lg">Simpan Pengaturan</button>
    </div>
</div>

<script src="<?= App::asset('js/settings.js') ?>"></script>