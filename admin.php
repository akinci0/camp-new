<?php
// panel.php - İstatistik Hesaplamaları Eklenmiş Tam Sürüm

include 'baglanti.php'; 

if ($conn->connect_error) {
    die("Veritabanı hatası: " . $conn->connect_error);
}

// --- 1. TABLO İÇİN TÜM VERİLERİ ÇEK ---
$sql = "SELECT * FROM basvurular ORDER BY created_at DESC";
$result = $conn->query($sql);

// --- 2. İSTATİSTİKLERİ HESAPLA ---
// Gruplandırılmış veriyi çekelim (Yıl ve Duruma göre)
$sql_stats = "SELECT YEAR(created_at) as yil, durum, COUNT(*) as sayi FROM basvurular GROUP BY yil, durum";
$result_stats = $conn->query($sql_stats);

$stats_data = [];
$bekleyen_sayisi = 0;
$kabul_sayisi = 0;

// Verileri döngüye alıp hem grafik için hazırlayalım hem de kartlardaki sayıları toplayalım
while($row = $result_stats->fetch_assoc()) { 
    $stats_data[] = $row;
    
    // Bekleyenleri topla
    if ($row['durum'] == 'beklemede') {
        $bekleyen_sayisi += $row['sayi'];
    }
    // Kabul edilenleri topla
    if ($row['durum'] == 'kabul') {
        $kabul_sayisi += $row['sayi'];
    }
}

