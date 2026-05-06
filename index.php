<?php
require_once 'config/koneksi.php';

/* ================= TAMBAH ================= */
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_siswa'];
    $kelas = $_POST['kelas'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO tb_absensi (nama_siswa, kelas, tanggal, status) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $nama, $kelas, $tanggal, $status);
    $stmt->execute();

    header("Location: ?msg=tambah");
    exit;
}

/* ================= EDIT ================= */
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_siswa'];
    $kelas = $_POST['kelas'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tb_absensi SET nama_siswa=?, kelas=?, tanggal=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $nama, $kelas, $tanggal, $status, $id);
    $stmt->execute();

    header("Location: ?msg=edit");
    exit;
}

/* ================= DATA ================= */
$result = $conn->query("SELECT * FROM tb_absensi ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Absensi</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body { background:#f1f5f9; transition:0.3s; }
.dark-mode { background:#0f172a; color:white; }
.card { border-radius:15px; }
.btn { border-radius:25px; }
.table tbody tr:hover { background:#eef2ff; }
.dark-mode .table { color:white; }
</style>
</head>

<body>

<div class="container py-4">

<!-- HEADER -->
<div class="d-flex justify-content-between mb-3">
    <h3>📊 Dashboard Absensi</h3>
    <div>
        <button class="btn btn-dark" onclick="toggleDark()">🌙</button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah</button>
    </div>
</div>

<!-- TABLE -->
<div class="card p-3">
    <input type="text" id="search" class="form-control mb-3" placeholder="Cari...">

    <table class="table text-center" id="table">
        <thead class="table-primary">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>

        <?php $no=1; while($row=$result->fetch_assoc()){ ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['nama_siswa'] ?></td>
            <td><?= $row['kelas'] ?></td>
            <td><?= $row['tanggal'] ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <button class="btn btn-primary btn-sm"
                    onclick="editData('<?= $row['id'] ?>','<?= $row['nama_siswa'] ?>','<?= $row['kelas'] ?>','<?= $row['tanggal'] ?>','<?= $row['status'] ?>')">
                    ✏️
                </button>

                <button class="btn btn-danger btn-sm"
                    onclick="hapusData(<?= $row['id'] ?>)">
                    🗑
                </button>
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
    <input type="text" name="nama_siswa" class="form-control mb-2" placeholder="Nama" required>
    <input type="text" name="kelas" class="form-control mb-2" placeholder="Kelas" required>
    <input type="date" name="tanggal" class="form-control mb-2" required>
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
// DARK MODE
function toggleDark(){
    document.body.classList.toggle("dark-mode");
}

// EDIT MODAL
function editData(id,nama,kelas,tanggal,status){
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_nama").value = nama;
    document.getElementById("edit_kelas").value = kelas;
    document.getElementById("edit_tanggal").value = tanggal;
    document.getElementById("edit_status").value = status;

    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}

// HAPUS
function hapusData(id){
    Swal.fire({
        title:'Yakin hapus?',
        text:'Data tidak bisa dikembalikan!',
        icon:'warning',
        showCancelButton:true
    }).then((result)=>{
        if(result.isConfirmed){
            window.location.href = "hapus.php?id=" + id;
        }
    });
}

// 🔍 SEARCH
document.getElementById("search").addEventListener("keyup", function(){
    let val = this.value.toLowerCase();
    document.querySelectorAll("#table tbody tr").forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(val) ? "" : "none";
    });
});

// 🔥 NOTIF SEKALI (FIX REFRESH)
const url = new URL(window.location);
const msg = url.searchParams.get('msg');

if(msg){
    let text = '';

    if(msg === 'tambah') text = 'Data berhasil ditambah';
    if(msg === 'edit') text = 'Data berhasil diupdate';
    if(msg === 'hapus') text = 'Data berhasil dihapus';

    Swal.fire({
        icon:'success',
        title:'Berhasil!',
        text:text,
        timer:1500,
        showConfirmButton:false
    });

    // hapus parameter dari URL
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>

</body>
</html>