-- =====================================================
-- Bel Sekolah Digital - Seed Data Contoh
-- =====================================================

-- Pengaturan awal
INSERT INTO settings (id) VALUES (1);

-- Jenis bel
INSERT INTO bell_types (name, category) VALUES
('Bel Masuk Sekolah',              'Bel Masuk'),
('Bel Pelajaran 1',                'Bel Pergantian Pelajaran'),
('Bel Pelajaran 2',                'Bel Pergantian Pelajaran'),
('Bel Pelajaran 3',                'Bel Pergantian Pelajaran'),
('Bel Pelajaran 4',                'Bel Pergantian Pelajaran'),
('Bel Pelajaran 5',                'Bel Pergantian Pelajaran'),
('Bel Pelajaran 6',                'Bel Pergantian Pelajaran'),
('Bel Pelajaran 7',                'Bel Pergantian Pelajaran'),
('Bel Istirahat 1',                'Bel Istirahat'),
('Bel Masuk Setelah Istirahat 1',  'Bel Masuk Setelah Istirahat'),
('Bel Istirahat 2',                'Bel Istirahat'),
('Bel Masuk Setelah Istirahat 2',  'Bel Masuk Setelah Istirahat'),
('Bel Pulang',                     'Bel Pulang'),
('Bel Khusus - Upacara',           'Bel Khusus');

-- User admin (password: admin123)
INSERT INTO users (username, password, name, role) VALUES
('admin', '$2y$12$G63ptrdISTLCLsMXPqI.QeXrMMPEu1GjiLIJc0Ir3ziLII46pivOG', 'Administrator', 'admin');

-- Jadwal SENIN
INSERT INTO schedules (day, time, name, bell_type_id) VALUES
('Senin', '06:45', 'Bel Masuk Sekolah', 1),
('Senin', '07:00', 'Bel Pelajaran 1', 2),
('Senin', '07:45', 'Bel Pelajaran 2', 3),
('Senin', '08:30', 'Bel Istirahat 1', 9),
('Senin', '08:45', 'Bel Masuk Setelah Istirahat 1', 10),
('Senin', '09:30', 'Bel Pelajaran 3', 4),
('Senin', '10:15', 'Bel Pelajaran 4', 5),
('Senin', '10:30', 'Bel Istirahat 2', 11),
('Senin', '10:45', 'Bel Masuk Setelah Istirahat 2', 12),
('Senin', '11:30', 'Bel Pelajaran 5', 6),
('Senin', '12:15', 'Bel Pelajaran 6', 7),
('Senin', '13:00', 'Bel Pulang', 13);

-- Jadwal SELASA
INSERT INTO schedules (day, time, name, bell_type_id) VALUES
('Selasa', '06:45', 'Bel Masuk Sekolah', 1),
('Selasa', '07:00', 'Bel Pelajaran 1', 2),
('Selasa', '07:45', 'Bel Pelajaran 2', 3),
('Selasa', '08:30', 'Bel Istirahat 1', 9),
('Selasa', '08:45', 'Bel Masuk Setelah Istirahat 1', 10),
('Selasa', '09:30', 'Bel Pelajaran 3', 4),
('Selasa', '10:15', 'Bel Pelajaran 4', 5),
('Selasa', '10:30', 'Bel Istirahat 2', 11),
('Selasa', '10:45', 'Bel Masuk Setelah Istirahat 2', 12),
('Selasa', '11:30', 'Bel Pelajaran 5', 6),
('Selasa', '12:15', 'Bel Pelajaran 6', 7),
('Selasa', '13:00', 'Bel Pulang', 13);

-- Jadwal RABU
INSERT INTO schedules (day, time, name, bell_type_id) VALUES
('Rabu', '06:45', 'Bel Masuk Sekolah', 1),
('Rabu', '07:00', 'Bel Pelajaran 1', 2),
('Rabu', '07:45', 'Bel Pelajaran 2', 3),
('Rabu', '08:30', 'Bel Istirahat 1', 9),
('Rabu', '08:45', 'Bel Masuk Setelah Istirahat 1', 10),
('Rabu', '09:30', 'Bel Pelajaran 3', 4),
('Rabu', '10:15', 'Bel Pelajaran 4', 5),
('Rabu', '10:30', 'Bel Istirahat 2', 11),
('Rabu', '10:45', 'Bel Masuk Setelah Istirahat 2', 12),
('Rabu', '11:30', 'Bel Pelajaran 5', 6),
('Rabu', '12:15', 'Bel Pelajaran 6', 7),
('Rabu', '13:00', 'Bel Pulang', 13);

