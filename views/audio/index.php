<?php
use Core\App; $title = 'Audio Bel'; $activeMenu = 'audio'; ?>
<div class="page-header">
    <div>
        <h1 class="page-header-title">Audio Bel</h1>
        <p class="page-header-sub">Kelola file audio bel sekolah</p>
    </div>
    <div class="page-header-actions">
        <button type="button" id="btnUpload" class="btn btn-gradient"><?= App::icon('plus', 'w-4 h-4') ?> Upload Audio</button>
    </div>
</div>

<div class="alert alert-info">Klik Preview untuk mendengar suara sebelum digunakan.</div>

<div class="grid grid-3 animate-slide-in" id="audioGrid">
    <?php foreach ($audioFiles as $af): ?>
        <div class="card card-pad audio-card"
            data-id="<?= (int) $af['id'] ?>"
            data-name="<?= htmlspecialchars($af['name'], ENT_QUOTES) ?>"
            data-filename="<?= htmlspecialchars($af['filename'], ENT_QUOTES) ?>"
            data-filepath="<?= htmlspecialchars($af['filepath'], ENT_QUOTES) ?>"
            data-bell-type-id="<?= (int) ($af['bell_type_id'] ?? 0) ?>"
            data-volume="<?= (float) $af['volume'] ?>"
            data-duration="<?= (int) $af['duration'] ?>"
            data-is-default="<?= $af['is_default'] ? '1' : '0' ?>"
            data-icon-default="<?= $af['is_default'] ? '🔊' : '🎵' ?>">
            <div class="audio-card-head">
                <div class="stat-icon stat-icon-purple card-icon"><?= $af['is_default'] ? App::icon('speaker', 'w-6 h-6') : App::icon('music', 'w-6 h-6') ?></div>
                <?php if ($af['is_default']): ?>
                    <span class="badge badge-amber">Default</span>
                <?php endif; ?>
            </div>
            <h3 class="audio-card-title"><?= htmlspecialchars($af['name'], ENT_QUOTES) ?></h3>
            <div class="audio-card-file text-muted"><?= htmlspecialchars($af['filename'], ENT_QUOTES) ?></div>
            <?php if (!empty($af['bell_type_name'])): ?>
                <div class="audio-card-type"><?= htmlspecialchars($af['bell_type_name'], ENT_QUOTES) ?></div>
            <?php endif; ?>
            <div class="audio-card-meta text-muted">Vol: <?= round($af['volume'] * 100) ?>% · Durasi <?= (int) $af['duration'] ?> dtk</div>
            <div class="audio-card-actions">
                <button type="button" class="btn btn-sm btn-success btn-preview">Preview</button>
                <button type="button" class="icon-btn icon-btn-sm btn-edit" title="Edit"><?= App::icon('pencil', 'w-4 h-4') ?></button>
                <button type="button" class="icon-btn icon-btn-sm icon-btn-danger btn-delete" title="Hapus"><?= App::icon('trash', 'w-4 h-4') ?></button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($audioFiles)): ?>
        <div class="empty-state" style="grid-column:1/-1;">Belum ada audio bel. Klik "<?= App::icon('plus', 'w-4 h-4') ?> Upload Audio" untuk menambahkan.</div>
    <?php endif; ?>
</div>

<div class="modal-backdrop" id="audioModal" hidden>
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Upload Audio</h3>
            <button type="button" class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="modal-body">
            <div class="alert alert-error" id="formError" hidden></div>
            <div class="form-group" id="fileGroup">
                <label class="form-label" for="f_file">File Audio</label>
                <input type="file" id="f_file" class="form-input" accept="audio/*">
                <div class="form-error" style="margin-top:0.25rem;color:var(--text-muted);font-size:0.75rem;">MP3, WAV, OGG — maks 10MB</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="f_name">Nama</label>
                <input type="text" id="f_name" class="form-input" placeholder="cth: Bel Masuk Pagi">
            </div>
            <div class="form-group">
                <label class="form-label" for="f_bell_type_id">Jenis Bel</label>
                <select id="f_bell_type_id" class="form-select">
                    <option value="">Pilih</option>
                    <?php foreach ($bellTypes as $bt): ?>
                        <option value="<?= (int) $bt['id'] ?>"><?= htmlspecialchars($bt['name'], ENT_QUOTES) ?> (<?= htmlspecialchars($bt['category'], ENT_QUOTES) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="f_volume">Volume</label>
                <div class="volume-label">
                    <span>Volume</span>
                    <span id="volLabel">80%</span>
                </div>
                <input type="range" id="f_volume" min="0" max="1" step="0.1" value="0.8">
            </div>
            <div class="form-group">
                <label class="form-label" for="f_duration">Durasi (detik)</label>
                <input type="number" id="f_duration" class="form-input" min="1" max="300" value="5" placeholder="cth: 5">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" id="btnBatal">Batal</button>
            <button type="button" class="btn btn-primary" id="btnSave">Simpan</button>
        </div>
    </div>
</div>

<style>
.modal { background: #fff; border-radius: 0.75rem; width: 100%; max-width: 480px; max-height: 90vh; box-shadow: 0 20px 40px rgba(0,0,0,0.2); display: flex; flex-direction: column; }
.modal-body { padding: 1.25rem; overflow-y: auto; flex: 1; min-height: 0; }
.audio-card-title { margin:0 0 0.25rem; font-size:1.05rem; }
.audio-card-file { font-size:0.8rem; margin-bottom:0.25rem; word-break:break-all; }
.audio-card-type { font-size:0.8rem; font-weight:600; color:var(--primary); margin-bottom:0.5rem; }
.audio-card-meta { font-size:0.8rem; margin-bottom:0.9rem; }
.audio-card-actions { display:flex; gap:0.5rem; flex-wrap:wrap; }
</style>

<script src="<?= App::asset('js/audio.js') ?>"></script>