<?php

// Membuat Koneksi
$conn = mysqli_connect('localhost', 'root', '', 'kasir');

// Check if a session is already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Login
if (isset($_POST['login'])) {
    // Initiate Variables
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sanitize user input to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // Query to check if the user exists
    $query = "SELECT * FROM user WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    // Check if the query was successful
    if ($result && mysqli_num_rows($result) > 0) {
        // Jika data ditemukan
        // Berhasil login
        $_SESSION['login'] = true;
        header('Location: index.php');
        exit;
    } else {
        // Data tidak ditemukan
        // Gagal login
        echo '
        <script>
        alert("Username atau Password salah");
        window.location.href = "login.php";
        </script>
        ';
        exit;
    }
}

//Kodingan Fuction untuk menambah barang
if (isset($_POST['tambahbarang'])) {
    $namaproduk = $_POST['namaproduk'];
    $deskripsi = $_POST['deskripsi'];
    $stock = $_POST['stock'];
    $harga = $_POST['harga'];

    $insert = mysqli_query($conn,"insert into produk (namaproduk,deskripsi,harga,stock) values ('$namaproduk','$deskripsi','$harga','$stock')");

    if($insert){
        header('location:stock.php');
    } else {
        echo '
        <script>
        alert("Gagal menambah barang baru");
        window.location.href = "stock.php";
        </script>
        ';
        exit;
    }
}

//Kodingan Fuction untuk menambah pelanggan
if (isset($_POST['tambahpelanggan'])) {
    $namapelanggan = $_POST['namapelanggan'];
    $notelp = $_POST['notelp'];
    $alamat = $_POST['alamat'];
    

    $insert = mysqli_query($conn,"insert into pelanggan (namapelanggan,notelp,alamat) values ('$namapelanggan','$notelp','$alamat')");

    if($insert){
        header('location:pelanggan.php');
    } else {
        echo '
        <script>
        alert("Gagal menambah pelanggan baru");
        window.location.href = "pelanggan.php";
        </script>
        ';
        exit;
    }
}


if (isset($_POST['tambahpesanan'])) {
    $idpelanggan = $_POST['idpelanggan'];
    
    $insert = mysqli_query($conn,"insert into pesanan (idpelanggan) values ('$idpelanggan')");

    if($insert){
        header('location:index.php');
    } else {
        echo '
        <script>
        alert("Gagal menambah pesanan baru");
        window.location.href = "index.php";
        </script>
        ';
        exit;
    }
}


//produk dipilih di pesanan
if (isset($_POST['addproduk'])) {
    $idproduk = $_POST['idproduk'];
    $idp= $_POST['idp']; //idpesanan
    $qty= $_POST['qty']; //jumlah yang mau dikeluarkan
    
    //hitung stock sekarang ada berapa
    $hitung1 = mysqli_query($conn,"SELECT * FROM produk where idproduk='$idproduk'");
    $hitung2 = mysqli_fetch_array($hitung1);
    $stocksekarang = $hitung2['stock']; //stock barang saat ini

    if($stocksekarang>=$qty){

        //kurangi stocknya dengan jumlah yang akan dikeluarkan
        $selisih = $stocksekarang-$qty;

        //stocknya cukup
        $insert = mysqli_query($conn, "insert into detailpesanan (idpesanan,idproduk,qty) VALUES ('$idp','$idproduk','$qty')");
        $update = mysqli_query($conn,"update produk set stock='$selisih' where idproduk='$idproduk'");

        if($insert&&$update){
            header('location:view.php?idp='.$idp);
        } else {
            echo '
            <script>
            alert("Gagal menambah pesanan baru");
            window.location.href ="view.php?idp='.$idp.'"
            </script>
            ';
            exit;
        }
    } else {
        //stock tidak cukup
            echo '
            <script>
            alert("Stock barang tidak cukup");
            window.location.href ="view.php?idp='.$idp.'"
            </script>
            ';
            exit;
        
    }
  
}

//Menambah barang masuk
if(isset($_POST['barangmasuk'])){
    $idproduk = $_POST['idproduk'];
    $qty = $_POST['qty'];

    // Cari tahu stock sekarang berapa
    $caristock = mysqli_query($conn, "SELECT * FROM produk WHERE idproduk='$idproduk'");
    $caristock2 = mysqli_fetch_array($caristock);
    $stocksekarang = $caristock2['stock'];

    // Hitung
    $newstock = $stocksekarang + $qty;

    $insertb = mysqli_query($conn, "INSERT INTO masuk (idproduk, qty) VALUES ('$idproduk', '$qty')");
    $updatetb = mysqli_query($conn, "UPDATE produk SET stock='$newstock' WHERE idproduk='$idproduk'");

    if($insertb && $updatetb){
        header('location: masuk.php');
    } else {
        echo '
            <script>
            alert("Gagal");
            window.location.href ="masuk.php";
            </script>
        ';
        exit;
    }
}


