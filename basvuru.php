<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>PiA Bootcamp Başvuru Formu</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f9; display: flex; justify-content: center; padding: 50px; }
        .container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 500px; }
        h2 { text-align: center; color: #463e66; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #333; }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #00ADB5; color: white; border: none; border-radius: 5px; margin-top: 20px; font-size: 16px; cursor: pointer; }
        button:hover { background: #008c93; }
    </style>
</head>
<body>

<div class="container">
    <h2>Kamp Başvuru Formu</h2>
    <form action="basvuru_kaydet.php" method="POST" enctype="multipart/form-data">
        
        <label>TC Kimlik No:</label>
        <input type="text" name="tc_kimlik" required maxlength="11" placeholder="11 haneli TC">

        <label>Ad Soyad:</label>
        <input type="text" name="ad_soyad" required>

        <label>E-posta:</label>
        <input type="email" name="email" required>

        <label>Eğitim Durumu:</label>
        <select name="egitim_durumu">
            <option value="lisans">Lisans</option>
            <option value="yuksek_lisans">Yüksek Lisans</option>
            <option value="diger">Diğer</option>
        </select>

        <label>Motivasyon Mektubu:</label>
        <textarea name="motivasyon_metni" rows="4" required placeholder="Neden katılmak istiyorsun?"></textarea>

        <label>CV Yükle (PDF):</label>
        <input type="file" name="ozgecmis" accept=".pdf" required>

        <label>Öğrenci Belgesi (PDF/Resim):</label>
        <input type="file" name="belge" accept=".pdf,.jpg,.png" required>

        <button type="submit">Başvuruyu Gönder</button>
    </form>
</div>

</body>
</html>