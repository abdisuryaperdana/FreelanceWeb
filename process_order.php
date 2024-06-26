<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPekerjaan = $_POST['idPekerjaan'];
    $deskripsiOrder = $_POST['deskripsiOrder'];
    $klienId = $_SESSION['user_id'];

    if (empty($deskripsiOrder)) {
        ?>
<!DOCTYPE html>
<html>

<head>
    <title>Error - Deskripsi Order Kosong</title>
    <!-- Tambahkan CSS atau styling sesuai kebutuhan -->
    <style>
    .error-container {
        text-align: center;
        margin-top: 50px;
    }

    .error-message {
        font-size: 18px;
        color: red;
        margin-bottom: 20px;
    }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-message">
            Deskripsi Order tidak boleh kosong.
        </div>
        <a href="javascript:history.back()" class="btn btn-primary">Kembali</a>
    </div>
</body>

</html>
<?php
        exit(); // Hentikan eksekusi script jika deskripsi order kosong
    }
    
    $targetDirectory = __DIR__.'/assets/img/'; // Folder tujuan untuk menyimpan file
    $targetFile = $targetDirectory . basename($_FILES["fileOrder"]["name"]); // Path lengkap file yang akan diunggah
    $uploadOk = 1; // Flag untuk menandai apakah pengunggahan berhasil atau gagal

    // Validasi file kosong
    if ($_FILES["fileOrder"]["size"] == 0) {
        $file = ""; // Set file kosong jika tidak diunggah
    } else {
        // Cek apakah file sudah ada
        // if (file_exists($targetFile)) {
        //     echo "Maaf, file sudah ada.";
        //     $uploadOk = 0;
        // }

        // Batasi ukuran file (contoh diset maksimal 15MB)
        if ($_FILES["fileOrder"]["size"] > 15 * 1024 * 1024) {
            echo "Maaf, ukuran file terlalu besar.";
            $uploadOk = 0;
        }

        // Izinkan hanya beberapa tipe file tertentu (misalnya: hanya gambar)
        $allowedExtensions = array("jpg", "pdf", "jpeg", "png", "gif");
        $fileExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo "Maaf, hanya file JPG, JPEG, pdf, PNG, dan GIF yang diizinkan.";
            $uploadOk = 0;
        }

        // Periksa apakah uploadOk masih bernilai 0
        if ($uploadOk == 0) {
            echo "Maaf, file tidak diunggah.";
            die;
        } else { // Jika semua syarat terpenuhi, coba unggah file
            if (move_uploaded_file($_FILES["fileOrder"]["tmp_name"], $targetFile)) {
                echo "File " . basename($_FILES["fileOrder"]["name"]) . " berhasil diunggah.";
            } else {
                echo "Maaf, terjadi kesalahan saat mengunggah file.";
                die;
            }

            $file = $_FILES["fileOrder"]["name"];
        }
    }

   
    $queryInsertOrder = "INSERT INTO order_table (deskripsi_order, klien_id, id_pekerjaan, `file`) VALUES ('$deskripsiOrder', $klienId, $idPekerjaan, '$file')";

if ($conn->query($queryInsertOrder) === TRUE) {
    $lastOrderId = $conn->insert_id;
    // Ubah status_pekerjaan menjadi 'sudah dipesan'
    $queryUpdateStatus = "UPDATE pekerjaan SET status_pekerjaan = 'sudah dipesan' WHERE id_pekerjaan = $idPekerjaan";

    if ($conn->query($queryUpdateStatus) === TRUE) {
        // Jika berhasil mengubah status_pekerjaan, redirect ke halaman pembayaran
        $user_id = $_SESSION['user_id'];
        $notification_type = "Order Berhasil";
        $message = "Pesanan Anda berhasil dipesan, silahkan lakukan pembayaran!";
    
        // Menyimpan notifikasi status selesai ke dalam tabel notifications
        $queryInsertNotification = "INSERT INTO notifications (user_id, notification_type, message, created_at, is_read) VALUES ($user_id, '$notification_type', '$message', CURRENT_TIMESTAMP, 0)";

        if ($conn->query($queryInsertNotification) === TRUE) {
            // Notifikasi status selesai berhasil disimpan ke dalam tabel notifications
            header("Location: halaman_pembayaran.php?order_id=$lastOrderId");
            exit();
        } else {
            // Gagal menyimpan notifikasi status selesai
            echo "Gagal menyimpan notifikasi status selesai: " . $conn->error;
            exit();
        }
    } else {
        echo "Gagal mengubah status pekerjaan.";
        exit();
    }
} else {
    echo "Error: " . $queryInsertOrder . "<br>" . $conn->error;
}


    // Tambahkan logic notifikasi setelah order berhasil dimasukkan ke database



}
?>