<?php
session_start();
require "../../config/connection.php";
require_once "../../config/base_url.php";

// cek role mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: ../../login.php");
    exit;
}
$username = $_SESSION['user']['nama'] ?? 'Mahasiswa';

// ambil semua pengajuan mahasiswa + dosen pembimbing
$stmt = $pdo->prepare("
    SELECT 
        p.id,
        p.id_pengajuan,
        p.judul_ta,
        p.status,
        p.catatan_admin,
        p.created_at,

        MAX(CASE WHEN db.role = 'dosbing_1' THEN d.nama END) AS dosen1_nama,
        MAX(CASE WHEN db.role = 'dosbing_2' THEN d.nama END) AS dosen2_nama,

        p.status_bukti_pembayaran,
        p.catatan_bukti_pembayaran,
        p.status_formulir_pendaftaran,
        p.catatan_formulir_pendaftaran,
        p.status_transkrip_nilai,
        p.catatan_transkrip_nilai,
        p.status_bukti_magang,
        p.catatan_bukti_magang

    FROM pengajuan_ta p
    LEFT JOIN dosbing_ta db ON db.pengajuan_id = p.id
    LEFT JOIN dosen d ON db.dosen_id = d.id
    WHERE p.mahasiswa_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$pengajuan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// mapping kolom => label file
$files = [
    'bukti_pembayaran' => ['status'=>'status_bukti_pembayaran', 'catatan'=>'catatan_bukti_pembayaran', 'label'=>'Bukti Pembayaran'],
    'formulir' => ['status'=>'status_formulir_pendaftaran', 'catatan'=>'catatan_formulir_pendaftaran', 'label'=>'Formulir Pendaftaran'],
    'transkrip' => ['status'=>'status_transkrip_nilai', 'catatan'=>'catatan_transkrip_nilai', 'label'=>'Transkrip Nilai'],
    'magang' => ['status'=>'status_bukti_magang', 'catatan'=>'catatan_bukti_magang', 'label'=>'Bukti Kelulusan Magang'],
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="<?= base_url('assets/img/Logo.webp') ?>">
<title>Status Pengajuan TA</title>
<style>
/* TOP */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px
}
.topbar h1{
    color:#ff8c42;
    font-size:28px
}

/* PROFILE */
.mhs-info{
    display:flex;
    align-items:left;
    gap:20px
}
.mhs-text span{
    font-size:13px;
    color:#555
}
.mhs-text b{
    color:#ff8c42;
    font-size:14px
}

.avatar{
    width:42px;
    height:42px;
    background:#ff8c42;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}

/* CARD */
.card {
    background:#fff;
    border-radius:18px;
    padding:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.2);
    overflow-x: hidden;
}

.card h2{
    text-align: center;
    color: #ff8c42;
}

.divider {
    border: none;
    height: 0.5px;
    width: 100% !important;
    background: #FF983D;
    display: block;
    margin: 12px 0;
}

/* JUDUL */
.ta-judul-wrap{
    margin-top:18px;
}
.ta-body{
    margin-top:6px;
    background:#f3f4f6;
    border-radius:12px;
    padding:8px 10px;
    font-size:14px;
    font-weight:700;
    color:#555;
}

/* TANGGAL */
.ta-textbox{
    background:#f3f4f6;
    padding:6px 10px;
    border-radius:8px;
    font-size:12px;
    font-weight:700;
    color:#555;
    width:fit-content;
}

.ta-meta-label{
    margin-top: 10px;
    font-size: 14px;
    font-weight: 700;
    color: #ff8c42;
}

/* tombol */
.actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top: 14px;
}
/* badge status */
.badge-status {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    border: 1px solid;
    display: inline-block;
    width: fit-content;
}

/* DIAJUKAN / PROSES */
.badge-proses {
    color:#2563EB;
    background: rgba(37,99,235,.12);
    border-color: rgba(37,99,235,.4);
}

/* PERLU REVISI */
.badge-revisi {
    color:#FF983D;
    background: rgba(255,152,61,.15);
    border-color: rgba(255,152,61,.5);
}

