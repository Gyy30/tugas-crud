<?php
require_once 'config/koneksi.php';

/* ================= FILTER ================= */
$where = "";
if(isset($_GET['dari']) && isset($_GET['sampai']) && $_GET['dari'] != "" && $_GET['sampai'] != ""){
    $dari = $_GET['dari'];
    $sampai = $_GET['sampai'];
    $where = "WHERE tanggal BETWEEN '$dari' AND '$sampai'";
}

/* ================= EXPORT ================= */
if(isset($_GET['export'])){
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=data_absensi.csv");

    $output = fopen("php://output", "w");
    fputcsv($output, ['Nama','Kelas','Tanggal','Status']);

    $export = $conn->query("SELECT * FROM tb_absensi $where");
    while($row = $export->fetch_assoc()){
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

/* ================= TAMBAH ================= */
if (isset($_POST['tambah'])) {
    $stmt = $conn->prepare("INSERT INTO tb_absensi (nama_siswa, kelas, tanggal, status) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $_POST['nama_siswa'], $_POST['kelas'], $_POST['tanggal'], $_POST['status']);
    $stmt->execute();
    header("Location: ?msg=tambah"); exit;
}

/* ================= EDIT ================= */
if (isset($_POST['edit'])) {
    $stmt = $conn->prepare("UPDATE tb_absensi SET nama_siswa=?, kelas=?, tanggal=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $_POST['nama_siswa'], $_POST['kelas'], $_POST['tanggal'], $_POST['status'], $_POST['id']);
    $stmt->execute();
    header("Location: ?msg=edit"); exit;
}

/* ================= DATA ================= */
$data = $conn->query("SELECT * FROM tb_absensi $where ORDER BY id DESC");

/* ================= STATS ================= */
$filter = $where ? "$where AND" : "WHERE";

$total = $conn->query("SELECT COUNT(*) as t FROM tb_absensi $where")->fetch_assoc()['t'];
$hadir = $conn->query("SELECT COUNT(*) as t FROM tb_absensi $filter status='Hadir'")->fetch_assoc()['t'];
$izin  = $conn->query("SELECT COUNT(*) as t FROM tb_absensi $filter status='Izin'")->fetch_assoc()['t'];
$sakit = $conn->query("SELECT COUNT(*) as t FROM tb_absensi $filter status='Sakit'")->fetch_assoc()['t'];

/* ================= CHART ================= */
$chart = $conn->query("
    SELECT tanggal,
    SUM(status='Hadir') as hadir,
    SUM(status='Izin') as izin,
    SUM(status='Sakit') as sakit
    FROM tb_absensi
    $where
    GROUP BY tanggal
    ORDER BY tanggal ASC
");

$tanggal=[]; $chart_hadir=[]; $chart_izin=[]; $chart_sakit=[];
while($c=$chart->fetch_assoc()){
    $tanggal[]=$c['tanggal'];
    $chart_hadir[]=$c['hadir'];
    $chart_izin[]=$c['izin'];
    $chart_sakit[]=$c['sakit'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Absensi</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body { background:#eef2f7; font-family:'Inter',sans-serif; }

/* SIDEBAR */
.sidebar{
width:230px;height:100vh;position:fixed;
background:linear-gradient(180deg,#1e293b,#0f172a);
color:white;padding:20px;
}
.sidebar a{
display:block;color:#cbd5e1;padding:10px;
border-radius:10px;text-decoration:none;
}
.sidebar a:hover{background:#334155;color:white}

/* CONTENT */
.content{margin-left:250px;padding:25px}

/* CARD */
.card{
border:none;border-radius:16px;
box-shadow:0 8px 25px rgba(0,0,0,0.05)
}

/* STAT */
.stat-card{
padding:18px;border-radius:16px;color:white
}
.bg-total{background:linear-gradient(135deg,#6366f1,#4f46e5)}
.bg-hadir{background:linear-gradient(135deg,#22c55e,#16a34a)}
.bg-izin{background:linear-gradient(135deg,#facc15,#eab308)}
.bg-sakit{background:linear-gradient(135deg,#ef4444,#dc2626)}

.table thead{background:#4f46e5;color:white}
.table tbody tr:hover{background:#eef2ff}
.btn{border-radius:10px}

/* STATUS GLOW */
.status {
padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;
display:inline-flex;align-items:center;gap:6px;
}
.status-hadir {background:#16a34a;color:white;box-shadow:0 0 12px #22c55e;}
.status-izin {background:#ca8a04;color:white;box-shadow:0 0 12px #facc15;}
.status-sakit {background:#dc2626;color:white;box-shadow:0 0 12px #ef4444;}

/* 🔥 PREMIUM BUTTON */
.btn-premium {
border:none;padding:10px 18px;border-radius:12px;font-weight:600;
transition:0.25s;backdrop-filter:blur(10px);
}

.btn-filter {background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;box-shadow:0 4px 15px rgba(37,99,235,0.4);}
.btn-filter:hover {transform:translateY(-2px);}

.btn-reset {background:#64748b;color:white;}
.btn-reset:hover {background:#475569;}

.btn-export {background:linear-gradient(135deg,#22c55e,#16a34a);color:white;}
.btn-export:hover {transform:translateY(-2px);}

.btn-tambah-premium {
width:100%;padding:12px;border-radius:14px;
background:linear-gradient(135deg,#16a34a,#15803d);
color:white;font-weight:600;
box-shadow:0 6px 20px rgba(22,163,74,0.4);
}
.btn-tambah-premium:hover {transform:scale(1.02);}
</style>
</head>

<body>

<div class="sidebar">
<h4>📊 Absensi</h4>
<a href="#">Dashboard</a>
</div>

<div class="content">

<h4 class="mb-4">Dashboard</h4>

<!-- STAT -->
<div class="row g-4 mb-4">
<div class="col-md-3"><div class="stat-card bg-total"><h6>Total</h6><h4><?= $total ?></h4></div></div>
<div class="col-md-3"><div class="stat-card bg-hadir"><h6>Hadir</h6><h4><?= $hadir ?></h4></div></div>
<div class="col-md-3"><div class="stat-card bg-izin"><h6>Izin</h6><h4><?= $izin ?></h4></div></div>
<div class="col-md-3"><div class="stat-card bg-sakit"><h6>Sakit</h6><h4><?= $sakit ?></h4></div></div>
</div>

<!-- CHART -->
<div class="card p-4 mb-4">
<h6>Statistik Kehadiran</h6>
<canvas id="chart" height="100"></canvas>
</div>

<!-- FILTER -->
<div class="card p-3 mb-3">
<form method="GET" class="row g-2">
<input type="date" name="dari" class="form-control col" value="<?= $_GET['dari'] ?? '' ?>">
<input type="date" name="sampai" class="form-control col" value="<?= $_GET['sampai'] ?? '' ?>">

<button class="btn btn-premium btn-filter col">🔍 Filter</button>
<a href="index.php" class="btn btn-premium btn-reset col">♻ Reset</a>
<a href="?export=1&dari=<?= $_GET['dari'] ?? '' ?>&sampai=<?= $_GET['sampai'] ?? '' ?>" class="btn btn-premium btn-export col">⬇ Export</a>
</form>
</div>

<!-- TABLE -->
<div class="card p-3">
<button class="btn-tambah-premium mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">➕ Tambah Data</button>

<table class="table text-center">
<thead><tr><th>Nama</th><th>Kelas</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>

<?php while($row=$data->fetch_assoc()){ 
$status=$row['status'];
$class=$status=='Hadir'?'status-hadir':($status=='Izin'?'status-izin':'status-sakit');
$icon=$status=='Hadir'?'✔':($status=='Izin'?'⚠':'✖');
?>

<tr>
<td><?= $row['nama_siswa'] ?></td>
<td><?= $row['kelas'] ?></td>
<td><span class="status <?= $class ?>"><?= $icon ?> <?= $status ?></span></td>
<td>
<button class="btn btn-primary btn-sm"
onclick="editData('<?= $row['id'] ?>','<?= $row['nama_siswa'] ?>','<?= $row['kelas'] ?>','<?= $row['tanggal'] ?>','<?= $row['status'] ?>')">Edit</button>

<button class="btn btn-danger btn-sm"
onclick="hapusData(<?= $row['id'] ?>)">Hapus</button>
</td>
</tr>

<?php } ?>

</tbody>
</table>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// EDIT
function editData(id,nama,kelas,tanggal,status){
edit_id.value=id;
edit_nama.value=nama;
edit_kelas.value=kelas;
edit_tanggal.value=tanggal;
edit_status.value=status;
new bootstrap.Modal(document.getElementById('modalEdit')).show();
}

// HAPUS
function hapusData(id){
Swal.fire({title:'Hapus data?',showCancelButton:true}).then(r=>{
if(r.isConfirmed){ window.location="hapus.php?id="+id; }
});
}

// CHART
new Chart(document.getElementById('chart'), {
type:'line',
data:{
labels: <?= json_encode($tanggal) ?>,
datasets:[
{label:'Hadir',data:<?= json_encode($chart_hadir) ?>,borderWidth:3,tension:0.4},
{label:'Izin',data:<?= json_encode($chart_izin) ?>,borderWidth:3,tension:0.4},
{label:'Sakit',data:<?= json_encode($chart_sakit) ?>,borderWidth:3,tension:0.4}
]
}
});

// NOTIF
const msg=new URLSearchParams(window.location.search).get('msg');
if(msg){
Swal.fire({icon:'success',title:'Berhasil',timer:1200,showConfirmButton:false});
history.replaceState({},'',location.pathname);
}
</script>

</body>
</html>