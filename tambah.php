<?php
require_once 'config/koneksi.php';

// kalau tombol simpan ditekan
if (isset($_POST['simpan'])) {
    $nama    = $_POST['nama_siswa'];
    $kelas   = $_POST['kelas'];
    $tanggal = $_POST['tanggal'];
    $status  = $_POST['status'];

    // query insert
    $stmt = $conn->prepare("INSERT INTO tb_absensi (nama_siswa, kelas, tanggal, status) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $nama, $kelas, $tanggal, $status);

    if ($stmt->execute()) {
        header("Location: index.php?msg=tambah");
        exit;
    } else {
        echo "Gagal menyimpan data: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Data</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container py-5">
    <div class="card p-4">
        <h4 class="mb-3">Tambah Data Absensi</h4>

        <form method="POST">
            <input type="text" name="nama_siswa" class="form-control mb-2" placeholder="Nama Siswa" required>
            <input type="text" name="kelas" class="form-control mb-2" placeholder="Kelas" required>
            <input type="date" name="tanggal" class="form-control mb-2" required>

            <select name="status" class="form-control mb-3">
                <option>Hadir</option>
                <option>Izin</option>
                <option>Sakit</option>
            </select>

            <button name="simpan" class="btn btn-success">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

</body>
</html>