/* DISETUJUI */
.badge-disetujui {
    color:#16A34A;
    background: rgba(22,163,74,.15);
    border-color: rgba(22,163,74,.5);
}

/* DITOLAK */
.badge-ditolak {
    color:#DC2626;
    background: rgba(220,38,38,.15);
    border-color: rgba(220,38,38,.5);
}


/* id capsule */
.badge-id {
    background:#f3f4f6;
    color:#555;
    font-size:12px;
    padding:6px 14px;
    border-radius:10px;
    font-weight:700;
    width: fit-content;
}

/* GRID META */
.ta-grid-meta{
    display:grid;
    grid-template-columns: repeat(3, minmax(160px, auto));
    gap:20px;
    padding-bottom:12px;
    border-bottom:1px dashed rgba(255,152,61,.3);
}

/* ITEM */
.ta-item{
    display:flex;
    flex-direction:column;
    gap:6px;
}

/* DOSEN BOX */
.dosen-box {
    background:#fff7f1;
    border:1px solid rgba(255,152,61,.35);
    border-radius:16px;
    padding:16px;
    margin-top:16px;
}

.dosen-title {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    font-size:16px;
    font-weight:800;
    color:#ff8c42;
    margin-bottom:10px;
}

.dosen-list {
    list-style:none;
    padding:0;
    margin:0;
}

.dosen-list li {
    display:flex;
    gap:10px;
    align-items:center;
    margin-bottom:8px;
    font-size:13px;
}

.dosen-num {
    width:26px;
    height:26px;
    border-radius:8px;
    border:1px solid #FF983D;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    color:#FF983D;
}
.dosen-info{
    display: flex;
    flex-direction: column;
    justify-content: start;
    font-size:14px;
}
.dosen-info p{
    margin: 0;
    font-size: 10px;
    font-weight: 600;
    color: #FF3A3D;
}
/* CATATAN ADMIN */
.catatan-box {
    background:#FFEDEE;
    border:1px solid rgba(255,58,61,.25);
    border-radius:16px;
    padding:14px 16px;
    margin-top:18px;
}

.catatan-title {
    font-size:12px;
    font-weight:800;
    color:#FF3A3D;
    margin-bottom:6px;
}

/* tombol tengah */
.center-action {
    display:flex;
    justify-content:center;
    margin-top:20px;
}

.btn-detail {
    background:#FF983D;
    color:#fff;
    padding:12px 28px;
    border-radius:999px;
    font-weight:800;
    text-decoration:none;
    font-size:14px;
    box-shadow:0 4px 10px rgba(255,152,61,.35);
}

.btn-detail:hover {
    opacity:.9;
}

/* info bawah */
.info-warning {
    background:#FFE4E5;
    border:1px solid rgba(255,58,61,.35);
    border-radius:18px;
    padding:16px 18px;
    color:#FF3A3D;
    font-size:13px;
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:20px;
}
.material-symbols-rounded {
    font-size: 20px;
    vertical-align: middle;
}

/* tanggal */
.status-date {
    display:flex;
    align-items:center;
    gap:6px;
    font-size:12px;
    color:#9ca3af;
}

/* judul dosen */
.dosen-title {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
}

/* catatan admin */
.catatan-title {
    display:flex;
    align-items:center;
    gap:6px;
}

/* tombol */
.btn-detail {
    display:inline-flex;
    align-items:center;
    gap:6px;
}

/* =========================
   RESPONSIVE META INFO
   ========================= */