$json_stats = json_encode($stats_data);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>PiA Yönetim Paneli</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* SIFIRLAMA VE TEMEL */
        :root {
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 70px;
            --primary-color: #463e66; /* KOYU MOR */
            --accent-color: #00ADB5;  /* TURKUAZ */
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background: #F8F9FA; display: flex; transition: 0.3s; }

        /* --- SIDEBAR (SOL MENÜ) --- */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background-color: var(--primary-color);
            color: white;
            position: fixed;
            top: 0; left: 0;
            display: flex; flex-direction: column;
            padding: 20px;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            transition: width 0.3s;
            overflow: hidden;
            z-index: 1000;
        }
        
        .sidebar.collapsed { width: var(--sidebar-width-collapsed); padding: 20px 10px; }
        .sidebar.collapsed h2 span, .sidebar.collapsed .menu-text { display: none; }
        .sidebar.collapsed .menu-item { text-align: center; padding: 12px 0; }
        .sidebar.collapsed .sidebar-header { justify-content: center; }

        .sidebar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; white-space: nowrap; }
        .sidebar h2 { margin: 0; font-size: 1.4rem; }
        .toggle-btn { background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; }

        .menu-item { 
            padding: 12px 15px; color: #ddd; text-decoration: none; border-radius: 5px; margin-bottom: 5px; 
            display: flex; align-items: center; gap: 15px; transition: 0.3s; white-space: nowrap;
        }
        .menu-item:hover, .menu-item.active { background: var(--accent-color); color: white; }
        .menu-item i { font-size: 1.2rem; min-width: 25px; }

        /* --- İÇERİK ALANI --- */
        .main { margin-left: var(--sidebar-width); padding: 30px; width: 100%; transition: margin-left 0.3s; }
        .main.collapsed { margin-left: var(--sidebar-width-collapsed); }
        
        /* KARTLAR & KUTULAR */
        .cards { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { flex: 1; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid var(--accent-color); }
        .charts { display: flex; gap: 20px; margin-bottom: 30px; height: 350px; }
        .chart-box { flex: 1; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .table-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: var(--accent-color); color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f1f1f1; }

        .btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; color: white; display: inline-block; margin-right: 5px; }
        .btn-cv { background: var(--primary-color); }
        .btn-belge { background: #FFC107; color: black; }
        select { padding: 5px; border-radius: 4px; border: 1px solid #ccc; }
        
        .section-title { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; color: #333; }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><span>PiA Panel</span></h2>
            <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        </div>
        <a href="#genel-bakis" class="menu-item active"><i class="fas fa-chart-line"></i> <span class="menu-text">Genel Bakış</span></a>
        <a href="#basvuru-listesi" class="menu-item"><i class="fas fa-users"></i> <span class="menu-text">Başvurular</span></a>
        <a href="#ayarlar" class="menu-item"><i class="fas fa-cog"></i> <span class="menu-text">Ayarlar</span></a>
    </div>

    <div class="main" id="mainContent">
        
        <div id="genel-bakis" style="padding-top: 20px;">
            <h2 class="section-title">Genel Durum</h2>
            
            <div class="cards">
                <div class="card">
                    <h3>Toplam Başvuru</h3>
                    <h1><?php echo $result->num_rows; ?></h1>
                </div>
                <div class="card" style="border-color: #FFC107;">
                    <h3>Bekleyen İşlemler</h3>
                    <h1><?php echo $bekleyen_sayisi; ?></h1>
                </div>
                <div class="card" style="border-color: #28a745;">
                    <h3>Kabul Edilen</h3>
                    <h1><?php echo $kabul_sayisi; ?></h1>
                </div>
            </div>

            <div class="charts">
                <div class="chart-box">
                    <h3>Başvuru Trendi</h3>
                    <canvas id="barChart"></canvas>
                </div>
                <div class="chart-box">
                    <h3>Durum Dağılımı</h3>
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>

        <div id="basvuru-listesi" class="table-box" style="margin-top: 50px;">
            <h2 class="section-title">Başvuru Listesi</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Motivasyon</th>
                        <th>Dosyalar</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0) {
                        $result->data_seek(0); // Tablo için veri işaretçisini başa al
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td><strong>".$row['ad_soyad']."</strong><br><small>".$row['tc_kimlik']."</small></td>
                                <td>".$row['email']."</td>
                                <td>".substr($row['motivasyon_metni'],0,40)."...</td>
                                <td>
                                    <a href='".$row['ozgecmis_path']."' class='btn btn-cv' target='_blank'>CV</a>
                                    <a href='".$row['ogrenci_belgesi_path']."' class='btn btn-belge' target='_blank'>Belge</a>
                                </td>
                                <td>
                                    <select onchange=\"updateStatus('".$row['tc_kimlik']."', this.value)\">
                                        <option ".($row['durum']=='beklemede'?'selected':'')." value='beklemede'>Beklemede</option>
                                        <option ".($row['durum']=='kabul'?'selected':'')." value='kabul'>Kabul</option>
                                        <option ".($row['durum']=='ret'?'selected':'')." value='ret'>Ret</option>
                                        <option ".($row['durum']=='incelendi'?'selected':'')." value='incelendi'>İncelendi</option>
                                    </select>
                                </td>
                            </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('collapsed');
        }

        const stats = <?php echo $json_stats; ?>;
        
        const years = [...new Set(stats.map(item => item.yil))];
        const yearlyCounts = years.map(year => stats.filter(s => s.yil == year).reduce((a,b)=>a+parseInt(b.sayi),0));
        
        const statusCounts = { 'kabul': 0, 'ret': 0, 'beklemede': 0, 'incelendi': 0 };
        stats.forEach(s => { if(statusCounts[s.durum] !== undefined) statusCounts[s.durum] += parseInt(s.sayi); });

        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: years,
                datasets: [{ label: 'Başvuru Sayısı', data: yearlyCounts, backgroundColor: '#00ADB5', borderRadius: 5 }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: ['Kabul', 'Ret', 'Beklemede', 'İncelendi'],
                datasets: [{ data: [statusCounts.kabul, statusCounts.ret, statusCounts.beklemede, statusCounts.incelendi], backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8'] }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        function updateStatus(tc, durum) {
            fetch('update_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ tc_kimlik: tc, yeni_durum: durum })
            }).then(r => r.json()).then(d => {
                if(d.success) {
                    alert('Durum güncellendi!');
                    location.reload(); // Sayfayı yenile ki yukarıdaki sayaçlar güncellensin
                }
                else alert('Hata: ' + d.message);
            });
        }
    </script>
</body>
</html>