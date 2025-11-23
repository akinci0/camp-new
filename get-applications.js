// Serverless Fonksiyonu (Node.js) - get-applications.js

const mysql = require('mysql2/promise');

// MySQL bağlantı ayarları (Environment Variables'dan alınır)
const dbConfig = {
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    // Bağlantı performansını artırmak için connection pool kullanılması önerilir.
};

// Bu, API endpoint'inizin ana işlevidir.
exports.handler = async (event) => {
    let connection;

    // Admin Paneli farklı bir domain'de (GitHub Pages) olduğu için CORS ayarı KRİTİKTİR.
    const headers = {
        'Access-Control-Allow-Origin': '*', // Tüm originlere izin ver
        'Access-Control-Allow-Methods': 'GET, OPTIONS',
        'Content-Type': 'application/json',
    };
    
    // Tarayıcıların yaptığı ön kontrol (preflight) isteğini yönetme
    if (event.httpMethod === 'OPTIONS') {
        return {
            statusCode: 200,
            headers,
            body: JSON.stringify({ message: 'CORS Preflight Success' })
        };
    }

    try {
        // 1. MySQL Bağlantısını Kurma
        // Connection Pool kullanılması önerilir, ancak basitlik için tekil bağlantı kuralım.
        connection = await mysql.createConnection(dbConfig);

        // 2. SQL Sorgusu: Tablonuzdaki tüm ilgili kolonları çekiyoruz.
        // TC kimlik, ad, e-posta, motivasyon ve durum kolonları Admin Paneli için zorunludur.
        const [rows] = await connection.execute(
            `
            SELECT 
                tc_kimlik, 
                ad_soyad, 
                email, 
                egitim_durumu,
                motivasyon_metni,
                ozgecmis_path,
                ogrenci_belgesi_path,
                durum,
                created_at
            FROM basvurular
            ORDER BY created_at DESC;
            `
        );
        
        // 3. Başarılı Yanıt (Veriyi JSON formatında döndürür)
        return {
            statusCode: 200,
            headers,
            body: JSON.stringify(rows), // Veritabanından gelen tüm satırları JSON olarak döndür
        };

    } catch (error) {
        console.error("Veritabanı veya Sunucusuz Fonksiyon Hatası:", error);

        // 4. Hata Yanıtı
        return {
            statusCode: 500,
            headers,
            body: JSON.stringify({ message: "Veritabanı bağlantısı veya sorgu hatası oluştu.", error: error.message }),
        };

    } finally {
        // Bağlantıyı kapat
        if (connection) {
            await connection.end();
        }
    }
};