/* TABLET */
@media (max-width: 768px) {
    .ta-grid-meta {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
}

/* MOBILE */
@media (max-width: 480px) {
    .ta-grid-meta {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .ta-item {
        padding: 10px 12px;
        background: #fff7f1;
        border-radius: 12px;
        border: 1px solid rgba(255,152,61,.25);
    }

    .ta-meta-label {
        font-size: 12px;
    }
}

</style>
</head>
<body>

<div class="container">

    <?php include "../sidebar.php"; ?>

    <div class="main-content">
        <div class="topbar">
            <h1>Riwayat Pengajuan Tugas Akhir</h1>

            <div class="mhs-info">
                <div class="mhs-text">
                    <span>Selamat Datang,</span><br>
                    <b><?= htmlspecialchars($username) ?></b>
                </div>
                <div class="avatar">
                    <span class="material-symbols-rounded" style="color:#fff">person</span>
                </div>
            </div>
        </div>

        <?php if ($pengajuan_list): ?>
            <?php foreach($pengajuan_list as $data): ?>
                <?php
                $status = strtolower(trim($data['status'] ?? 'proses'));

                $badgeClass = match ($status) {
                    'revisi', 'perlu revisi' => 'badge-revisi',
                    'disetujui'              => 'badge-disetujui',
                    'ditolak'                => 'badge-ditolak',
                    'diajukan', 'proses'     => 'badge-proses',
                    default                  => 'badge-proses',
                };
                ?>


            <div class="card">

                <!-- HEADER -->
                <h2>Status Pengajuan</h2>
                <hr class="divider">

                <!-- META INFO -->
                <div class="ta-grid-meta">
                    <div class="ta-item">
                        <span class="ta-meta-label">ID TA</span>
                        <span class="badge-id">
                            <?= htmlspecialchars($data['id_pengajuan'] ?? '-') ?>
                        </span>
                    </div>

                    <div class="ta-item">
                        <span class="ta-meta-label">Status Pengajuan</span>
                        <span class="badge-status <?= $badgeClass ?>">
                            <?= strtoupper($data['status'] ?? 'PROSES') ?>
                        </span>
                    </div>

                    <div class="ta-item">
                        <span class="ta-meta-label">Tanggal Pengajuan</span>
                        <span class="badge-id">
                            <?= date('d F Y, H:i', strtotime($data['created_at'])) ?>
                        </span>
                    </div>
                </div>

                <!-- JUDUL -->
                <div class="ta-judul-wrap">
                    <span class="ta-meta-label">Judul Tugas Akhir</span>
                    <div class="ta-body">
                        <?= htmlspecialchars($data['judul_ta']) ?>
                    </div>
                </div>


                <!-- DOSEN -->
                <div class="dosen-box">
                    <div class="dosen-title">
                        <span class="material-symbols-rounded">school</span>
                        DOSEN PEMBIMBING
                    </div>
                    <hr class="divider">

                    <ul class="dosen-list">
                        <li>
                            <div class="dosen-num">1</div>
                            <div class="dosen-info">
                                <p> Dosen Pembimbing 1 </p>
                                <?= htmlspecialchars($data['dosen1_nama'] ?? '-') ?>
                            </div>
                        </li>
                        <li>
                            <div class="dosen-num">2</div>
                            <div class="dosen-info">
                                <p> Dosen Pembimbing 2 </p>
                                <?= htmlspecialchars($data['dosen2_nama'] ?? '-') ?>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- CATATAN -->
                <div class="catatan-box">
                    <div class="catatan-title">
                        <span class="material-symbols-rounded">info</span>
                        CATATAN ADMIN
                    </div>
                    <?= !empty($data['catatan_admin'])
                        ? htmlspecialchars($data['catatan_admin'])
                        : '-' ?>
                </div>

                <!-- ACTION -->
                <div class="center-action">
                    <a href="detail.php?id=<?= $data['id'] ?>" class="btn-detail">
                        <span class="material-symbols-rounded">folder_open</span>
                        Lihat Detail Berkas
                    </a>
                </div>

            </div>

            <!-- INFO GLOBAL -->
            <div class="info-warning">
                <span class="material-symbols-rounded">info</span>
                Status pengajuan Anda diperbarui secara berkala oleh tim Admin Prodi.
            </div>


            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <p>
                    Belum ada pengajuan TA.
                    <a href="form.php" class="button">Ajukan TA Baru</a>
                </p>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>