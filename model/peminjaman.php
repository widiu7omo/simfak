<?php
if ( session_status() == PHP_SESSION_NONE ) {
	session_start();
}
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
require_once 'database.php';
require_once 'notifikasi.php';

function add( $post ) {

	$db       = new Database();
	$barangs  = $post['kind']['barang'];
	$ruangans = $post['kind']['ruangan'];

	//insert barang
	if ( isset( $barangs['data'] ) ) {
		foreach ( $barangs['data'] as $key => $barang ) {
			$barang_id         = $barang['id'];
			$jumlah            = $barangs['data'][ $key ]['jumlah'];
			$akun_id           = $barangs['akun_id'];
			$perihal           = $barangs['perihal'];
			$tanggal_transaksi = $barangs['dueDate']['start'];
			$tanggal_kembali   = $barangs['dueDate']['end'];
			$q                 = "INSERT INTO peminjaman_barang(barang_id, jumlah, akun_id,perihal,tanggal_transaksi,tanggal_kembali) VALUES ('$barang_id','$jumlah','$akun_id','$perihal',STR_TO_DATE('$tanggal_transaksi','%d-%m-%Y'),STR_TO_DATE('$tanggal_kembali','%d-%m-%Y'))";
			$db->query( $q );
		}
		//delete cart_barang
		$q = "DELETE FROM cart_barang WHERE akun_id = '$barangs[akun_id]'";
		$db->query( $q );
	}

	//insert ruangan
	if ( isset( $ruangans['data'] ) ) {
		foreach ( $ruangans['data'] as $key => $ruangan ) {
			$ruangan_id        = $ruangan['id'];
			$akun_id           = $ruangans['akun_id'];
			$perihal           = $ruangans['perihal'];
			$tanggal_transaksi = $ruangans['dueDate']['start'];
			$tanggal_kembali   = $ruangans['dueDate']['end'];
			$q                 = "INSERT INTO peminjaman_ruangan(ruangan_id, akun_id,perihal,tanggal_transaksi,tanggal_kembali) VALUES ('$ruangan_id','$akun_id','$perihal',STR_TO_DATE('$tanggal_transaksi','%d-%m-%Y'),STR_TO_DATE('$tanggal_kembali','%d-%m-%Y'))";
			$db->query( $q );
		}
		//delete cart_ruangan
		$q = "DELETE FROM cart_ruangan WHERE akun_id = '$ruangans[akun_id]'";
		$db->query( $q );
	}

	//set success
	$q = "INSERT INTO notifikasi(pengirim, penerima, pesan,link,kategori) VALUES ('$_SESSION[nim]','kabag umum','Mahasiswa mengajukan peminjaman fasilitas kampus.','./peminjaman-list.php','permohonan')";
	$db->query( $q );
	$_SESSION['status'] = (object) [ 'status' => 'success', 'message' => 'Pengajuan sedang diproses' ];
	echo json_encode( array( 'status' => 'success' ) );
}

function get_all_peminjaman( $for = null ) {
	$db = new Database();
	$q  = "SELECT DISTINCT tanggal_transaksi,tanggal_kembali,status,perihal,m.nama_mahasiswa,pr.akun_id FROM peminjaman_ruangan pr LEFT OUTER JOIN mahasiswa m ON pr.akun_id = m.nim";
	if ( $for != null ) {
		if ( $for == 'kabag umum' ) {
			//kabag menerima permintaan dari mahasiswa
			//kabag umum only see peminjaman with status == 0
			$q .= " WHERE status = 0";
		}
		if ( $for == 'bmn' ) {
			//bmn menerima permintaan dari mahasiswa
			//bmn umum can't see peminjaman with status == 0
			$q .= " WHERE status <> 0";
		}
	}
	$db->query( $q );
	$listPBarang = $db->fetch();
	$q           = "SELECT DISTINCT tanggal_transaksi,tanggal_kembali,status,perihal,m.nama_mahasiswa,pb.akun_id FROM peminjaman_barang pb LEFT OUTER JOIN mahasiswa m ON pb.akun_id = m.nim";
	if ( $for != null ) {
		if ( $for == 'kabag umum' ) {
			//kabag menerima permintaan dari mahasiswa
			//kabag umum only see peminjaman with status == 0
			$q .= " WHERE status = 0";
		}
		if ( $for == 'bmn' ) {
			//bmn menerima permintaan dari mahasiswa
			//bmn umum can't see peminjaman with status == 0
			$q .= " WHERE status <> 0";
		}
	}
	$db->query( $q );
	$listPRuangan = $db->fetch();

	$mergedArr = array_merge( $listPRuangan, $listPBarang );

	$mergedArr = array_map( 'json_encode', $mergedArr );
	$mergedArr = array_unique( $mergedArr );

	return $mergedPengajuan = array_map( 'json_decode', $mergedArr );
}

