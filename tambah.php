<?php
require_once 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_siswa = trim($_POST['nama_siswa']);
    $kelas      = trim($_POST['kelas']);
    $tanggal    = $_POST['tanggal'];
    $status     = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO tb_absensi (nama_siswa, kelas, tanggal, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama_siswa, $kelas, $tanggal, $status);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Gagal menyimpan data!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Siswa</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            padding: 20px;
        }

        .form-container {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background: #198754;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background: #157347;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 10px;
            text-decoration: none;
            color: #333;
        }

        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Tambah Siswa</h2>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <input type="text" name="nama_siswa" placeholder="Nama Siswa" required>
        <input type="text" name="kelas" placeholder="Kelas" required>
        <input type="date" name="tanggal" required>

        <select name="status">
            <option value="Hadir">Hadir</option>
            <option value="Izin">Izin</option>
            <option value="Sakit">Sakit</option>
        </select>

        <button type="submit" class="btn">Simpan</button>
    </form>

    <a href="index.php" class="back">← Kembali</a>
</div>

</body>
</html>