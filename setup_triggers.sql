-- Script ini HARUS dijalankan di Database (phpMyAdmin / SQL Console)
-- Tujuannya agar stok buku otomatis berkurang saat dipinjam, dan bertambah saat dikembalikan/ditolak.

-- 1. Trigger Kurangi Stok (Saat Checkout / Insert Detail Peminjaman)
DROP TRIGGER IF EXISTS kurangi_stok;
DELIMITER //
CREATE TRIGGER kurangi_stok 
AFTER INSERT ON detail_peminjaman
FOR EACH ROW
BEGIN
    UPDATE buku SET stok = stok - 1 WHERE id_buku = NEW.id_buku;
END //
DELIMITER ;

-- 2. Trigger Kembalikan Stok (Saat Admin Update Status ke 'kembali' atau 'ditolak')
DROP TRIGGER IF EXISTS kembalikan_stok_update;
DELIMITER //
CREATE TRIGGER kembalikan_stok_update
AFTER UPDATE ON peminjaman
FOR EACH ROW
BEGIN
    -- Cek jika status berubah menjadi 'kembali' ATAU 'ditolak'
    -- DAN status sebelumnya BUKAN 'kembali' atau 'ditolak' (untuk mencegah double counting jika di-update berkali-kali)
    IF (NEW.status IN ('kembali', 'ditolak')) AND (OLD.status NOT IN ('kembali', 'ditolak')) THEN
        -- Update stok buku yang berelasi dengan id_peminjaman ini
        UPDATE buku b
        JOIN detail_peminjaman dp ON b.id_buku = dp.id_buku
        SET b.stok = b.stok + 1
        WHERE dp.id_peminjaman = NEW.id_peminjaman;
    END IF;
END //
DELIMITER ;