function get_all_history( $for = null ) {
	$db = new Database();
	$q  = "SELECT DISTINCT tanggal_transaksi,tanggal_kembali,status,perihal,m.nama_mahasiswa,pr.akun_id FROM riwayat_barang pr LEFT OUTER JOIN mahasiswa m ON pr.akun_id = m.nim";
	if ( $for != null ) {
		if ( $for == 'kabag umum' ) {
			//kabag menerima permintaan dari mahasiswa
			//kabag umum only see peminjaman with status == 0
			$q .= " WHERE status = 0";
		}
		if ( $for == 'bmn' ) {
			//bmn menerima permintaan dari mahasiswa
			//bmn umum can't see peminjaman with status == 0
			$q .= " WHERE status <> 0";
		}
	}
	$db->query( $q );
	$listPBarang = $db->fetch();
	$q           = "SELECT DISTINCT tanggal_transaksi,tanggal_kembali,status,perihal,m.nama_mahasiswa,pb.akun_id FROM riwayat_ruangan pb LEFT OUTER JOIN mahasiswa m ON pb.akun_id = m.nim";
	if ( $for != null ) {
		if ( $for == 'kabag umum' ) {
			//kabag menerima permintaan dari mahasiswa
			//kabag umum only see peminjaman with status == 0
			$q .= " WHERE status = 0";
		}
		if ( $for == 'bmn' ) {
			//bmn menerima permintaan dari mahasiswa
			//bmn umum can't see peminjaman with status == 0
			$q .= " WHERE status <> 0";
		}
	}
	$db->query( $q );
	$listPRuangan = $db->fetch();

	$mergedArr = array_merge( $listPRuangan, $listPBarang );

	$mergedArr = array_map( 'json_encode', $mergedArr );
	$mergedArr = array_unique( $mergedArr );

	return $mergedPengajuan = array_map( 'json_decode', $mergedArr );
}

function show_peminjaman( $perihal, $type, $akun_id ) {
	$db = new Database();
	$q  = "";
	if ( $type == 'barang' ) {
		$q = "SELECT b.nama_barang,pb.tanggal_transaksi,pb.jumlah,pb.perihal 
                    FROM peminjaman_barang pb
                    LEFT OUTER JOIN barang b ON pb.barang_id = b.id 
                    WHERE pb.akun_id = '$akun_id' AND pb.perihal = '$perihal'";
	}
	if ( $type == 'ruangan' ) {
		$q = "SELECT r.nama_ruangan,pr.tanggal_transaksi,pr.perihal,p.nama_prodi
                    FROM peminjaman_ruangan pr 
                    LEFT OUTER JOIN ruangan r ON pr.ruangan_id = r.id
                    LEFT OUTER JOIN prodi p ON r.prodi_id = p.id
                    WHERE pr.akun_id = '$akun_id' AND pr.perihal = '$perihal'";
	}
	$db->query( $q );

	return $db->fetch();
}

function show_history( $perihal, $type, $akun_id ) {
	$db = new Database();
	$q  = "";
	if ( $type == 'barang' ) {
		$q = "SELECT b.nama_barang,pb.tanggal_transaksi,pb.jumlah,pb.perihal 
                    FROM riwayat_barang pb
                    LEFT OUTER JOIN barang b ON pb.barang_id = b.id 
                    WHERE pb.akun_id = '$akun_id' AND pb.perihal = '$perihal'";
	}
	if ( $type == 'ruangan' ) {
		$q = "SELECT r.nama_ruangan,pr.tanggal_transaksi,pr.perihal,p.nama_prodi
                    FROM riwayat_ruangan pr 
                    LEFT OUTER JOIN ruangan r ON pr.ruangan_id = r.id
                    LEFT OUTER JOIN prodi p ON r.prodi_id = p.id
                    WHERE pr.akun_id = '$akun_id' AND pr.perihal = '$perihal'";
	}
	$db->query( $q );

	return $db->fetch();
}

function change_status( $post ) {
	$akun_id = $post['nim'];
	$perihal = $post['perihal'];
	$status  = $post['status'];

	$db = new Database();
	$q  = "UPDATE peminjaman_barang SET status = $status WHERE akun_id = '$akun_id' AND perihal = '$perihal'";
	if ( $db->query( $q ) ) {
		$q = "UPDATE peminjaman_ruangan SET status = $status WHERE akun_id = '$akun_id' AND perihal = '$perihal'";
		if ( $db->query( $q ) ) {
			$message = '';
			if ( $post['status'] == 1 ) {
				$message = "Permohonan disetujui sepenuhnya";
			}
			if ( $post['status'] == 100 ) {
				$message = "Persetujuan perminjaman berhasil";
			}
			if ( $post['status'] == 101 ) {
				$message = "Penolakan perminjaman berhasil";
			}
			$_SESSION['status'] = (object) [
				'status'  => 'success',
				'message' => $message
			];
		}
	} else {
		$_SESSION['status'] = (object) [ 'status' => 'fail', 'message' => 'Operasi gagal' ];
	}
	header( 'Location: ../peminjaman-list.php' );

}

