<?php
require_once 'config/koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM tb_absensi WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: index.php?msg=hapus_sukses");
    } else {
        echo "Gagal hapus data!";
    }

    $stmt->close();
}
?>