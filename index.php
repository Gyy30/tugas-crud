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

/* ================= DATA CHART (PER TANGGAL) ================= */
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

$tanggal = [];
$chart_hadir = [];
$chart_izin = [];
$chart_sakit = [];

while($c = $chart->fetch_assoc()){
    $tanggal[] = $c['tanggal'];
    $chart_hadir[] = $c['hadir'];
    $chart_izin[] = $c['izin'];
    $chart_sakit[] = $c['sakit'];
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
body { font-family:'Inter',sans-serif; background:#f1f5f9; }

.sidebar{
width:220px;height:100vh;position:fixed;
background:#111827;color:white;padding:20px;
}
.sidebar a{
display:block;color:#cbd5e1;padding:10px;
border-radius:10px;text-decoration:none;
}
.sidebar a:hover{background:#1f2937;color:white}

.content{margin-left:240px;padding:20px}

.card{border:none;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.05)}

.stat-card{background:white;padding:12px;border-radius:12px}
.green{border-left:4px solid #22c55e}
.yellow{border-left:4px solid #facc15}
.red{border-left:4px solid #ef4444}

.table thead{background:#4f46e5;color:white}
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
<div class="row g-3 mb-4">
<div class="col-md-3"><div class="stat-card"><b>Total:</b> <?= $total ?></div></div>
<div class="col-md-3"><div class="stat-card green"><b>Hadir:</b> <?= $hadir ?></div></div>
<div class="col-md-3"><div class="stat-card yellow"><b>Izin:</b> <?= $izin ?></div></div>
<div class="col-md-3"><div class="stat-card red"><b>Sakit:</b> <?= $sakit ?></div></div>
</div>

<!-- CHART GARIS -->
<div class="card p-3 mb-4">
<canvas id="chart"></canvas>
</div>

<!-- FILTER -->
<div class="card p-3 mb-3">
<form method="GET" class="row g-2">
<input type="date" name="dari" class="form-control col" value="<?= $_GET['dari'] ?? '' ?>">
<input type="date" name="sampai" class="form-control col" value="<?= $_GET['sampai'] ?? '' ?>">
<button class="btn btn-primary col">Filter</button>
<a href="index.php" class="btn btn-secondary col">Reset</a>
<a href="?export=1&dari=<?= $_GET['dari'] ?? '' ?>&sampai=<?= $_GET['sampai'] ?? '' ?>" class="btn btn-success col">Export</a>
</form>
</div>

<!-- TABLE -->
<div class="card p-3">
<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah</button>

<table class="table text-center">
<thead>
<tr><th>Nama</th><th>Kelas</th><th>Status</th><th>Aksi</th></tr>
</thead>
<tbody>

<?php while($row=$data->fetch_assoc()){ ?>
<tr>
<td><?= $row['nama_siswa'] ?></td>
<td><?= $row['kelas'] ?></td>
<td><?= $row['status'] ?></td>
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

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah">
<div class="modal-dialog">
<form method="POST" class="modal-content p-3">
<h5>Tambah Data</h5>
<input type="text" name="nama_siswa" class="form-control mb-2" placeholder="Nama">
<input type="text" name="kelas" class="form-control mb-2" placeholder="Kelas">
<input type="date" name="tanggal" class="form-control mb-2">
<select name="status" class="form-control mb-2">
<option>Hadir</option>
<option>Izin</option>
<option>Sakit</option>
</select>
<button name="tambah" class="btn btn-success">Simpan</button>
</form>
</div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="modalEdit">
<div class="modal-dialog">
<form method="POST" class="modal-content p-3">
<input type="hidden" name="id" id="edit_id">
<h5>Edit Data</h5>
<input type="text" name="nama_siswa" id="edit_nama" class="form-control mb-2">
<input type="text" name="kelas" id="edit_kelas" class="form-control mb-2">
<input type="date" name="tanggal" id="edit_tanggal" class="form-control mb-2">
<select name="status" id="edit_status" class="form-control mb-2">
<option>Hadir</option>
<option>Izin</option>
<option>Sakit</option>
</select>
<button name="edit" class="btn btn-primary">Update</button>
</form>
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
Swal.fire({title:'Hapus?',showCancelButton:true}).then(r=>{
if(r.isConfirmed){
window.location="hapus.php?id="+id;
}
});
}

// LINE CHART
new Chart(document.getElementById('chart'), {
type: 'line',
data: {
labels: <?= json_encode($tanggal) ?>,
datasets: [
{
label: 'Hadir',
data: <?= json_encode($chart_hadir) ?>
},
{
label: 'Izin',
data: <?= json_encode($chart_izin) ?>
},
{
label: 'Sakit',
data: <?= json_encode($chart_sakit) ?>
}
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