function restore_stok( $post, $mode = null ) {
	$akun_id = $post['nim'];
	$perihal = $post['perihal'];
	$status  = $post['status'];
	$db      = new Database();
	$q       = "DELETE FROM peminjaman_barang WHERE akun_id = '$akun_id' AND status = '$status' AND perihal = '$perihal'";
	$db->query( $q );
	$q = "DELETE FROM peminjaman_ruangan WHERE akun_id = '$akun_id' AND status = '$status' AND perihal = '$perihal'";
	$db->query( $q );
	//user yang eksekusi
	if ( $mode != null && $mode == 'redirect' ) {
		$pesan = "$akun_id mengembalikan barang, periksa terlebih dahulu";
		send_notif( $akun_id, 'bmn', $pesan, './peminjaman-list.php', 'Pengembalian Barang');
		$_SESSION['status'] = (object) [
			'status'  => 'success',
			'message' => 'Pengembalian Berhasil, Akan dicek oleh pihak bmn'
		];
		header( 'Location: ../peminjaman-user-status.php' );
	}
	//admin yang eksekusi
	elseif ( $mode != null && $mode = 'lengkap' ) {
		$q = "UPDATE riwayat_barang SET status = 111 WHERE akun_id = '$akun_id' AND perihal = '$perihal'";
		$db->query( $q );
		$q = "UPDATE riwayat_ruangan SET status = 111 WHERE akun_id = '$akun_id' AND perihal = '$perihal'";
		$db->query( $q );
		$_SESSION['status'] = (object) [
			'status'  => 'success',
			'message' => 'Pengembalian Berhasil, Barang sudah lengkap'
		];
		header( 'Location: ../peminjaman-list.php' );
	}
}

function checking_status( $post ) {
	$akun_id       = $post['nim'];
	$perihal       = $post['perihal'];
	$status        = $post['status'];
	$db            = new Database();
	$q             = "SELECT status FROM riwayat_barang WHERE akun_id = '$akun_id' AND status = '$status' AND perihal = '$perihal'";
	$statusBarang  = $db->query( $q );
	$db            = new Database();
	$q             = "SELECT status FROM riwayat_barang WHERE akun_id = '$akun_id' AND status = '$status' AND perihal = '$perihal'";
	$statusRuangan = $db->query( $q );
}

if ( isset( $_GET['f'] ) ) {
	$get = $_GET;
	switch ( $get['f'] ) {
		case 'delete':
			delete( $get['id'] );
			break;
		default:
			return;
	}
}
if ( isset( $_POST['button'] ) ) {
	$post = $_POST;
	switch ( $post['button'] ) {
		case 'edit':
			edit( $post );
			break;
		case 'add':
			add( $post );
			break;
		case 'send':
			change_status( $post );
			send_notif( 'bmn', $post['nim'], "Permintaan persetujuan peminjaman fasilitas perihal $post[perihal] <b class=\"text-success\">disetujui</b> oleh BMN", './peminjaman-user-status.php', 'Perizinan' );
			break;
		case 'accept':
			$post['status'] = 100;
			change_status( $post );
			$pesan = 'Peminjaman fasilitas perihal ' . $post['perihal'] . ', <b class="text-success">diterima</b> oleh kabag umum, akan diteruskan ke BMN';
			send_notif( 'kabag umum', $post['nim'], $pesan, './peminjaman-user-status.php', 'Perizinan' );
			send_notif( 'kabag umum', 'bmn', $pesan, './peminjaman-list.php', 'Perizinan' );
			$new_pesan = "Mahasiswa mengajukan peminjaman fasilitas kampus.";
			update_notif( $post['nim'], 'kabag umum', $new_pesan );
			break;
		case 'deny':
			$post['status'] = 101;
			change_status( $post );
			$pesan = 'Peminjaman fasilitas perihal ' . $post['perihal'] . ', <b class="text-danger">ditolak</b> oleh kabag umum, akan diteruskan ke BMN';
			send_notif( 'kabag umum', $post['nim'], $pesan, './peminjaman-user-status.php', 'Perizinan' );
			$new_pesan = "Mahasiswa mengajukan peminjaman fasilitas kampus.";
			update_notif( $post['nim'], 'kabag umum', $new_pesan );
			restore_stok( $post );
			break;
		case 'kembalikan':
			restore_stok( $post, 'redirect' );
			break;
		case 'lengkap':
			restore_stok( $post, 'lengkap' );
			break;
		default:
			echo 'wrong direction';
			break;
	}
}