//hapus produk pesanan
if(isset($_POST['hapusprodukpesanan'])){
    $idp = $_POST['idp']; //iddetailpesanan
    $idpr = $_POST['idpr'];
    $idpesanan = $_POST['idpesanan'];

    //cek qty sekarang
    $cek1 = mysqli_query($conn,"select * from detailpesanan where iddetailpesanan='$idp'");
    $cek2 = mysqli_fetch_array($cek1);
    $qtysekarang = $cek2['qty'];

    //cek stock sekarang
    $cek3 = mysqli_query($conn,"select * from produk where idproduk='$idpr'");
    $cek4 = mysqli_fetch_array($cek3);
    $stocksekarang = $cek4['stock'];

    $hitung = $stocksekarang+$qtysekarang;

    $update = mysqli_query($conn,"update produk set stock='$hitung' where idproduk='$idpr'"); //update stock
    $hapus = mysqli_query($conn,"delete from detailpesanan where idproduk='$idpr' and iddetailpesanan='$idp'");

    if($update&&$hapus){
        header('location:view.php?idp='.$idpesanan);
    } else {
        echo '
        <script>
        alert("Gagal menghapus barang");
        window.location.href ="view.php?idp='.$idpesanan.'"
        </script>
        ';
        exit;
    }
}

//edit barang
if(isset($_POST['editbarang'])){
    $np = $_POST['namaproduk']; //namaproduk
    $desc = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $idp = $_POST['idp']; //idproduk

    $query = mysqli_query($conn,"update produk set namaproduk='$np', deskripsi='$desc', harga='$harga' where idproduk='$idp' ");

    if($query){
        header('location:stock.php');
    } else {
        echo '
        <script>
        alert("Gagal");
        window.location.href ="stock.php"
        </script>
        ';
        exit;
    }
}

//hapus barang
if(isset($_POST['hapusbarang'])){
    $idp = $_POST['idp'];

    $query = mysqli_query($conn,"delete from produk where idproduk='$idp'");

    if($query){
        header('location:stock.php');
    } else {
        echo '
        <script>
        alert("Gagal");
        window.location.href ="stock.php"
        </script>
        ';
        exit;
    }

}

//edit pelanggan
if(isset($_POST['editpelanggan'])){
    $npl = $_POST['namapelanggan']; //namapelanggan
    $nt = $_POST['notelp'];
    $a = $_POST['alamat'];
    $idpl = $_POST['idpl']; //idpelanggan

    $query = mysqli_query($conn,"update pelanggan set namapelanggan='$npl', notelp='$nt', alamat='$a' where idpelanggan='$idpl' ");

    if($query){
        header('location:pelanggan.php');
    } else {
        echo '
        <script>
        alert("Gagal");
        window.location.href ="pelanggan.php"
        </script>
        ';
        exit;
    }
}

//hapus pelanggan
if(isset($_POST['hapuspelanggan'])){
    $idpl = $_POST['idpl'];

    $query = mysqli_query($conn,"delete from pelanggan where idpelanggan='$idpl'");

    if($query){
        header('location:pelanggan.php');
    } else {
        echo '
        <script>
        alert("Gagal");
        window.location.href ="pelanggan.php"
        </script>
        ';
        exit;
    }

}


//mengubah data barang masuk
if(isset($_POST['editdatabarangmasuk'])){
    $qty = $_POST['qty']; 
    $idm = $_POST['idm']; // idmasuk
    $idp = $_POST['idp']; // idproduk
    
    // Cari tahu qty nya sekarang berapa
    $caritahu = mysqli_query($conn, "SELECT * FROM masuk WHERE idmasuk='$idm'");
    $caritahu2 = mysqli_fetch_array($caritahu);
    $qtysekarang = $caritahu2['qty'];

    // Cari tahu stock sekarang berapa
    $caristock = mysqli_query($conn, "SELECT * FROM produk WHERE idproduk='$idp'");
    $caristock2 = mysqli_fetch_array($caristock);
    $stocksekarang = $caristock2['stock'];

    if($qty >= $qtysekarang){
        // Jika inputan user lebih besar dari qty yang tercatat
        // Hitung selisih 
        $selisih = $qty - $qtysekarang;
        $newstock = $stocksekarang + $selisih;

        $query1 = mysqli_query($conn, "UPDATE masuk SET qty='$qty' WHERE idmasuk='$idm'");
        $query2 = mysqli_query($conn, "UPDATE produk SET stock='$newstock' WHERE idproduk='$idp'");

        if($query1 && $query2){
            header('location: masuk.php');
        } else {
            echo '
            <script>
            alert("Gagal");
            window.location.href ="masuk.php";
            </script>
            ';
            exit;
        }
    } else {
        // Jika lebih kecil
        // Hitung selisih
        $selisih = $qtysekarang - $qty;
        $newstock = $stocksekarang - $selisih;

        $query1 = mysqli_query($conn, "UPDATE masuk SET qty='$qty' WHERE idmasuk='$idm'");
        $query2 = mysqli_query($conn, "UPDATE produk SET stock='$newstock' WHERE idproduk='$idp'");

        if($query1 && $query2){
            header('location: masuk.php');
        } else {
            echo '
            <script>
            alert("Gagal");
            window.location.href ="masuk.php";
            </script>
            ';
            exit;
        }
    }
}


