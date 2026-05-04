<?php
require_once 'config/koneksi.php';

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM tb_absensi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
?>

<form method="POST">
    <input type="text" name="nama_siswa" value="<?= $data['nama_siswa']; ?>" required>
    <input type="text" name="kelas" value="<?= $data['kelas']; ?>" required>
    <input type="date" name="tanggal" value="<?= $data['tanggal']; ?>" required>

    <select name="status">
        <option value="Hadir" <?= $data['status']=='Hadir'?'selected':''; ?>>Hadir</option>
        <option value="Izin" <?= $data['status']=='Izin'?'selected':''; ?>>Izin</option>
        <option value="Sakit" <?= $data['status']=='Sakit'?'selected':''; ?>>Sakit</option>
    </select>

    <button type="submit">Update</button>
</form>