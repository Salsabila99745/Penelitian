<?php

include "../../inc/config.php";
include "../../inc/functions.php";

ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
error_reporting(E_ALL);

$id_user        = $_POST['id_user'];
$kode_encrypt   = $_POST['kode_encrypt'];
$pesan          = $_POST['pesan'];

$result1        = $mysqli->query("SELECT kode_encrypt FROM tbl_encrypt WHERE kode_encrypt='$kode_encrypt'");
$data1          = $result1->num_rows;

if ($data1 > 0) {
  //kode encrypt sudah terdaftar
  echo "<script>document.location.href='../?mod=encrypt pg=form_input encrypt kode_encrypt=$kode_encrypt&pesan=$pesan&act=ks'</script>";
} else {

  //Proses Spread Spectrum -> menentukan gambar yang akan menjadi media penyisipan ciphertext (cover image) 
  $file_type     = array('png');
  $post_max_size = 20000000; // 20MB 
  $post_min_size = 100000; // 100KB 
  $file_name     = $_FILES['file']['name'];
  $file_tmp      = $_FILES['file']['tmp_name'];
  $file_size     = $_FILES['file']['size'];
  //cari extensi file dengan menggunakan fungsi explode
  $explode       = explode('.', $file_name);
  $extensi       = $explode[count($explode) - 1];
  $datein        = "encrypt-" . date("Y-M-DHis");
  $file_simpan   = $datein . '.' . $extensi;

  //check apakah type file sudah sesuai
  if (!in_array($extensi, $file_type)) {
    @$eror    = true;
    @$act    .= 'ts';
  }
  //check ukuran file apakah lebih dari 10 MB
  else if ($file_size > $post_max_size) {
    @$eror   = true;
    @$act    .= 'uk';
  }
  //check ukuran file apakah kurang dari 1 MB
  else if ($file_size < $post_min_size) {
    @$eror   = true;
    @$act    .= 'ukm';
  }

  if (@$eror == true) {
    echo "<script>document.location.href='../?mod=encrypt&pg=form_input_encrypt&kode_encrypt=$kode_encrypt&pesan=$pesan&act=$act'</script>";
  } else {
    //---------------------------------------------------------------------------------------
    //PROSES AES 128 CBC
    //PROSES AES -> 1. Transformasi AddRoundKey pada proses enkripsi pertama kali pada round = 0 
    $key            = $kode_encrypt;
    $plaintext      = $pesan;
    //PROSES AES -> 2. SubBytes merupakan transformasi byte dimana setiap elemen pada state akan dipetakan dengan menggunakan sebuah tabel substitusi ( S-Box ).  Untuk setiap byte pada array state.
    $ivlen          = openssl_cipher_iv_length($cipher = "AES-128-CBC");
    //PROSES AES -> 3. Transformasi Shiftrows pada dasarnya adalah proses pergeseran bit dimana bit paling kiri akan dipindahkan menjadi bit paling kanan ( rotasi bit ). 
    $iv             = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    //PROSES AES -> 4. MixColumns mengoperasikan setiap elemen yang berada dalam satu kolom pada state, jika bit sesuai maka masuk kedalam mixColumns
    $hmac           = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
    //PROSES AES -> 5. AddRoundKey: melakukan XOR antara state sekarang dengan round key
    $ciphertext     = base64_encode($iv . $hmac . $ciphertext_raw);
    //---------------------------------------------------------------------------------------

    // Number of bytes in an integer.
    $INTEGER_BYTES = 4;
    $BYTE_BITS = 8;

    //Proses Spread Spectrum -> memasukkan pesan informasi sebagai ciphertext untuk disisipkan  
    $message = $ciphertext;

    //Proses Spread Spectrum -> menentukan key file yang akan digunakan sebagai password dalam proses extract 
    $binaryMessage = toBinary($message);
    // Jumlah bit yang terkandung dalam pesan, alias ukuran payload sebagai bilangan bulat
    $messageLength = strlen($binaryMessage);
    // Mengubah message length mmenjadi biner dan juga pastikan untuk mengisinya dengan 32 bit
    $binaryMessageLength = str_pad(decbin($messageLength), $INTEGER_BYTES * $BYTE_BITS, "0", STR_PAD_LEFT);

    // The payload will incorporate the length and the message.
    $payload = $binaryMessageLength . $binaryMessage;

    //Proses Spread Spectrum -> Penyisipan file ke dalam gambar 
    $src    = $file_tmp; // diambil dari proses penyisipan pesan (cover image) di atas
    $image  = imagecreatefrompng($src); //format image berupa png bawaan dari PHP 
    // menentukan ukuran file gambar dan mengembalikan dimensi bersama dengan tipe file dan string height
    $size   = getimagesize($src); 
    $width  = $size[0]; // variabel untuk ukuran x
    $height = $size[1]; // variabel untuk ukuran y

    //function encodePayload(string $payload, $image, $width, $height) {
    function encodePayload($payload, $image, $width, $height){ // deklarasi fungsi bernama encodePayload 
      $payloadLength = strlen($payload); // untuk menghitung panjang dari data yang disisipkan

      // menyimpan 128 bit per piksel (1 spektrum tersebar untuk setiap saluran warna) kali lebar, kali tinggi.
      // kondisi yang memeriksa apakah panjang data yang akan disisipkan melebihi kapasitas yang ditentukan
      if ($payloadLength > $width * $height * 3) { 
        echo "Image not big enough to hold data."; 
        return false;
      }

      $bitIndex = 0; // inisialisasi variabel 
      for ($y = 0; $y < $height; $y++) { //loop untuk setiap baris (tinggi) pada gambar
        for ($x = 0; $x < $width; $x++) { // loop untuk setiap kolom (tinggi) pada gambar
          $rgb = imagecolorat($image, $x, $y); // untuk mendapatkan nilai RGB dari piksel 
          // setiap nilai saluran warna diekstrak dari integer asli.
          $r = ($rgb >> 16) & 0xFF; //untuk mengambil nilai (R) dari piksel untuk pergeseran bit ke kanan 
          $g = ($rgb >> 8) & 0xFF; //untuk mengambil nilai (G) dari piksel untuk pergeseran bit ke kanan 
          $b = $rgb & 0xFF; //untuk mengambil nilai (B) dari piksel untuk pergeseran bit ke kanan 

          //Melakukan penyisipan bit dari payload ke komponen warna
          $r = ($bitIndex < $payloadLength) ? (($r & 0xFE) | $payload[$bitIndex++]) : $r; 
          $g = ($bitIndex < $payloadLength) ? (($g & 0xFE) | $payload[$bitIndex++]) : $g;
          $b = ($bitIndex < $payloadLength) ? (($b & 0xFE) | $payload[$bitIndex++]) : $b;

          // Pembentukan objek warna baru berdasarkan RGB yang sudah dimodifikasi sebelumnya
          $color = imagecolorallocate($image, $r, $g, $b);
          imagesetpixel($image, $x, $y, $color); //untuk mengatur warna piksel 

          //kondisi yg memeriksa apakah semua bit payload sudah disisipkan ke dalam gambar
          if ($bitIndex >= $payloadLength) { 
            return true;
          }
        }
      }
    }

    if (encodePayload($payload, $image, $width, $height)) { //kondisi untuk memeriksa fungsi encodePayload
      //Proses Spread Spectrum -> memetakan menjadi gambar baru / memasukkan gambar baru kedalam database
      //Mengeksekusi query untuk memasukkan informasi terkait hasil penyisipan data ke tabel 'tbl_encrypt'
      $result = $mysqli->query("INSERT INTO tbl_encrypt VALUES(null,$id_user,'$kode_encrypt','$pesan','$ciphertext','$file_simpan')");
      echo "<script>document.location.href='../?mod=encrypt&pg=form_input_encrypt&key=$key&act=db'</script>";
    } else {
      echo "<script>document.location.href='../?mod=encrypt&pg=form_input_encrypt&act=dg'</script>";
    }

    imagepng($image, '../../file/encrypt/' . $file_simpan); //menyimpan gambar hasil penyisipan 
    imagedestroy($image); 
  }
}
