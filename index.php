<?php
require_once 'config/koneksi.php';

$query = "SELECT * FROM tb_absensi ORDER BY id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Absensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        .table-container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn-tambah {
            background: #198754;
            color: white;
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn-edit {
            background: #0d6efd;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 5px;
        }

        .btn-hapus {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background: #4CAF50;
            color: white;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f2f2f2;
        }

        tbody tr:hover {
            background: #e0f7fa;
            transition: 0.3s;
        }
    </style>
</head>
<body>

<div class="table-container">
    <h2>Data Absensi Siswa</h2>

    <!-- TOMBOL TAMBAH -->
    <div style="display:flex; justify-content:flex-end; margin-bottom:10px;">
        <a href="tambah.php" class="btn-tambah">+ Tambah Siswa</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>

        <?php
        if ($result->num_rows > 0) {
            $no = 1;
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['id']}</td>
                        <td>{$row['nama_siswa']}</td>
                        <td>{$row['tanggal']}</td>
                        <td>{$row['status']}</td>
                        <td>
                            <a href='edit.php?id=".$row['id']."' class='btn-edit'>Edit</a>
                            <a href='hapus.php?id=".$row['id']."' class='btn-hapus' onclick='return confirm(\"Yakin mau hapus?\")'>Hapus</a>
                        </td>
                      </tr>";
                $no++;
            }
        } else {
            echo "<tr><td colspan='6'>Data tidak ditemukan</td></tr>";
        }
        ?>

        </tbody>
    </table>
</div>

</body>
</html>