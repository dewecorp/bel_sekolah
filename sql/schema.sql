-- =====================================================
-- Bel Sekolah Digital - Schema Database MySQL
-- =====================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL DEFAULT 'Administrator',
    role VARCHAR(20) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bell_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bell_types_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audio_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    filename VARCHAR(100) NOT NULL,
    filepath VARCHAR(255) NOT NULL,
    bell_type_id INT UNSIGNED NULL,
    is_default TINYINT(1) DEFAULT 0,
    volume DECIMAL(3,2) DEFAULT 0.80,
    duration INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bell_type_id) REFERENCES bell_types(id) ON DELETE SET NULL,
    INDEX idx_audio_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    day VARCHAR(10) NOT NULL,
    time CHAR(5) NOT NULL,
    name VARCHAR(100) NOT NULL,
    bell_type_id INT UNSIGNED NULL,
    audio_id INT UNSIGNED NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bell_type_id) REFERENCES bell_types(id) ON DELETE SET NULL,
    FOREIGN KEY (audio_id) REFERENCES audio_files(id) ON DELETE SET NULL,
    INDEX idx_schedules_day (day),
    INDEX idx_schedules_active (is_active),
    INDEX idx_schedules_day_time (day, time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS holidays (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_holidays_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
    id TINYINT UNSIGNED PRIMARY KEY,
    school_name VARCHAR(150) NOT NULL DEFAULT 'Sekolah Digital Indonesia',
    school_logo VARCHAR(255) NULL,
    school_address VARCHAR(255) DEFAULT '',
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    time_format ENUM('12','24') DEFAULT '24',
    default_volume DECIMAL(3,2) DEFAULT 0.80,
    bell_duration INT DEFAULT 5,
    system_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bell_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    time CHAR(5) NOT NULL,
    schedule_name VARCHAR(150) NOT NULL,
    bell_type VARCHAR(100) NOT NULL DEFAULT 'Umum',
    status ENUM('berhasil','gagal') DEFAULT 'berhasil',
    mode ENUM('otomatis','manual') DEFAULT 'otomatis',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_history_date (date),
    INDEX idx_history_dt (date, time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;