-- Jadwal KAMIS
INSERT INTO schedules (day, time, name, bell_type_id) VALUES
('Kamis', '06:45', 'Bel Masuk Sekolah', 1),
('Kamis', '07:00', 'Bel Pelajaran 1', 2),
('Kamis', '07:45', 'Bel Pelajaran 2', 3),
('Kamis', '08:30', 'Bel Istirahat 1', 9),
('Kamis', '08:45', 'Bel Masuk Setelah Istirahat 1', 10),
('Kamis', '09:30', 'Bel Pelajaran 3', 4),
('Kamis', '10:15', 'Bel Pelajaran 4', 5),
('Kamis', '10:30', 'Bel Istirahat 2', 11),
('Kamis', '10:45', 'Bel Masuk Setelah Istirahat 2', 12),
('Kamis', '11:30', 'Bel Pelajaran 5', 6),
('Kamis', '12:15', 'Bel Pelajaran 6', 7),
('Kamis', '13:00', 'Bel Pulang', 13);

-- Jadwal JUMAT
INSERT INTO schedules (day, time, name, bell_type_id) VALUES
('Jumat', '06:45', 'Bel Masuk Sekolah', 1),
('Jumat', '07:00', 'Bel Pelajaran 1', 2),
('Jumat', '07:45', 'Bel Pelajaran 2', 3),
('Jumat', '08:30', 'Bel Istirahat', 9),
('Jumat', '09:00', 'Bel Masuk Setelah Istirahat', 10),
('Jumat', '09:45', 'Bel Pelajaran 3', 4),
('Jumat', '10:30', 'Bel Pelajaran 4', 5),
('Jumat', '11:00', 'Bel Pulang', 13);

-- Jadwal SABTU
INSERT INTO schedules (day, time, name, bell_type_id) VALUES
('Sabtu', '06:45', 'Bel Masuk Sekolah', 1),
('Sabtu', '07:00', 'Bel Pelajaran 1', 2),
('Sabtu', '07:45', 'Bel Pelajaran 2', 3),
('Sabtu', '08:30', 'Bel Istirahat 1', 9),
('Sabtu', '08:45', 'Bel Masuk Setelah Istirahat 1', 10),
('Sabtu', '09:30', 'Bel Pelajaran 3', 4),
('Sabtu', '10:15', 'Bel Pelajaran 4', 5),
('Sabtu', '10:30', 'Bel Pulang', 13);

-- Hari libur nasional 2026
INSERT INTO holidays (date, name, description) VALUES
('2026-01-01', 'Tahun Baru', 'Perayaan Tahun Baru Masehi'),
('2026-01-27', 'Isra Mi''raj', 'Isra Mi''raj Nabi Muhammad SAW'),
('2026-02-01', 'Tahun Baru Imlek', 'Perayaan Tahun Baru Imlek 2577'),
('2026-03-29', 'Wafat Isa Almasih', 'Wafatnya Isa Almasih'),
('2026-04-01', 'Hari Raya Nyepi', 'Tahun Baru Saka 1948'),
('2026-04-10', 'Idul Fitri', 'Idul Fitri 1447 H'),
('2026-04-11', 'Cuti Bersama Idul Fitri', 'Cuti bersama Idul Fitri'),
('2026-05-01', 'Hari Buruh', 'Hari Buruh Internasional'),
('2026-05-14', 'Waisak', 'Hari Raya Waisak 2570'),
('2026-05-29', 'Kenaikan Isa Almasih', 'Kenaikan Isa Almasih'),
('2026-06-01', 'Hari Lahir Pancasila', 'Hari Lahir Pancasila'),
('2026-06-17', 'Idul Adha', 'Idul Adha 1447 H'),
('2026-07-08', 'Tahun Baru Islam', '1 Muharram 1448 H'),
('2026-08-17', 'Hari Kemerdekaan RI', 'HUT RI ke-81'),
('2026-09-05', 'Maulid Nabi', 'Maulid Nabi Muhammad SAW'),
('2026-12-25', 'Hari Raya Natal', 'Hari Raya Natal');

-- Riwayat contoh
INSERT INTO bell_history (date, time, schedule_name, bell_type, status, mode) VALUES
('2026-08-24', '06:45', 'Bel Masuk Sekolah', 'Bel Masuk', 'berhasil', 'otomatis'),
('2026-08-24', '07:00', 'Bel Pelajaran 1', 'Bel Pergantian Pelajaran', 'berhasil', 'otomatis'),
('2026-08-24', '08:30', 'Bel Istirahat 1', 'Bel Istirahat', 'berhasil', 'otomatis'),
('2026-08-24', '13:00', 'Bel Pulang', 'Bel Pulang', 'berhasil', 'otomatis'),
('2026-08-25', '06:45', 'Bel Masuk Sekolah', 'Bel Masuk', 'berhasil', 'otomatis'),
('2026-08-25', '07:00', 'Bel Pelajaran 1', 'Bel Pergantian Pelajaran', 'berhasil', 'otomatis'),
('2026-08-25', '08:30', 'Bel Istirahat 1', 'Bel Istirahat', 'berhasil', 'manual');