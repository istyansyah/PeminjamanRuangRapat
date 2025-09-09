CREATE DATABASE peminjaman_ruang;
USE peminjaman_ruang;

-- Tabel Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Ruangan
CREATE TABLE ruangan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_ruangan VARCHAR(100) NOT NULL,
    kapasitas INT NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Fasilitas
CREATE TABLE fasilitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_ruangan INT,
    nama_fasilitas VARCHAR(100) NOT NULL,
    jumlah INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ruangan) REFERENCES ruangan(id) ON DELETE CASCADE
);

-- Tabel Peminjaman
CREATE TABLE peminjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    id_ruangan INT NOT NULL,
    agenda TEXT NOT NULL,
    nama_peminjam VARCHAR(100) NOT NULL,
    divisi VARCHAR(100) NOT NULL,
    tanggal DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    nota_dinas VARCHAR(255) NOT NULL,
    status ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_ruangan) REFERENCES ruangan(id) ON DELETE CASCADE
);

-- Tabel Peminjaman Fasilitas
CREATE TABLE peminjaman_fasilitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_peminjaman INT NOT NULL,
    nama_fasilitas VARCHAR(100) NOT NULL,
    jumlah INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id) ON DELETE CASCADE
);

-- Insert data admin default password admin : password
INSERT INTO users (nama, email, password, role) VALUES 
('Administrator', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert beberapa ruangan contoh
INSERT INTO ruangan (nama_ruangan, kapasitas, lokasi) VALUES 
('Ruang Rapat A', 20, 'Lantai 1 Gedung Utama'),
('Ruang Rapat B', 15, 'Lantai 2 Gedung Utama'),
('Ruang Rapat Executive', 10, 'Lantai 3 Gedung Manajemen');

-- Insert fasilitas contoh
INSERT INTO fasilitas (id_ruangan, nama_fasilitas, jumlah) VALUES 
(1, 'Proyektor', 1),
(1, 'Kursi', 20),
(1, 'Meja Rapat', 1),
(2, 'Proyektor', 1),
(2, 'Kursi', 15),
(2, 'Whiteboard', 1),
(3, 'TV LED', 1),
(3, 'Kursi Executive', 10),
(3, 'Meja Executive', 1);