-- Tạo database mới
CREATE DATABASE IF NOT EXISTS quanly_tintuc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quanly_tintuc;

-- Tạo bảng loại tin
CREATE TABLE IF NOT EXISTS loaitin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenloai VARCHAR(255) NOT NULL,
    trangthai TINYINT(1) DEFAULT 1
);

-- Tạo bảng users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('viewer', 'baiviet', 'loaitin', 'admin') NOT NULL DEFAULT 'viewer'
);

-- Tạo bảng bài viết
CREATE TABLE IF NOT EXISTS baiviet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tieude VARCHAR(255) NOT NULL,
    noidung TEXT NOT NULL,
    loaitin_id INT NOT NULL,
    ngaytao DATETIME DEFAULT CURRENT_TIMESTAMP,
    tacgia VARCHAR(100),
    FOREIGN KEY (loaitin_id) REFERENCES loaitin(id)
);

-- Thêm user admin mẫu (mật khẩu: 123456)
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$wH6QwQwQwQwQwQwQwQwQwOQwQwQwQwQwQwQwQwQwQwQwQwQwQw', 'admin')
    ON DUPLICATE KEY UPDATE username=username;

-- Thêm dữ liệu mẫu cho loaitin
INSERT INTO loaitin (tenloai, trangthai) VALUES
('Văn hóa', 1),
('Xã hội', 1),
('Kinh Tế', 1),
('Giáo dục', 1),
('Y tế', 1),
('Khoa học', 1),
('Tin tức chung', 1),
('Thông báo', 1)
ON DUPLICATE KEY UPDATE tenloai=tenloai;

ALTER TABLE loaitin ADD COLUMN tenloai VARCHAR(255) NOT NULL AFTER id;
