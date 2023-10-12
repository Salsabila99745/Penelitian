<?php 
  
  include "../../inc/config.php"; 


  $id_user        = $_POST['id_user']; 
  $kode_encrypt   = $_POST['kode_encrypt']; 
  $jenis_decrypt  = $_POST['jenis_decrypt'];
  $ciphertext     = $_POST['ciphertext'];

  if ($jenis_decrypt=='File'){  
  //Proses spread specrtum -> 1.  Memilih file gambar atau covert image yang akan di-extract
  $file_type     = array('png'); // type file
  $post_max_size = 20000000; // 20MB 
  $file_name     = $_FILES['file']['name'];  
  $file_tmp      = $_FILES['file']['tmp_name'];  
  $file_size     = $_FILES['file']['size'];  
  //cari extensi file dengan menggunakan fungsi explode
  $explode       = explode('.',$file_name);
  $extensi       = $explode[count($explode)-1];
  $datein        = "decrypt-".date("Y-M-DHis");
  $file_simpan   = $datein.'.'.$extensi;
 
  //check apakah type file sudah sesuai
    if(!in_array($extensi,$file_type)){
      @$eror    = true;
      @$act    .= 'ts';
    }
    //check ukuran file apakah lebih dari 10 MB
    if($file_size > $post_max_size){
      @$eror   = true;
      @$act    .= 'uk';
    }
    //check ukuran file apakah kurang dari 1 MB
    if($file_size < $post_min_size){
      @$eror   = true;
      @$act    .= 'ukm';
    }

  if(@$eror == true){
    echo "<script>document.location.href='../?mod=decrypt&pg=form_input_decrypt&kode_encrypt=$kode_encrypt&act=$act&jenis_decrypt=$jenis_decrypt'</script>";
  } else {
    $INTEGER_BITS = 32;
    $src    = $file_tmp;
    $image  = imagecreatefrompng($src);

    $size   = getimagesize($src);
    $width  = $size[0];
    $height = $size[1];

    // Returns the message length in bits as an integer.
    function decodeMessageLength($image, $width, $height) { //fungsi untuk menerima gambar, lebar, tinggi gambar
        // We need to process the first 32 of the image to retrieve the int.
        //PROSES AES -> 1.  PreRoud Ciphertxt, Di XOR dengan AddRoundKey:
        $numOfBits = 32; // jumlah bit yang akan di ekstaksi 
        $bitIndex = 0; // indeks bit yang sedang di ekstrak dari gambar
        $binaryMessageLength = 0; // variabel yang akan menyimpan panjang pesan
        for($y = 0; $y < $height; $y++) { //loop untuk mengiterasi setiap baris (tinggi)
            for($x = 0; $x < $width; $x++) { //loop untuk mengiterasi setiap kolom (lebar)
                //PROSES AES -> 2. Proses InvShiftRow ini merupakan kebalikan dari ShiftRow.
                $rgb = imagecolorat($image, $x, $y); // untuk mendapatkan nilai RGB dari piksel
                // We extract each component's spread spectrum by simply ANDing with 1.
                $r = ($rgb >> 16) & 1; // Mengekstrak bit terakhir dari komponen merah (R) piksel
                $g = ($rgb >> 8) & 1; // Mengekstrak bit terakhir dari komponen hijau (G) piksel.
                $b = $rgb & 1; // Mengekstrak bit terakhir dari komponen biru (B) piksel.
                
                // Jika indeks bit masih kurang dari jumlah bit yang akan diekstrak
                $binaryMessageLength = ($bitIndex++ < $numOfBits) ? (($binaryMessageLength << 1) | $r) : $binaryMessageLength;
                $binaryMessageLength = ($bitIndex++ < $numOfBits) ? (($binaryMessageLength << 1) | $g) : $binaryMessageLength;
                $binaryMessageLength = ($bitIndex++ < $numOfBits) ? (($binaryMessageLength << 1) | $b) : $binaryMessageLength;
                
                // Jika indeks bit sudah mencapai atau melebihi jumlah bit yang akan diekstrak 
                if($bitIndex >= $numOfBits) { 
                    return $binaryMessageLength;
                }
            }
        }
    }

    function decodeBinaryMessage($image, $width, $height, $offset, $messageLength) { 
        $offsetRemainder = $offset % 3;
        // We get 3 bits for each pixel, so the offset needs to be divided by 3.
        $offset /= 3;
        // Instead of looping through all the pixels, an offset is used for the starting indices.
        $line = $offset / $width;
        $col = $offset % $width;
        $binaryMessage = '';
        $bitIndex = 0;
        for($y = $line; $y < $height; $y++) {
            for($x = $col; $x < $width; $x++) { 
                $rgb = imagecolorat($image, $x, $y);
                // We extract each component's spread spectrum by simply ANDing with 1.
                //PROSES AES -> 3. Proses InvSubBytes sebenarnya sama dengan proses SubByte hanya saja tabel yang digunakan yaitu tabel InvSubBytes
                $r = ($rgb >> 16) & 1;
                $g = ($rgb >> 8) & 1;
                $b = $rgb & 1;

                // Depending on the remainder, we will start with a different spread spectrum.
                //PROSES AES -> 4. Dari hasil InvSubBytes kemudian di XOR dengan AddRoundKey
                if($offsetRemainder == 1) {
                    $binaryMessage .= $g;
                    $binaryMessage .= $b;
                    $offsetRemainder = 0;
                    $bitIndex += 2;
                } else if($offsetRemainder == 2) {
                    $binaryMessage .= $b;
                    $offsetRemainder = 0;
                    $bitIndex++;
                } else {
                    // As long as the bit index is lower than the length of the message, concatenate each component's spread spectrum to the message.
                    $binaryMessage = ($bitIndex++ < $messageLength) ? ($binaryMessage.$r) : $binaryMessage;
                    $binaryMessage = ($bitIndex++ < $messageLength) ? ($binaryMessage.$g) : $binaryMessage;
                    $binaryMessage = ($bitIndex++ < $messageLength) ? ($binaryMessage.$b) : $binaryMessage;

                    //PROSES AES -> 5. InvMixColumns
                    if($bitIndex >= $messageLength) {
                        return $binaryMessage;
                    }
                }
            }
        }
    }
    //Proses spread spectrum -> 2.  Memasukan key file
    // fungsi ini dipanggil untuk mendapatkan panjang pesan terdekripsi yang telah disisipkan ke dalam gambar.
    $decodedMessageLength = decodeMessageLength($image, $width, $height); 
    // fungsi ini untuk menerima beberapa parameter dari image, width, height untuk informasi
    // gambar yang diperlukan untuk dekode 
    $decodedBinaryMessage = decodeBinaryMessage($image, $width, $height, $INTEGER_BITS, $decodedMessageLength); 
    //Proses spread spectrum -> 3.  Menampilkan hasil pembacaan pesan
    // Proses ini juga digunakan untuk mengkonversi pesan biner terdekripsi menjadi teks biasa
    $decodedMessage = implode(array_map('chr', array_map('bindec', str_split($decodedBinaryMessage, 8))));  
 
  } // tutup else

  $decrypt = $decodedMessage;
  imagedestroy($image); // fungsi untuk menghancurkan objek gambar 
} else {
  $decrypt = $ciphertext; // kondisi jika proses dekripsi pada tahap sebelumnya tidak berhasil
}
  
  $c              = base64_decode($decrypt); // Melakukan decoding Base64 dari teks pesan hasil dekripsi atau ciphertext
  $ivlen          = openssl_cipher_iv_length($cipher="AES-128-CBC"); // inisialisasi vector 
  $iv             = substr($c, 0, $ivlen);
  $hmac           = substr($c, $ivlen, $sha2len=32);
  $ciphertext_raw = substr($c, $ivlen+$sha2len);
  $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $kode_encrypt, $options=OPENSSL_RAW_DATA, $iv);
  $calcmac = hash_hmac('sha256', $ciphertext_raw, $kode_encrypt, $as_binary=true);
  // timing attack safe comparison


  if ($hmac == $calcmac){
    $result   = $mysqli->query("INSERT INTO tbl_decrypt VALUES(null,$id_user,'$jenis_decrypt',
                '$kode_encrypt','$original_plaintext')"); 
    echo "<script>document.location.href='../?mod=decrypt&pg=form_input_decrypt&act=db&key=$kode_encrypt'</script>";
  } else {
    echo "<script>document.location.href='../?mod=decrypt&pg=form_input_decrypt&act=dg'</script>";
  } 
?>