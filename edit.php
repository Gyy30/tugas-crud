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
}

/* ================= HAPUS ================= */
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $stmt = $conn->prepare("DELETE FROM tb_absensi WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: ?msg=hapus");
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
body { background:#f1f5f9; }
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

    <div id="pagination" class="text-center mt-3"></div>
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

// SEARCH
document.getElementById("search").addEventListener("keyup", function(){
    let val = this.value.toLowerCase();
    document.querySelectorAll("#table tbody tr").forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(val) ? "" : "none";
    });
});

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
        icon:'warning',
        showCancelButton:true
    }).then((result)=>{
        if(result.isConfirmed){
            window.location = "?hapus="+id;
        }
    });
}

// PAGINATION
let rows = document.querySelectorAll("#table tbody tr");
let perPage = 5;
function showPage(page){
    rows.forEach((row,i)=>{
        row.style.display = (i >= (page-1)*perPage && i < page*perPage) ? "" : "none";
    });

    let total = Math.ceil(rows.length/perPage);
    let html="";
    for(let i=1;i<=total;i++){
        html+=`<button class='btn btn-sm ${i==page?'btn-primary':'btn-light'} m-1' onclick='showPage(${i})'>${i}</button>`;
    }
    document.getElementById("pagination").innerHTML = html;
}
showPage(1);

// NOTIF
const msg = new URLSearchParams(window.location.search).get('msg');
if(msg){
    Swal.fire({
        icon:'success',
        title:'Berhasil!',
        text: msg + ' data berhasil',
        timer:1500,
        showConfirmButton:false
    });
}
</script>

</body>
</html>