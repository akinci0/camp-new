<?php
// admin.php - Tek Dosyalı Nihai Yapı

// Bu kodun çalışması için 'baglanti.php' dosyanızda $conn bağlantı nesnesinin tanımlı olması gerekmektedir.
include 'baglanti.php'; 

// Hata kontrolü
if ($conn->connect_error) {
    die("<div style='padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;'>
         <strong>KRİTİK HATA:</strong> Veritabanı bağlantısı başarısız. Hata: " . $conn->connect_error . "</div>");
}

// --- GRAFİKLER İÇİN VERİ HAZIRLIĞI (mysqli) ---
$sql_yearly_status = "
    SELECT YEAR(created_at) AS Yil, durum, COUNT(*) AS Sayi
    FROM basvurular
    GROUP BY Yil, durum
    ORDER BY Yil DESC, durum
";
$result_yearly_status = $conn->query($sql_yearly_status);

$yearly_status_data = [];
if ($result_yearly_status) {
    while($row = $result_yearly_status->fetch_assoc()) {
        $yearly_status_data[] = $row;
    }
}

$sql_main = "SELECT * FROM basvurular ORDER BY created_at DESC";
$result_main = $conn->query($sql_main);

$total_applications = ($result_main && $result_main->num_rows > 0) ? $result_main->num_rows : 0;

// JSON verisini güvenli aktarım için hazırlama (Grafik sorununu çözer)
$yearly_status_json = json_encode($yearly_status_data);
$yearly_status_json_clean = str_replace(array('\\', "'"), array('\\\\', '\\\''), $yearly_status_json);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>PiA Kamp Başvuruları Yönetim Paneli</title>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style> 
        /* --- GENEL STİLLER --- */
        :root { --primary-color: #0056B3; --accent-color: #FFC107; --secondary-color: #F8F9FA; --text-dark: #212529; }
        html, body { margin: 0; padding: 0; height: 100%; }
        body { font-family: sans-serif; background-color: #f4f4f9; display: flex; overflow-x: hidden; }
        
        /* --- SIDEBAR STİLİ --- */
        .sidebar {
            width: 250px; height: 100vh; background-color: var(--primary-color); color: white; padding: 20px;
            position: fixed; top: 0; left: 0; box-shadow: 2px 0 10px rgba(0,0,0,0.3); z-index: 100;
        }
        .sidebar h3 { font-size: 1.2em; margin-bottom: 25px; border-bottom: 1px solid rgba(255, 255, 255, 0.3); padding-bottom: 10px; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px 0; margin-bottom: 5px; opacity: 0.8; transition: opacity 0.2s; }
        .sidebar a:hover { opacity: 1; background-color: rgba(255, 255, 255, 0.1); padding-left: 10px; }
        .sidebar-stats { font-size: 1.5em; font-weight: bold; margin-top: 10px; }
        
        /* --- İÇERİK ALANI --- */
        .content {
            margin-left: 250px; padding: 20px; width: calc(100% - 250px); min-height: 100vh;
        }
        .admin-container { 
            background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            margin-bottom: 30px;
        }
        h2 { color: var(--primary-color); border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; margin-bottom: 20px; }
        
        /* Tablo ve Grafik Stilleri */
        .charts-container { display: flex; gap: 30px; margin-bottom: 40px; justify-content: space-around; flex-wrap: wrap; }
        .charts-container > div { width: 100%; max-width: 500px; }
        #appTable { width: 100%; border-collapse: collapse; margin-top: 30px; }
        #appTable th, #appTable td { border: 1px solid #ddd; padding: 12px; text-align: left; font-size: 0.9em; vertical-align: top; }
        #appTable th { background-color: var(--primary-color); color: white; }
        .btn-indir { background-color: var(--primary-color); color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; display: block; text-align: center; margin-top: 5px; }
        .btn-indir.belge { background-color:#ff9800; }
        .durum-kutusu { padding: 5px; border-radius: 4px; border: 1px solid #ccc; width: 100%; }
        
        /* Bildirim Stili */
        #notification { position: fixed; top: 10px; right: 10px; padding: 15px; border-radius: 5px; z-index: 1000; box-shadow: 0 0 10px rgba(0,0,0,0.5); display: none; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3>PiA Yönetim Paneli</h3>
        <a href="#dashboard">📊 Genel Durum</a>
        <a href="#basvuru-listesi">📄 Başvuru Listesi</a>
        <a href="#" onclick="alert('Yakında: Ayarlar')">⚙️ Ayarlar</a>
        
        <div style="margin-top: 50px;">
            <h3>İstatistik</h3>
            <div>Toplam Başvuru: <span class="sidebar-stats"><?php echo $total_applications; ?></span></div>
        </div>
    </div>
    
    <div class="content">

        <section id="dashboard" class="admin-container">
            <h2>📊 Yıllık Başvuru ve Analiz Raporu</h2>
            
            <div class="charts-container">
                <div style="width: 100%; max-width: 500px;">
                    <h3>Yıllık Başvuru Sayıları</h3>
                    <canvas id="yearlyApplicationsChart"></canvas>
                </div>
                
                <div style="width: 100%; max-width: 500px;">
                    <h3>Güncel Kabul Durumu Oranları</h3>
                    <canvas id="acceptanceRateChart"></canvas>
                </div>
            </div>
        </section>
        
        <section id="basvuru-listesi" class="admin-container">
            <h2>📄 Başvuru Detayları</h2>
            
            <table id="appTable">
                <thead>
                    <tr>
                        <th>TC Kimlik</th>
                        <th>Adı Soyadı</th>
                        <th>E-posta</th>
                        <th>Motivasyon (Önizleme)</th>
                        <th>Eğitim</th>
                        <th>Dosyalar</th>
                        <th>Tarih</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Tablo verilerini doldurma (mysqli)
                    if ($result_main && $result_main->num_rows > 0) {
                        while($row = $result_main->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["tc_kimlik"]) . "</td>";
                            echo "<td><strong>" . htmlspecialchars($row["ad_soyad"]) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                            
                            // Motivasyon (Önizleme)
                            $ozet = htmlspecialchars(substr($row["motivasyon_metni"], 0, 50)) . "...";
                            echo "<td>" . $ozet . "</td>";
                            
                            echo "<td>" . htmlspecialchars($row["egitim_durumu"]) . "</td>";

                            // Dosyalar (Linkler)
                            echo "<td>";
                            // NOT: ozgecmis_path ve ogrenci_belgesi_path kolonlarının varlığından emin olun!
                            if(isset($row["ozgecmis_path"]) && !empty($row["ozgecmis_path"])) {
                                echo "<a href='" . htmlspecialchars($row["ozgecmis_path"]) . "' target='_blank' class='btn-indir'>CV</a>";
                            }
                            if(isset($row["ogrenci_belgesi_path"]) && !empty($row["ogrenci_belgesi_path"])) {
                                echo "<a href='" . htmlspecialchars($row["ogrenci_belgesi_path"]) . "' target='_blank' class='btn-indir belge'>Belge</a>";
                            }
                            echo "</td>";
                            
                            echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
                            
                            // Durum (SELECT Kutusu) - data-tc ile TC Kimliği taşıyoruz
                            echo "<td>";
                            echo "<select class='durum-kutusu admin-decision' data-tc='" . htmlspecialchars($row['tc_kimlik']) . "'>";
                            echo "<option value='beklemede' " . ($row['durum'] == 'beklemede' ? 'selected' : '') . ">Beklemede</option>";
                            echo "<option value='incelendi' " . ($row['durum'] == 'incelendi' ? 'selected' : '') . ">İncelendi</option>";
                            echo "<option value='kabul' " . ($row['durum'] == 'kabul' ? 'selected' : '') . ">Kabul Edildi</option>";
                            echo "<option value='ret' " . ($row['durum'] == 'ret' ? 'selected' : '') . ">Reddedildi</option>";
                            echo "</select>";
                            echo "</td>";
                            
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align:center; padding:20px;'>Henüz hiç başvuru yok.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
        
    </div>
    
    <div id="notification"></div>

<script>
    // 1. PHP'DEN GELEN JSON VERİSİNİ ALMA (Grafik sorununu çözen güvenli aktarım)
    // Bu, PHP'den gelen temizlenmiş JSON string'ini güvenli bir şekilde alır.
    const yearlyStatusData = JSON.parse('<?php echo $yearly_status_json_clean; ?>');

    // --- YARDIMCI FONKSİYONLAR ---
    function showNotification(message, type) {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = '';
        notification.classList.add(type);
        notification.style.display = 'block';

        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
    
    // --- GRAFİK ÇİZME FONKSİYONLARI ---
    function drawYearlyChart() {
        const ctx = document.getElementById('yearlyApplicationsChart').getContext('2d');
        
        // Veri İşleme: Yıllara göre toplam sayıyı bulur
        const yearMap = new Map();
        yearlyStatusData.forEach(item => {
            const year = item.Yil;
            const count = parseInt(item.Sayi);
            yearMap.set(year, (yearMap.get(year) || 0) + count);
        });

        const years = Array.from(yearMap.keys()).sort();
        const totalCounts = Array.from(yearMap.values());

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: years,
                datasets: [{
                    label: 'Yıllık Toplam Başvuru',
                    data: totalCounts,
                    backgroundColor: 'rgba(0, 86, 179, 0.8)', // PiA Mavi
                    borderWidth: 1
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true, title: {display: true, text: 'Başvuru Sayısı'} } } }
        });
    }

    // 2. Kabul Durumu Oranları Çizimi (Pasta Grafik)
    function drawAcceptanceChart() {
        const ctx = document.getElementById('acceptanceRateChart').getContext('2d');

        const labelsMap = {
            'kabul': 'Kabul Edildi', 
            'ret': 'Reddedildi', 
            'beklemede': 'Beklemede',
            'incelendi': 'İnceleniyor'
        };

        const colorMap = {
            'kabul': '#4CAF50', // Yeşil
            'ret': '#DC3545', // Kırmızı
            'beklemede': '#17A2B8', // Mavi/Gri
            'incelendi': '#FFC107' // Turuncu
        };

        // Veriyi sadece güncel durumlar için grupla
        const statusCounts = {};
        yearlyStatusData.forEach(item => {
            if (statusCounts[item.durum]) {
                statusCounts[item.durum] += parseInt(item.Sayi);
            } else {
                statusCounts[item.durum] = parseInt(item.Sayi);
            }
        });

        const labels = Object.keys(statusCounts).map(key => labelsMap[key] || key);
        const counts = Object.values(statusCounts);
        const colors = Object.keys(statusCounts).map(key => colorMap[key] || '#6c757d');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Başvuru Durumu',
                    data: counts,
                    backgroundColor: colors,
                    hoverOffset: 4
                }]
            },
            options: { responsive: true }
        });
    }

    // --- JS İLE KARAR GÜNCELLEME (AJAX) MANTIĞI ---
    async function updateDecision(tcKimlik, yeniDurum) {
        
        try {
            const response = await fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ tc_kimlik: tcKimlik, yeni_durum: yeniDurum })
            });

            const result = await response.json();

            if (result.success) {
                showNotification(`Başvuru durumu başarıyla güncellendi: ${yeniDurum}`, 'success');
                // Grafikleri yenilemek için sayfayı yeniden yükle
                setTimeout(() => window.location.reload(), 1500); 
            } else {
                showNotification(`Hata: ${result.message}`, 'error');
            }
        } catch (error) {
             showNotification('Ağ hatası: Sunucuya ulaşılamıyor. Lütfen update_status.php dosyasını kontrol edin.', 'error');
             console.error('AJAX Error:', error);
        }
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        // Grafikleri çiz
        drawYearlyChart(); 
        drawAcceptanceChart(); 

        // Durum (Select) değişikliğini dinle
        document.addEventListener('change', function(event) {
            if (event.target.classList.contains('admin-decision')) {
                const select = event.target;
                const tcKimlik = select.getAttribute('data-tc');
                const yeniDurum = select.value;
                
                updateDecision(tcKimlik, yeniDurum);
            }
        });
    });
</script>
</body>
</html>