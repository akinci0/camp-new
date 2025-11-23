<?php
// panel.php - SON VE HATASIZ SÜRÜM (Tüm Grafikler Dahil)

include 'baglanti.php'; 

if ($conn->connect_error) {
    die("Veritabanı hatası: " . $conn->connect_error);
}

// --- 1. TABLO İÇİN TÜM VERİLERİ ÇEK ---
$sql = "SELECT * FROM basvurular ORDER BY created_at DESC";
$result = $conn->query($sql);

// --- 2. KART İSTATİSTİKLERİ ---
$sql_stats = "SELECT durum, COUNT(*) as sayi FROM basvurular GROUP BY durum";
$result_stats = $conn->query($sql_stats);

$bekleyen_sayisi = 0;
$kabul_sayisi = 0;
$toplam_basvuru = $result->num_rows; // Toplam sayıyı direkt buradan alalım

while($row = $result_stats->fetch_assoc()) { 
    if ($row['durum'] == 'beklemede') $bekleyen_sayisi += $row['sayi'];
    if ($row['durum'] == 'kabul') $kabul_sayisi += $row['sayi'];
}

// --- 3. GRAFİK VERİLERİ ---

// A) Yıllık Trend (DÜZELTİLDİ: Bu sorgu eksikti)
$sql_year = "SELECT YEAR(created_at) as yil, COUNT(*) as sayi FROM basvurular GROUP BY yil ORDER BY yil ASC";
$res_year = $conn->query($sql_year);
$data_year = []; 
while($r = $res_year->fetch_assoc()) { $data_year[] = $r; }

// B) Durum Dağılımı (Pasta Grafik İçin)
// Kart istatistikleri için çektiğimiz sorguyu tekrar kullanabiliriz veya temiz olsun diye tekrar çekebiliriz.
$sql_pie = "SELECT durum, COUNT(*) as sayi FROM basvurular GROUP BY durum";
$res_pie = $conn->query($sql_pie);
$data_pie = []; 
while($r = $res_pie->fetch_assoc()) { $data_pie[] = $r; }

// C) Eğitim Durumu
$sql_edu = "SELECT egitim_durumu, COUNT(*) as sayi FROM basvurular GROUP BY egitim_durumu";
$res_edu = $conn->query($sql_edu);
$data_edu = [];
while($r = $res_edu->fetch_assoc()) { $data_edu[] = $r; }

// D) Aylık Trend (Son 12 Ay)
$sql_month = "SELECT DATE_FORMAT(created_at, '%Y-%m') as ay, COUNT(*) as sayi FROM basvurular GROUP BY ay ORDER BY ay ASC LIMIT 12";
$res_month = $conn->query($sql_month);
$data_month = [];
while($r = $res_month->fetch_assoc()) { $data_month[] = $r; }

