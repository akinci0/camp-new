// server.js - Node.js Backend Sunucusu

const express = require('express');
const mysql = require('mysql2/promise');
const cors = require('cors');
const bodyParser = require('body-parser'); // JSON verisi okumak için

const app = express();
const PORT = 3000;

// Middleware
app.use(cors()); // CORS'u etkinleştir (Front-end'in veri çekmesi için)
app.use(bodyParser.json()); // JSON body'sini okumak için

// MySQL Bağlantı Ayarları (Yerel test için)
const dbConfig = {
    host: '127.0.0.1', 
    user: 'root',
    password: 'root', 
    database: 'proje', 
    port: 8889 // Veya MAMP portunuz (Örn: 8889)
};

// --- API Endpoint: Veri Çekme (/api/applications) ---
app.get('/api/applications', async (req, res) => {
    let connection;
    try {
        connection = await mysql.createConnection(dbConfig);
        
        // 1. Ana Tablo Verileri
        const [applications] = await connection.execute("SELECT tc_kimlik, ad_soyad, email, egitim_durumu, motivasyon_metni, ozgecmis_path, ogrenci_belgesi_path, durum, created_at FROM basvurular ORDER BY created_at DESC");
        
        // 2. Grafik Verileri
        const [stats] = await connection.execute("SELECT YEAR(created_at) AS Yil, durum, COUNT(*) AS Sayi FROM basvurular GROUP BY Yil, durum ORDER BY Yil DESC, durum");

        res.json({
            success: true,
            applications: applications,
            statistics: stats
        });

    } catch (error) {
        console.error("DB/Sorgu Hatası:", error);
        res.status(500).json({ success: false, message: "Veritabanı bağlantısı veya sorgu hatası." });
    } finally {
        if (connection) connection.end();
    }
});

// --- API Endpoint: Karar Güncelleme (/api/update-decision) ---
app.post('/api/update-decision', async (req, res) => {
    const { tc_kimlik, yeni_durum } = req.body;
    let connection;

    if (!tc_kimlik || !yeni_durum) {
        return res.status(400).json({ success: false, message: 'Eksik TC Kimlik veya Durum bilgisi.' });
    }

    try {
        connection = await mysql.createConnection(dbConfig);
        const [result] = await connection.execute("UPDATE basvurular SET durum = ?, updated_at = NOW() WHERE tc_kimlik = ?", [yeni_durum, tc_kimlik]);

        if (result.affectedRows > 0) {
            res.json({ success: true, message: 'Başvuru durumu başarıyla güncellendi.' });
        } else {
            res.json({ success: false, message: 'Güncellenecek kayıt bulunamadı veya durum değişmedi.' });
        }
    } catch (error) {
        console.error("UPDATE Sorgu Hatası:", error);
        res.status(500).json({ success: false, message: 'Sunucu hatası: Güncelleme başarısız.' });
    } finally {
        if (connection) connection.end();
    }
});


app.listen(PORT, () => {
    console.log(`PiA Admin API'ı çalışıyor: http://localhost:${PORT}`);
    console.log(`Front-end'i http://localhost:8888/panel.php adresinde açın.`);
});