//hapus data barang masuk
if(isset($_POST['hapusdatabarangmasuk'])){
    $idm = $_POST['idm'];
    $idp = $_POST['idp'];

     // Cari tahu qty nya sekarang berapa
     $caritahu = mysqli_query($conn, "SELECT * FROM masuk WHERE idmasuk='$idm'");
     $caritahu2 = mysqli_fetch_array($caritahu);
     $qtysekarang = $caritahu2['qty'];
 
     // Cari tahu stock sekarang berapa
     $caristock = mysqli_query($conn, "SELECT * FROM produk WHERE idproduk='$idp'");
     $caristock2 = mysqli_fetch_array($caristock);
     $stocksekarang = $caristock2['stock'];

    // Hitung selisih
    $newstock = $stocksekarang - $qtysekarang;

    $query1 = mysqli_query($conn, "DELETE FROM masuk WHERE idmasuk='$idm'");
    $query2 = mysqli_query($conn, "UPDATE produk SET stock='$newstock' WHERE idproduk='$idp'");

    if($query1 && $query2){
        header('location: masuk.php');
    } else {
        echo '
        <script>
        alert("Gagal");
        window.location.href ="masuk.php";
        </script>
        ';
        exit;
        }
    }

//hapus pesanan
if(isset($_POST['hapuspesanan'])){
    $idpesanan = $_POST['idpesanan'];

    $cekdata = mysqli_query($conn,"select * from detailpesanan dp where idpesanan='$idpesanan'");

    while($ok=mysqli_fetch_array($cekdata)){
        //balikin stock
        $qty = $ok['qty'];
        $idproduk = $ok['idproduk'];
        $iddp = $ok['iddetailpesanan'];

        // Cari tahu stock sekarang berapa
        $caristock = mysqli_query($conn, "SELECT * FROM produk WHERE idproduk='$idproduk'");
        $caristock2 = mysqli_fetch_array($caristock);
        $stocksekarang = $caristock2['stock'];

        $newstock = $stocksekarang+$qty;

        $queryupdate = mysqli_query($conn,"update produk set stock='$newstock' where idproduk='$idproduk'");

        //hapus data
        $querydelete = mysqli_query($conn,"delete from detailpesanan where iddetailpesanan='$iddp'");


        
    }

    $query = mysqli_query($conn,"delete from pesanan where idpesanan='$idpesanan'");

    if($queryupdate && $querydelete &&$query){
        header('location:index.php');
    } else {
        echo '
        <script>
        alert("Gagal");
        window.location.href ="index.php"
        </script>
        ';
        exit;
    }

}



//mengubah data detail pesanan
if(isset($_POST['editdetailpesanan'])){
    $qty = $_POST['qty']; 
    $iddp = $_POST['iddp']; // iddetailpesanan
    $idpr = $_POST['idpr']; // idproduk
    $idp = $_POST['idp']; // idpesanan
    
    // Cari tahu qty nya sekarang berapa
    $caritahu = mysqli_query($conn, "SELECT * FROM detailpesanan WHERE iddetailpesanan='$iddp'");
    $caritahu2 = mysqli_fetch_array($caritahu);
    $qtysekarang = $caritahu2['qty'];

    // Cari tahu stock sekarang berapa
    $caristock = mysqli_query($conn, "SELECT * FROM produk WHERE idproduk='$idpr'");
    $caristock2 = mysqli_fetch_array($caristock);
    $stocksekarang = $caristock2['stock'];

    if($qty >= $qtysekarang){
        // Jika inputan user lebih besar dari qty yang tercatat
        // Hitung selisih 
        $selisih = $qty - $qtysekarang;
        $newstock = $stocksekarang - $selisih;

        $query1 = mysqli_query($conn, "UPDATE detailpesanan SET qty='$qty' WHERE iddetailpesanan='$iddp'");
        $query2 = mysqli_query($conn, "UPDATE produk SET stock='$newstock' WHERE idproduk='$idpr'");

        if($query1 && $query2){
            header('location: view.php?idp='.$idp);
        } else {
            echo '
                <script>
                alert("Gagal");
                window.location.href ="view.php?idp='.$idp.'";
                </script>
            ';
            exit;
        }
    } else {
        // Jika lebih kecil
        // Hitung selisih
        $selisih = $qtysekarang - $qty;
        $newstock = $stocksekarang + $selisih;

        $query1 = mysqli_query($conn, "UPDATE detailpesanan SET qty='$qty' WHERE iddetailpesanan='$iddp'");
        $query2 = mysqli_query($conn, "UPDATE produk SET stock='$newstock' WHERE idproduk='$idpr'");

        if($query1 && $query2){
            header('location: view.php?idp='.$idp);
        } else {
            echo '
                <script>
                alert("Gagal");
                window.location.href="view.php?idp='.$idp.'";
                </script>
            ';
            exit;
        }
    }
}



?>