// Hepsini Tek JSON Paketinde Topla (DÜZELTİLDİ: 'year' eklendi)
$chart_data_package = [
    'year'  => $data_year,
    'pie'   => $data_pie,
    'edu'   => $data_edu,
    'month' => $data_month
];
$json_data = json_encode($chart_data_package);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>PiA Yönetim Paneli</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
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
        .main { margin-left: var(--sidebar-width); padding: 30px; width: 100%; transition: margin-left 0.3s; padding-bottom: 100px; }
        .main.collapsed { margin-left: var(--sidebar-width-collapsed); }
        
        /* KARTLAR */
        .cards { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { flex: 1; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid var(--accent-color); }
        .card h3 { margin: 0; font-size: 0.9rem; color: #666; }
        .card h1 { margin: 5px 0 0; font-size: 2rem; color: var(--primary-color); }

        /* GRAFİKLER */
        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .chart-box { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            height: 450px; 
            display: flex;
            flex-direction: column;
        }
        .chart-box h3 { margin-top: 0; color: #444; font-size: 1rem; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .chart-container { flex: 1; position: relative; }

        /* TABLO */
        .table-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: var(--accent-color); color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        tr:hover { background: #f1f1f1; }

        /* BUTONLAR VE İKONLAR */
        .btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; color: white; display: inline-block; margin-right: 5px; }
        .btn-cv { background: var(--primary-color); }
        .btn-belge { background: #FFC107; color: black; }
        
        .read-more-btn { 
            background: none; border: none; color: var(--primary-color); cursor: pointer; font-weight: bold; font-size: 0.9rem; 
            display: inline-flex; align-items: center; gap: 5px;
        }
        .read-more-btn:hover { text-decoration: underline; color: var(--accent-color); }

        select { padding: 5px; border-radius: 4px; border: 1px solid #ccc; }
        .section-title { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; color: #333; }

        /* AYARLAR KUTULARI */
        .settings-grid { display: flex; gap: 20px; flex-wrap: wrap; }
        .setting-card { flex: 1; min-width: 250px; background: #fff; padding: 25px; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .setting-card h3 { margin-top: 0; color: var(--primary-color); border-bottom: 1px solid #eee; padding-bottom: 10px; }

        /* MODAL */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); }
        .modal-content { background: white; margin: 10% auto; padding: 30px; width: 50%; border-radius: 10px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.3); animation: slideDown 0.3s ease-out; }
        .close-modal { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; position: absolute; right: 20px; top: 10px; }
        .close-modal:hover { color: black; }
        
        @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
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
        <a href="cikis.php" class="menu-item" style="margin-top:auto; background:#dc3545"><i class="fas fa-sign-out-alt"></i> <span class="menu-text">Çıkış</span></a>
    </div>

    <div class="main" id="mainContent">
        
        <div id="genel-bakis" style="padding-top: 10px;">
            <h2 class="section-title">Genel Durum</h2>
            
            <div class="cards">
                <div class="card">
                    <h3>Toplam Başvuru</h3>
                    <h1><?php echo $toplam_basvuru; ?></h1>
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

            <div class="charts-grid">
                <div class="chart-box">
                    <h3>📅 Yıllık Başvuru Trendi</h3>
                    <div class="chart-container"><canvas id="yearChart"></canvas></div>
                </div>
                <div class="chart-box">
                    <h3>📊 Başvuru Durumu</h3>
                    <div class="chart-container"><canvas id="pieChart"></canvas></div>
                </div>
                <div class="chart-box">
                    <h3>🎓 Eğitim Düzeyi Dağılımı</h3>
                    <div class="chart-container"><canvas id="eduChart"></canvas></div>
                </div>
                <div class="chart-box">
                    <h3>📈 Son Aylardaki Hareketlilik</h3>
                    <div class="chart-container"><canvas id="monthChart"></canvas></div>
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
                        $result->data_seek(0);
                        while($row = $result->fetch_assoc()) {
                            $fullText = htmlspecialchars($row['motivasyon_metni'], ENT_QUOTES);
                            $shortText = mb_substr($row['motivasyon_metni'], 0, 40, 'UTF-8') . '...';
                            
                            echo "<tr>
                                <td><strong>".$row['ad_soyad']."</strong><br><small style='color:#999'>".$row['tc_kimlik']."</small></td>
                                <td>".$row['email']."</td>
                                <td>
                                    ".$shortText."
                                    <button class='read-more-btn' onclick=\"openModal('".$fullText."', '".$row['ad_soyad']."')\">
                                        <i class='fa-solid fa-eye'></i> Oku
                                    </button>
                                </td>
                                <td>
                                    <a href='".$row['ozgecmis_path']."' class='btn btn-cv' target='_blank'>CV</a>
                                    <a href='".$row['ogrenci_belgesi_path']."' class='btn btn-belge' target='_blank'>Belge</a>
                                </td>
                                <td>
                                    <select onchange=\"updateStatus('".$row['tc_kimlik']."', this.value)\">
                                        <option ".($row['durum']=='beklemede'?'selected':'')." value='beklemede'>Beklemede</option>
                                        <option ".($row['durum']=='incelendi'?'selected':'')." value='incelendi'>İncelendi</option>
                                        <option ".($row['durum']=='kabul'?'selected':'')." value='kabul'>Kabul</option>
                                        <option ".($row['durum']=='ret'?'selected':'')." value='ret'>Ret</option>
                                    </select>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>Henüz başvuru yok.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="ayarlar" class="table-box" style="margin-top: 50px;">
            <h2 class="section-title">Sistem Ayarları</h2>
            
            <div class="settings-grid">
                <div class="setting-card">
                    <h3>📊 Veri İhracı</h3>
                    <p>Tüm başvuru verilerini Excel (CSV) formatında indir.</p>
                    <a href="export.php" class="btn btn-cv" style="display: block; text-align: center; padding: 15px; font-size: 1rem; margin-top: 15px;">
                        <i class="fas fa-file-download"></i> Listeyi İndir
                    </a>
                </div>
                <div class="setting-card">
                    <h3>🔒 Başvuru Dönemi</h3>
                    <p>Başvuruları geçici olarak kapat/aç.</p>
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-top: 15px;">
                        <input type="checkbox" checked onclick="alert('Durum Değiştirildi')">
                        <span style="font-weight:bold;">Başvurular Aktif</span>
                    </label>
                </div>
                <div class="setting-card">
                    <h3>🔑 Şifre Değiştir</h3>
                    <form id="passwordForm" style="margin-top: 15px;">
                        <input type="text" id="u_name" placeholder="Kullanıcı Adı" class="btn" style="width:100%; background:white; color:black; border:1px solid #ddd; margin-bottom:10px;">
                        <input type="password" id="o_pass" placeholder="Eski Şifre" class="btn" style="width:100%; background:white; color:black; border:1px solid #ddd; margin-bottom:10px;">
                        <input type="password" id="n_pass" placeholder="Yeni Şifre" class="btn" style="width:100%; background:white; color:black; border:1px solid #ddd; margin-bottom:10px;">
                        <button type="submit" class="btn btn-belge" style="width: 100%; padding:10px;">Güncelle</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <div id="motivationModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle" style="color: var(--primary-color); border-bottom: 1px solid #eee; padding-bottom: 10px;">Motivasyon Mektubu</h3>
            <p id="modalText" style="line-height: 1.6; color: #333; margin-top: 20px; font-size: 1.1rem;"></p>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('collapsed');
        }

        function openModal(text, name) {
            document.getElementById('modalText').innerText = text;
            document.getElementById('modalTitle').innerText = name + " - Motivasyon Mektubu";
            document.getElementById('motivationModal').style.display = "block";
        }

        function closeModal() {
            document.getElementById('motivationModal').style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('motivationModal')) closeModal();
        }

        // --- GRAFİKLER İÇİN VERİLERİ AL ---
        const data = <?php echo $json_data ?: '{}'; ?>;

        // 1. Yıllık Trend (Bar)
        if(data.year) {
            new Chart(document.getElementById('yearChart'), {
                type: 'bar',
                data: {
                    labels: data.year.map(d => d.yil),
                    datasets: [{ label: 'Başvuru Sayısı', data: data.year.map(d => d.sayi), backgroundColor: '#00ADB5', borderRadius: 5 }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // 2. Durum Dağılımı (Doughnut)
        if(data.pie) {
            const statusCounts = { 'kabul': 0, 'ret': 0, 'beklemede': 0, 'incelendi': 0 };
            data.pie.forEach(s => { if(statusCounts[s.durum] !== undefined) statusCounts[s.durum] = parseInt(s.sayi); });
            
            new Chart(document.getElementById('pieChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Kabul', 'Ret', 'Beklemede', 'İncelendi'],
                    datasets: [{ data: [statusCounts.kabul, statusCounts.ret, statusCounts.beklemede, statusCounts.incelendi], backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8'] }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // 3. Eğitim Durumu (Polar Area)
        if(data.edu) {
            const eduLabels = data.edu.map(d => d.egitim_durumu === 'diger' ? 'LİSE MEZUNU' : d.egitim_durumu.toUpperCase());
            const eduCounts = data.edu.map(d => d.sayi);

            new Chart(document.getElementById('eduChart'), {
                type: 'polarArea',
                data: { labels: eduLabels, datasets: [{ data: eduCounts, backgroundColor: ['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)'] }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // 4. Aylık Trend (Line)
        if(data.month) {
            new Chart(document.getElementById('monthChart'), {
                type: 'line',
                data: { labels: data.month.map(d => d.ay), datasets: [{ label: 'Aylık Hareket', data: data.month.map(d => d.sayi), borderColor: '#463e66', fill: true, backgroundColor: 'rgba(70, 62, 102, 0.1)' }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // --- AJAX ---
        function updateStatus(tc, st) {
            fetch('update_status.php', {
                method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({tc_kimlik:tc, yeni_durum:st})
            }).then(r=>r.json()).then(d=>{ if(d.success) { alert('Güncellendi!'); location.reload(); } else alert('Hata: ' + d.message); });
        }

        // Şifre Değiştirme
        document.getElementById('passwordForm').addEventListener('submit', function(e){
            e.preventDefault();
            const u = document.getElementById('u_name').value;
            const o = document.getElementById('o_pass').value;
            const n = document.getElementById('n_pass').value;
            fetch('sifre_degistir.php', {
                method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({username:u, old_pass:o, new_pass:n})
            }).then(r=>r.json()).then(d => alert(d.message));
        });
    </script>
</body>
</html>