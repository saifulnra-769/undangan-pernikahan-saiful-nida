
<?php
/* =========================================================
   UNDANGAN PERNIKAHAN ROYAL JAWA ULTIMATE
   Mempelai: Muchamad Saiful & Nida Rohadatul Aisy
   Tanggal : 23 Desember 2026
   ========================================================= */

date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "wedding_db";

try {
    // Membuat koneksi awal ke MySQL
    $pdoServer = new PDO(
        "mysql:host=$host;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Membuat database otomatis jika belum ada
    $pdoServer->exec(
        "CREATE DATABASE IF NOT EXISTS `$db`
         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    // Koneksi ke database
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Membuat tabel RSVP
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rsvp (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(150) NOT NULL,
            jumlah INT NOT NULL DEFAULT 1,
            kehadiran VARCHAR(30) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Membuat tabel buku tamu
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS guestbook (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(150) NOT NULL,
            ucapan TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $dbReady = true;

} catch (PDOException $e) {
    $dbReady = false;
    $dbError = $e->getMessage();
}

/* =========================================================
   PROSES AJAX RSVP DAN BUKU TAMU
   ========================================================= */

if (isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    if (!$dbReady) {
        echo json_encode([
            "success" => false,
            "message" => "Database belum dapat diakses."
        ]);
        exit;
    }

    $action = $_POST['action'];

    if ($action === 'rsvp') {
        $nama = trim($_POST['nama'] ?? '');
        $jumlah = (int)($_POST['jumlah'] ?? 1);
        $kehadiran = trim($_POST['kehadiran'] ?? '');

        if ($nama === '' || $jumlah < 1 || $jumlah > 10 ||
            !in_array($kehadiran, ['Hadir', 'Tidak Hadir'], true)) {
            echo json_encode([
                "success" => false,
                "message" => "Mohon isi formulir RSVP dengan benar."
            ]);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO rsvp (nama, jumlah, kehadiran)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$nama, $jumlah, $kehadiran]);

        echo json_encode([
            "success" => true,
            "message" => "Terima kasih! Konfirmasi kehadiran berhasil dikirim."
        ]);
        exit;
    }

    if ($action === 'guestbook') {
        $nama = trim($_POST['nama'] ?? '');
        $ucapan = trim($_POST['ucapan'] ?? '');

        if ($nama === '' || $ucapan === '') {
            echo json_encode([
                "success" => false,
                "message" => "Nama dan ucapan harus diisi."
            ]);
            exit;
        }

        if (mb_strlen($nama) > 150 || mb_strlen($ucapan) > 2000) {
            echo json_encode([
                "success" => false,
                "message" => "Ucapan terlalu panjang."
            ]);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO guestbook (nama, ucapan)
            VALUES (?, ?)
        ");

        $stmt->execute([$nama, $ucapan]);

        echo json_encode([
            "success" => true,
            "message" => "Ucapan dan doa berhasil dikirim."
        ]);
        exit;
    }

    echo json_encode([
        "success" => false,
        "message" => "Permintaan tidak dikenali."
    ]);
    exit;
}

/* =========================================================
   MENGAMBIL DATA BUKU TAMU
   ========================================================= */

$comments = [];

if ($dbReady) {
    $stmt = $pdo->query("
        SELECT nama, ucapan, created_at
        FROM guestbook
        ORDER BY id DESC
        LIMIT 20
    ");

    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#100b0a">

<title>Saiful & Nida | Wedding Invitation</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Great+Vibes&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
/* =========================================================
   RESET DAN WARNA UTAMA
   ========================================================= */

:root {
    --gold: #d7b56d;
    --gold-light: #f6e3a8;
    --gold-dark: #92703b;
    --black: #100b0a;
    --black-soft: #19100f;
    --maroon: #4c101b;
    --cream: #f5ead3;
    --text: #e9dcc3;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    background: var(--black);
    color: var(--text);
    font-family: 'Poppins', sans-serif;
    overflow-x: hidden;
}

body.locked {
    overflow: hidden;
}

a {
    color: inherit;
    text-decoration: none;
}

button,
input,
textarea,
select {
    font: inherit;
}

button {
    cursor: pointer;
}

img {
    max-width: 100%;
    display: block;
}

section {
    position: relative;
    padding: 100px 7%;
    overflow: hidden;
}

.container {
    max-width: 1150px;
    margin: auto;
}

.section-title {
    text-align: center;
    margin-bottom: 55px;
}

.section-title small {
    color: var(--gold);
    letter-spacing: 5px;
    text-transform: uppercase;
    font-size: 10px;
}

.section-title h2 {
    font-family: 'Great Vibes', cursive;
    color: var(--gold-light);
    font-size: clamp(42px, 7vw, 65px);
    font-weight: 400;
    margin-top: 10px;
}

.section-title p {
    color: #b9aa91;
    font-size: 12px;
    margin-top: 12px;
    line-height: 1.8;
}

.gold-line {
    width: 75px;
    height: 1px;
    background: var(--gold);
    margin: 18px auto;
}

/* =========================================================
   SVG BATIK KAWUNG
   ========================================================= */

.svg-library {
    position: absolute;
    width: 0;
    height: 0;
    overflow: hidden;
}

.batik-corner {
    position: absolute;
    width: 250px;
    height: 250px;
    z-index: 4;
    pointer-events: none;
    opacity: .9;
    filter: drop-shadow(0 0 8px rgba(215,181,109,.3));
    animation: batikGlow 5s ease-in-out infinite;
}

.batik-corner svg {
    width: 100%;
    height: 100%;
}

.batik-corner.top-left {
    top: 0;
    left: 0;
}

.batik-corner.top-right {
    top: 0;
    right: 0;
    transform: rotate(90deg);
}

.batik-corner.bottom-left {
    bottom: 0;
    left: 0;
    transform: rotate(-90deg);
}

.batik-corner.bottom-right {
    bottom: 0;
    right: 0;
    transform: rotate(180deg);
}

@keyframes batikGlow {
    0%, 100% {
        filter: drop-shadow(0 0 5px rgba(215,181,109,.2));
    }
    50% {
        filter: drop-shadow(0 0 15px rgba(246,227,168,.6));
    }
}

/* =========================================================
   PEMBUKAAN BUKU
   ========================================================= */

#opening {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    background:
        radial-gradient(circle, #3a1a15 0%, #160d0b 55%, #080605 100%);
    transition: opacity 1s ease, visibility 1s ease;
}

#opening.hide {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.book {
    width: min(330px, 82vw);
    height: 440px;
    position: relative;
    perspective: 1500px;
}

.book-cover {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #5d1a23, #170c0b 70%);
    border: 2px solid var(--gold);
    border-radius: 5px 15px 15px 5px;
    box-shadow: 0 0 40px rgba(215,181,109,.2);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    transform-origin: left center;
    transition: transform 1.8s cubic-bezier(.7,0,.2,1);
    z-index: 3;
    overflow: hidden;
}

.book.open .book-cover {
    transform: rotateY(-115deg);
}

.book-cover::before {
    content: "";
    position: absolute;
    inset: 12px;
    border: 1px solid rgba(215,181,109,.65);
}

.book-cover-content {
    padding: 25px;
    position: relative;
    z-index: 2;
}

.book-cover-content small {
    color: var(--gold-light);
    letter-spacing: 4px;
    font-size: 10px;
}

.book-cover-content h1 {
    font-family: 'Great Vibes', cursive;
    font-size: 52px;
    line-height: 1.4;
    color: var(--gold-light);
    margin: 25px 0;
}

.book-cover-content p {
    font-size: 10px;
    letter-spacing: 3px;
    color: #e8d5b2;
}

.book-cover-content button {
    margin-top: 35px;
    background: transparent;
    border: 1px solid var(--gold);
    color: var(--gold-light);
    padding: 12px 25px;
    border-radius: 30px;
    font-size: 10px;
    letter-spacing: 2px;
    transition: .3s;
}

.book-cover-content button:hover {
    background: var(--gold);
    color: #160d0b;
}

.book-inside {
    position: absolute;
    inset: 0;
    background: #f4e8d1;
    border-radius: 5px 15px 15px 5px;
}

/* =========================================================
   PARTIKEL EMAS
   ========================================================= */

#particleCanvas {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 10000;
}

/* =========================================================
   HERO
   ========================================================= */

.hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    background:
        linear-gradient(rgba(10,5,5,.66), rgba(10,5,5,.88)),
        url('assets/cover.jpg') center/cover no-repeat;
    border-bottom: 1px solid rgba(215,181,109,.25);
}

.hero-content {
    position: relative;
    z-index: 5;
    max-width: 850px;
    padding: 40px 15px;
}

.hero-label {
    color: var(--gold-light);
    letter-spacing: 6px;
    font-size: 10px;
    text-transform: uppercase;
}

.hero h1 {
    font-family: 'Great Vibes', cursive;
    color: var(--gold-light);
    font-size: clamp(65px, 12vw, 115px);
    font-weight: 400;
    line-height: 1.15;
    margin: 25px 0;
    text-shadow: 0 5px 25px rgba(0,0,0,.7);
}

.hero .ampersand {
    font-family: 'Cormorant Garamond', serif;
    font-size: .55em;
    color: var(--gold);
}

.hero-date {
    letter-spacing: 5px;
    font-size: 12px;
    color: #f4dfb5;
}

.hero-location {
    color: #d4c2a4;
    font-size: 11px;
    margin-top: 14px;
}

.hero-decoration {
    width: 100px;
    height: 1px;
    background: var(--gold);
    margin: 25px auto;
}

.scroll-down {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    color: var(--gold-light);
    font-size: 10px;
    letter-spacing: 3px;
    animation: float 2s infinite;
}

@keyframes float {
    0%, 100% { transform: translate(-50%,0); }
    50% { transform: translate(-50%,-10px); }
}

/* =========================================================
   NAVIGASI
   ========================================================= */

.navbar {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(15,9,8,.94);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(215,181,109,.2);
}

.nav-inner {
    max-width: 1200px;
    margin: auto;
    padding: 15px 25px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 25px;
    flex-wrap: wrap;
}

.navbar a {
    color: #d8c7a7;
    font-size: 10px;
    letter-spacing: 1px;
    transition: .3s;
}

.navbar a:hover {
    color: var(--gold-light);
}

/* =========================================================
   PEMBUKAAN UNDANGAN
   ========================================================= */

.intro {
    background:
        radial-gradient(circle at center, #321816, #100b0a 70%);
    text-align: center;
}

.intro-content {
    max-width: 750px;
    margin: auto;
}

.bismillah {
    color: var(--gold-light);
    font-family: 'Cormorant Garamond', serif;
    font-size: 32px;
    margin-bottom: 30px;
}

.intro p {
    color: #d0c1a6;
    font-size: 13px;
    line-height: 2.2;
}

.intro-names {
    font-family: 'Great Vibes', cursive;
    color: var(--gold-light);
    font-size: 58px;
    margin: 30px 0;
}

/* =========================================================
   COUNTDOWN
   ========================================================= */

.countdown-section {
    background: linear-gradient(135deg, #45131d, #170b0a);
    text-align: center;
}

.countdown {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 35px;
}

.count-box {
    width: 110px;
    padding: 20px 10px;
    border: 1px solid rgba(215,181,109,.55);
    background: rgba(0,0,0,.2);
}

.count-box span {
    display: block;
    font-family: 'Cormorant Garamond', serif;
    font-size: 42px;
    color: var(--gold-light);
}

.count-box small {
    font-size: 9px;
    letter-spacing: 2px;
    color: #d4c2a4;
}

/* =========================================================
   PROFIL MEMPELAI
   ========================================================= */

.couple-section {
    background: #110c0b;
}

.couple-grid {
    display: grid;
    grid-template-columns: 1fr 100px 1fr;
    align-items: center;
    gap: 35px;
    max-width: 950px;
    margin: auto;
}

.couple-card {
    text-align: center;
}

.couple-photo {
    width: min(280px, 100%);
    height: 350px;
    margin: auto;
    padding: 8px;
    border: 1px solid var(--gold);
    background: linear-gradient(135deg, #4c101b, #130b0a);
    box-shadow: 0 0 30px rgba(215,181,109,.12);
    overflow: hidden;
}

.couple-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .5s ease;
}

.couple-photo:hover img {
    transform: scale(1.07);
}

.couple-card h3 {
    color: var(--gold-light);
    font-family: 'Great Vibes', cursive;
    font-size: 42px;
    font-weight: 400;
    margin-top: 25px;
}

.couple-card p {
    font-size: 11px;
    color: #c4b49a;
    line-height: 2;
    margin-top: 12px;
}

.couple-and {
    color: var(--gold);
    font-family: 'Great Vibes', cursive;
    font-size: 65px;
    text-align: center;
}

/* =========================================================
   CERITA CINTA
   ========================================================= */

.story-section {
    background: linear-gradient(180deg, #28100f, #100b0a);
}

.timeline {
    max-width: 750px;
    margin: auto;
    position: relative;
}

.timeline::before {
    content: "";
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 1px;
    background: var(--gold-dark);
}

.timeline-item {
    width: 50%;
    padding: 20px 40px;
    position: relative;
}

.timeline-item:nth-child(even) {
    margin-left: 50%;
}

.timeline-item::after {
    content: "";
    position: absolute;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--gold);
    top: 30px;
    right: -6px;
    box-shadow: 0 0 15px var(--gold);
}

.timeline-item:nth-child(even)::after {
    right: auto;
    left: -6px;
}

.timeline-item h3 {
    font-family: 'Cormorant Garamond', serif;
    color: var(--gold-light);
    font-size: 25px;
    margin-bottom: 10px;
}

.timeline-item p {
    font-size: 11px;
    line-height: 2;
    color: #c8b99f;
}

/* =========================================================
   ACARA PERNIKAHAN
   ========================================================= */

.event-section {
    background: #100b0a;
}

.event-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    max-width: 900px;
    margin: auto;
}

.event-card {
    text-align: center;
    padding: 40px 25px;
    border: 1px solid rgba(215,181,109,.45);
    background: linear-gradient(145deg, #301414, #160c0b);
    position: relative;
}

.event-card::before {
    content: "";
    position: absolute;
    inset: 8px;
    border: 1px solid rgba(215,181,109,.15);
    pointer-events: none;
}

.event-icon {
    font-size: 35px;
    color: var(--gold);
}

.event-card h3 {
    color: var(--gold-light);
    font-family: 'Cormorant Garamond', serif;
    font-size: 32px;
    margin: 15px 0;
}

.event-card p {
    color: #d0c0a5;
    font-size: 11px;
    line-height: 2.2;
}

.gold-button {
    display: inline-block;
    margin-top: 20px;
    padding: 11px 25px;
    border: 1px solid var(--gold);
    color: var(--gold-light);
    font-size: 10px;
    letter-spacing: 1px;
    transition: .3s;
    background: transparent;
}

.gold-button:hover {
    background: var(--gold);
    color: #170d0b;
}

/* =========================================================
   GALERI
   ========================================================= */

.gallery-section {
    background: linear-gradient(180deg, #321417, #100b0a);
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

.gallery-item {
    height: 280px;
    overflow: hidden;
    border: 1px solid rgba(215,181,109,.5);
    cursor: pointer;
    position: relative;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .6s ease;
}

.gallery-item:hover img {
    transform: scale(1.12);
}

.gallery-item::after {
    content: "✦";
    position: absolute;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    color: var(--gold-light);
    font-size: 35px;
    background: rgba(0,0,0,.2);
    opacity: 0;
    transition: .3s;
}

.gallery-item:hover::after {
    opacity: 1;
}

/* LIGHTBOX */

.lightbox {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.95);
    z-index: 5000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 25px;
}

.lightbox.active {
    display: flex;
}

.lightbox img {
    max-width: 90%;
    max-height: 85vh;
    border: 1px solid var(--gold);
}

.lightbox-close {
    position: absolute;
    top: 25px;
    right: 30px;
    color: white;
    font-size: 35px;
    background: none;
    border: none;
}

/* =========================================================
   RSVP DAN BUKU TAMU
   ========================================================= */

.form-section {
    background: #140c0b;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 35px;
    max-width: 1000px;
    margin: auto;
}

.form-card {
    padding: 35px;
    border: 1px solid rgba(215,181,109,.4);
    background: linear-gradient(145deg, #2b1212, #170c0b);
}

.form-card h3 {
    color: var(--gold-light);
    font-family: 'Cormorant Garamond', serif;
    font-size: 30px;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 17px;
}

.form-group label {
    display: block;
    color: #d8c5a5;
    font-size: 11px;
    margin-bottom: 8px;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 13px;
    color: white;
    background: rgba(0,0,0,.35);
    border: 1px solid rgba(215,181,109,.35);
    outline: none;
    border-radius: 0;
}

.form-group textarea {
    min-height: 110px;
    resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: var(--gold);
}

.form-group select option {
    color: #111;
}

.submit-button {
    width: 100%;
    padding: 13px;
    border: 1px solid var(--gold);
    background: var(--gold);
    color: #170c0b;
    font-size: 11px;
    letter-spacing: 2px;
    transition: .3s;
}

.submit-button:hover {
    background: transparent;
    color: var(--gold-light);
}

.form-message {
    margin-top: 15px;
    font-size: 11px;
    line-height: 1.7;
}

.guest-list {
    max-width: 850px;
    margin: 50px auto 0;
}

.guest-item {
    padding: 20px;
    border-bottom: 1px solid rgba(215,181,109,.25);
}

.guest-item h4 {
    color: var(--gold-light);
    font-size: 13px;
    margin-bottom: 7px;
}

.guest-item p {
    font-size: 11px;
    color: #c9b99f;
    line-height: 1.8;
}

.guest-item small {
    display: block;
    color: #8d7d68;
    font-size: 9px;
    margin-top: 8px;
}

/* =========================================================
   HADIAH PERNIKAHAN
   ========================================================= */

.gift-section {
    background: linear-gradient(135deg, #42151e, #160b0a);
    text-align: center;
}

.gift-content {
    max-width: 650px;
    margin: auto;
}

.gift-content p {
    font-size: 12px;
    color: #d4c2a5;
    line-height: 2;
}

.gift-card {
    margin: 35px auto 0;
    max-width: 390px;
    padding: 30px;
    background: rgba(0,0,0,.3);
    border: 1px solid rgba(215,181,109,.5);
}

.gift-card h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 30px;
    color: var(--gold-light);
}

.gift-card p {
    margin-top: 12px;
    font-size: 12px;
}

.gift-card button {
    margin-top: 20px;
    padding: 10px 22px;
    background: transparent;
    color: var(--gold-light);
    border: 1px solid var(--gold);
    font-size: 10px;
}

/* =========================================================
   PENUTUP
   ========================================================= */

.closing {
    text-align: center;
    background: #100b0a;
    padding: 110px 7%;
}

.closing h2 {
    font-family: 'Great Vibes', cursive;
    color: var(--gold-light);
    font-size: 65px;
    font-weight: 400;
    margin: 25px 0;
}

.closing p {
    max-width: 650px;
    margin: auto;
    font-size: 12px;
    color: #d0c0a6;
    line-height: 2.2;
}

.closing .signature {
    color: var(--gold);
    font-family: 'Great Vibes', cursive;
    font-size: 45px;
    margin-top: 35px;
}

footer {
    text-align: center;
    padding: 25px;
    color: #a38b66;
    font-size: 10px;
    border-top: 1px solid rgba(215,181,109,.2);
}

/* =========================================================
   TOMBOL MUSIK DAN SHARE
   ========================================================= */

.music-button,
.share-button {
    position: fixed;
    z-index: 200;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #3d171b;
    border: 1px solid var(--gold);
    color: var(--gold-light);
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 17px;
    box-shadow: 0 0 15px rgba(215,181,109,.15);
}

.music-button {
    bottom: 25px;
    right: 25px;
}

.share-button {
    bottom: 80px;
    right: 25px;
}

.music-button.playing {
    animation: musicRotate 4s linear infinite;
}

@keyframes musicRotate {
    to { transform: rotate(360deg); }
}

/* =========================================================
   KELopak BUNGA
   ========================================================= */

.petal {
    position: fixed;
    top: -30px;
    z-index: 10;
    pointer-events: none;
    color: rgba(215,181,109,.6);
    animation: fall linear forwards;
}

@keyframes fall {
    to {
        transform: translateY(110vh) rotate(720deg);
        opacity: 0;
    }
}

/* =========================================================
   ANIMASI REVEAL
   ========================================================= */

.reveal {
    opacity: 0;
    transform: translateY(35px);
    transition: opacity .9s ease, transform .9s ease;
}

.reveal.show {
    opacity: 1;
    transform: translateY(0);
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width: 850px) {
    section {
        padding: 80px 6%;
    }

    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .gallery-item {
        height: 230px;
    }

    .couple-grid {
        gap: 15px;
    }

    .couple-photo {
        height: 280px;
    }

    .batik-corner {
        width: 170px;
        height: 170px;
    }
}

@media(max-width: 600px) {
    .nav-inner {
        gap: 13px;
    }

    .navbar a {
        font-size: 8px;
    }

    .couple-grid {
        grid-template-columns: 1fr;
    }

    .couple-and {
        font-size: 45px;
        margin: 0;
    }

    .couple-photo {
        width: 230px;
        height: 290px;
    }

    .event-grid,
    .form-grid {
        grid-template-columns: 1fr;
    }

    .timeline::before {
        left: 8px;
    }

    .timeline-item,
    .timeline-item:nth-child(even) {
        width: 100%;
        margin-left: 0;
        padding: 20px 25px 20px 35px;
    }

    .timeline-item::after,
    .timeline-item:nth-child(even)::after {
        left: 2px;
        right: auto;
    }

    .gallery-grid {
        gap: 9px;
    }

    .gallery-item {
        height: 190px;
    }

    .count-box {
        width: 75px;
        padding: 14px 5px;
    }

    .count-box span {
        font-size: 30px;
    }

    .batik-corner {
        width: 115px;
        height: 115px;
    }

    .form-card {
        padding: 25px 18px;
    }
}

@media(prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        scroll-behavior: auto !important;
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
    }
}
</style>
</head>

<body class="locked">

<!-- =======================================================
     SVG BATIK LIBRARY
     ======================================================= -->

<svg class="svg-library" xmlns="http://www.w3.org/2000/svg">
<defs>
    <linearGradient id="batikGold" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#fff0b0"/>
        <stop offset="35%" stop-color="#d9ae50"/>
        <stop offset="65%" stop-color="#f5d77e"/>
        <stop offset="100%" stop-color="#99702c"/>
    </linearGradient>

    <g id="kawung">
        <ellipse cx="0" cy="-19" rx="13" ry="22"
                 fill="none" stroke="url(#batikGold)" stroke-width="2"/>
        <ellipse cx="0" cy="19" rx="13" ry="22"
                 fill="none" stroke="url(#batikGold)" stroke-width="2"/>
        <ellipse cx="-19" cy="0" rx="22" ry="13"
                 fill="none" stroke="url(#batikGold)" stroke-width="2"/>
        <ellipse cx="19" cy="0" rx="22" ry="13"
                 fill="none" stroke="url(#batikGold)" stroke-width="2"/>
        <circle cx="0" cy="0" r="5" fill="#f5d77e"/>
    </g>

    <g id="batikCorner">
        <path d="M0 0 H180 M0 0 V180"
              fill="none" stroke="url(#batikGold)" stroke-width="3"/>

        <path d="M12 12 H145 M12 12 V145"
              fill="none" stroke="url(#batikGold)" stroke-width="1.5"/>

        <path d="M0 0 Q60 0 60 60 Q60 100 100 100 Q140 100 140 140"
              fill="none" stroke="url(#batikGold)" stroke-width="3"/>

        <use href="#kawung" transform="translate(45 45) scale(.65)"/>
        <use href="#kawung" transform="translate(110 45) scale(.5)"/>
        <use href="#kawung" transform="translate(45 110) scale(.5)"/>
        <use href="#kawung" transform="translate(145 145) scale(.65)"/>

        <path d="M0 55 Q35 35 55 0 M0 90 Q55 55 90 0"
              fill="none" stroke="#d9ae50" stroke-width="1.5"/>

        <circle cx="12" cy="12" r="4" fill="#fff0b0"/>
        <circle cx="145" cy="145" r="4" fill="#fff0b0"/>
    </g>
</defs>
</svg>

<!-- =======================================================
     PEMBUKAAN BUKU
     ======================================================= -->

<div id="opening">
    <div class="book" id="book">
        <div class="book-inside"></div>

        <div class="book-cover">
            <div class="book-cover-content">
                <small>THE WEDDING OF</small>

                <h1>Saiful<br>&<br>Nida</h1>

                <p>23 • 12 • 2026</p>

                <button id="openInvitation">
                    BUKA UNDANGAN
                </button>
            </div>
        </div>
    </div>
</div>

<canvas id="particleCanvas"></canvas>

<!-- =======================================================
     HERO
     ======================================================= -->

<header class="hero" id="home">

    <div class="batik-corner top-left">
        <svg viewBox="0 0 180 180">
            <use href="#batikCorner"/>
        </svg>
    </div>

    <div class="batik-corner top-right">
        <svg viewBox="0 0 180 180">
            <use href="#batikCorner"/>
        </svg>
    </div>

    <div class="batik-corner bottom-left">
        <svg viewBox="0 0 180 180">
            <use href="#batikCorner"/>
        </svg>
    </div>

    <div class="batik-corner bottom-right">
        <svg viewBox="0 0 180 180">
            <use href="#batikCorner"/>
        </svg>
    </div>

    <div class="hero-content reveal">
        <p class="hero-label">The Wedding Celebration</p>

        <h1>
            Muchamad Saiful
            <span class="ampersand">&</span>
            Nida Rohadatul Aisy
        </h1>

        <div class="hero-decoration"></div>

        <p class="hero-date">23 DESEMBER 2026</p>

        <p class="hero-location">
            Menggoro, Tembarak, Temanggung
        </p>
    </div>

    <div class="scroll-down">SCROLL TO EXPLORE ↓</div>
</header>

<!-- =======================================================
     NAVBAR
     ======================================================= -->

<nav class="navbar">
    <div class="nav-inner">
        <a href="#home">HOME</a>
        <a href="#couple">MEMPELAI</a>
        <a href="#story">CERITA</a>
        <a href="#event">ACARA</a>
        <a href="#gallery">GALERI</a>
        <a href="#rsvp">RSVP</a>
        <a href="#gift">HADIAH</a>
    </div>
</nav>

<!-- =======================================================
     INTRO
     ======================================================= -->

<section class="intro">
    <div class="container intro-content reveal">

        <div class="bismillah">
            بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ
        </div>

        <div class="section-title">
            <small>Wedding Invitation</small>
            <h2>Assalamu'alaikum Wr. Wb.</h2>
            <div class="gold-line"></div>
        </div>

        <p>
            Dengan memohon rahmat dan ridha Allah SWT,
            kami bermaksud menyelenggarakan acara pernikahan
            putra-putri kami. Merupakan suatu kehormatan dan
            kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i
            berkenan hadir untuk memberikan doa restu.
        </p>

        <div class="intro-names">
            Saiful & Nida
        </div>

        <p>
            Atas kehadiran dan doa restunya, kami mengucapkan
            terima kasih.
        </p>
    </div>
</section>

<!-- =======================================================
     COUNTDOWN
     ======================================================= -->

<section class="countdown-section" id="countdown">
    <div class="container reveal">

        <div class="section-title">
            <small>Save The Date</small>
            <h2>Menuju Hari Bahagia</h2>
            <div class="gold-line"></div>
            <p>23 Desember 2026</p>
        </div>

        <div class="countdown">
            <div class="count-box">
                <span id="days">00</span>
                <small>HARI</small>
            </div>

            <div class="count-box">
                <span id="hours">00</span>
                <small>JAM</small>
            </div>

            <div class="count-box">
                <span id="minutes">00</span>
                <small>MENIT</small>
            </div>

            <div class="count-box">
                <span id="seconds">00</span>
                <small>DETIK</small>
            </div>
        </div>

    </div>
</section>

<!-- =======================================================
     PROFIL MEMPELAI
     ======================================================= -->

<section class="couple-section" id="couple">

    <div class="batik-corner top-left">
        <svg viewBox="0 0 180 180">
            <use href="#batikCorner"/>
        </svg>
    </div>

    <div class="container">

        <div class="section-title reveal">
            <small>Two Souls, One Heart</small>
            <h2>Mempelai</h2>
            <div class="gold-line"></div>
            <p>
                Dengan penuh rasa syukur, kami mempersembahkan
                dua insan yang akan memulai perjalanan baru.
            </p>
        </div>

        <div class="couple-grid">

            <div class="couple-card reveal">
                <div class="couple-photo parallax">
                    <img src="assets/saiful1.jpg"
                         alt="Muchamad Saiful">
                </div>

                <h3>Muchamad Saiful</h3>

                <p>
                    Putra dari<br>
                    Bapak Banariyanto<br>
                    & Ibu Siti Munawaroh
                </p>
            </div>

            <div class="couple-and">&</div>

            <div class="couple-card reveal">
                <div class="couple-photo parallax">
                    <img src="assets/nida.jpg"
                         alt="Nida Rohadatul Aisy">
                </div>

                <h3>Nida Rohadatul Aisy</h3>

                <p>
                    Putri dari<br>
                    Bapak Istachori<br>
                    & Ibu Daryanti
                </p>
            </div>

        </div>
    </div>
</section>

<!-- =======================================================
     CERITA CINTA
     ======================================================= -->

<section class="story-section" id="story">

    <div class="container">

        <div class="section-title reveal">
            <small>Our Journey</small>
            <h2>Cerita Cinta</h2>
            <div class="gold-line"></div>
            <p>
                Setiap perjalanan memiliki cerita,
                dan inilah awal perjalanan kami.
            </p>
        </div>

        <div class="timeline">

            <div class="timeline-item reveal">
                <h3>Awal Pertemuan</h3>
                <p>
                    Setiap kisah indah selalu memiliki awal.
                    Dari sebuah pertemuan sederhana, kami mulai
                    mengenal satu sama lain.
                </p>
            </div>

            <div class="timeline-item reveal">
                <h3>Menjalin Hubungan</h3>
                <p>
                    Seiring berjalannya waktu, rasa nyaman
                    tumbuh menjadi sebuah komitmen untuk
                    saling menjaga dan memahami.
                </p>
            </div>

            <div class="timeline-item reveal">
                <h3>Menuju Keseriusan</h3>
                <p>
                    Dengan niat baik dan doa, kami memutuskan
                    untuk melangkah ke jenjang yang lebih serius.
                </p>
            </div>

            <div class="timeline-item reveal">
                <h3>Hari Bahagia</h3>
                <p>
                    InsyaAllah, pada tanggal 23 Desember 2026,
                    kami akan memulai babak baru dalam kehidupan.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- =======================================================
     ACARA PERNIKAHAN
     ======================================================= -->

<section class="event-section" id="event">

    <div class="container">

        <div class="section-title reveal">
            <small>Wedding Day</small>
            <h2>Rangkaian Acara</h2>
            <div class="gold-line"></div>
            <p>
                Dengan penuh kebahagiaan, kami mengundang
                Anda untuk hadir dalam acara pernikahan kami.
            </p>
        </div>

        <div class="event-grid">

            <div class="event-card reveal">
                <div class="event-icon">✦</div>

                <h3>Akad Nikah</h3>

                <p>
                    Rabu, 23 Desember 2026<br>
                    Pukul 08.00 WIB - Selesai<br>
                    <br>
                    Menggoro, Tembarak,<br>
                    Temanggung
                </p>

                <a class="gold-button"
                   href="https://www.google.com/maps/search/?api=1&query=Menggoro+Tembarak+Temanggung"
                   target="_blank" rel="noopener">
                    PETUNJUK LOKASI
                </a>
            </div>

            <div class="event-card reveal">
                <div class="event-icon">❖</div>

                <h3>Resepsi</h3>

                <p>
                    Rabu, 23 Desember 2026<br>
                    Pukul 10.00 WIB - Selesai<br>
                    <br>
                    Menggoro, Tembarak,<br>
                    Temanggung
                </p>

                <a class="gold-button"
                   href="https://www.google.com/maps/search/?api=1&query=Menggoro+Tembarak+Temanggung"
                   target="_blank" rel="noopener">
                    PETUNJUK LOKASI
                </a>
            </div>

        </div>
    </div>
</section>

<!-- =======================================================
     GALERI FOTO
     ======================================================= -->

<section class="gallery-section" id="gallery">

    <div class="container">

        <div class="section-title reveal">
            <small>Our Memories</small>
            <h2>Galeri Foto</h2>
            <div class="gold-line"></div>
            <p>
                Beberapa momen yang menjadi bagian
                dari perjalanan cerita kami.
            </p>
        </div>

        <div class="gallery-grid">

            <div class="gallery-item reveal">
                <img src="assets/gallery1.jpg"
                     alt="Foto kenangan 1"
                     class="gallery-image">
            </div>

            <div class="gallery-item reveal">
                <img src="assets/gallery2.jpg"
                     alt="Foto kenangan 2"
                     class="gallery-image">
            </div>

            <div class="gallery-item reveal">
                <img src="assets/gallery3.jpg"
                     alt="Foto kenangan 3"
                     class="gallery-image">
            </div>

            <div class="gallery-item reveal">
                <img src="assets/gallery4.jpg"
                     alt="Foto kenangan 4"
                     class="gallery-image">
            </div>

        </div>
    </div>
</section>

<!-- LIGHTBOX -->

<div class="lightbox" id="lightbox">
    <button class="lightbox-close" id="closeLightbox">
        &times;
    </button>

    <img src="" alt="Foto diperbesar" id="lightboxImage">
</div>

<!-- =======================================================
     RSVP DAN BUKU TAMU
     ======================================================= -->

<section class="form-section" id="rsvp">

    <div class="container">

        <div class="section-title reveal">
            <small>Confirmation</small>
            <h2>RSVP & Ucapan</h2>
            <div class="gold-line"></div>
            <p>
                Silakan konfirmasi kehadiran dan
                kirimkan doa terbaik untuk kami.
            </p>
        </div>

        <div class="form-grid">

            <!-- FORM RSVP -->

            <div class="form-card reveal">

                <h3>Konfirmasi Kehadiran</h3>

                <form id="rsvpForm">

                    <input type="hidden"
                           name="action"
                           value="rsvp">

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text"
                               name="nama"
                               placeholder="Masukkan nama"
                               maxlength="150"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Tamu</label>
                        <input type="number"
                               name="jumlah"
                               min="1"
                               max="10"
                               value="1"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Konfirmasi Kehadiran</label>

                        <select name="kehadiran" required>
                            <option value="">Pilih kehadiran</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Tidak Hadir">Tidak Hadir</option>
                        </select>
                    </div>

                    <button class="submit-button" type="submit">
                        KIRIM KONFIRMASI
                    </button>

                    <div class="form-message"
                         id="rsvpMessage"
                         aria-live="polite"></div>

                </form>
            </div>

            <!-- FORM UCAPAN -->

            <div class="form-card reveal">

                <h3>Kirim Ucapan</h3>

                <form id="guestForm">

                    <input type="hidden"
                           name="action"
                           value="guestbook">

                    <div class="form-group">
                        <label>Nama Anda</label>

                        <input type="text"
                               name="nama"
                               placeholder="Masukkan nama"
                               maxlength="150"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Ucapan dan Doa</label>

                        <textarea name="ucapan"
                                  placeholder="Tuliskan ucapan dan doa..."
                                  maxlength="2000"
                                  required></textarea>
                    </div>

                    <button class="submit-button" type="submit">
                        KIRIM UCAPAN
                    </button>

                    <div class="form-message"
                         id="guestMessage"
                         aria-live="polite"></div>

                </form>
            </div>

        </div>

        <!-- DAFTAR UCAPAN -->

        <div class="guest-list" id="guestList">

            <div class="section-title">
                <small>Best Wishes</small>
                <h2>Ucapan & Doa</h2>
            </div>

            <?php if (count($comments) > 0): ?>

                <?php foreach ($comments as $comment): ?>

                    <div class="guest-item">
                        <h4><?= e($comment['nama']) ?></h4>

                        <p><?= nl2br(e($comment['ucapan'])) ?></p>

                        <small>
                            <?= e(date('d-m-Y H:i', strtotime($comment['created_at']))) ?>
                        </small>
                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p style="text-align:center;color:#b9aa91;font-size:12px">
                    Belum ada ucapan. Jadilah yang pertama memberikan doa.
                </p>

            <?php endif; ?>

        </div>

    </div>
</section>

<!-- =======================================================
     HADIAH PERNIKAHAN
     ======================================================= -->

<section class="gift-section" id="gift">

    <div class="container gift-content reveal">

        <div class="section-title">
            <small>Wedding Gift</small>
            <h2>Hadiah Pernikahan</h2>
            <div class="gold-line"></div>
        </div>

        <p>
            Doa restu Anda merupakan hadiah terindah bagi kami.
            Namun, apabila Anda ingin memberikan tanda kasih,
            dapat melalui informasi berikut.
        </p>

        <div class="gift-card">

            <h3>Bank BRI</h3>

            <p>Nomor Rekening</p>

            <p id="bankNumber">
                061401013504509
            </p>

            <p>Atas Nama: Muchamad Saiful</p>

            <button type="button" onclick="copyBank()">
                SALIN NOMOR REKENING
            </button>

        </div>

        <p style="margin-top:20px;font-size:10px">
            
        </p>

    </div>
</section>

<!-- =======================================================
     PENUTUP
     ======================================================= -->

<section class="closing">

    <div class="container reveal">

        <div class="section-title">
            <small>Thank You</small>
            <h2>Merupakan Suatu Kehormatan</h2>
            <div class="gold-line"></div>
        </div>

        <p>
            Merupakan suatu kebahagiaan bagi kami apabila
            Bapak/Ibu/Saudara/i berkenan hadir dan memberikan
            doa restu kepada kedua mempelai.
            Semoga Allah SWT menjadikan pernikahan ini
            sebagai keluarga yang sakinah, mawaddah,
            warahmah.
        </p>

        <div class="signature">
            Saiful & Nida
        </div>

        <p style="margin-top:20px">
            Wassalamu'alaikum Warahmatullahi Wabarakatuh
        </p>

    </div>
</section>

<footer>
    © 2026 Saiful & Nida Wedding Invitation
    <br>
    Made with Love
</footer>

<!-- =======================================================
     TOMBOL MUSIK DAN SHARE
     ======================================================= -->

<button class="music-button"
        id="musicButton"
        aria-label="Putar atau jeda musik">
    ♪
</button>

<button class="share-button"
        id="shareButton"
        aria-label="Bagikan undangan">
    ↗
</button>

<audio id="weddingMusic"
       src="assets/music/music.mp3"
       loop
       preload="none"></audio>

<!-- =======================================================
     JAVASCRIPT
     ======================================================= -->

<script>
/* =========================================================
   PEMBUKAAN UNDANGAN DAN PARTIKEL EMAS
   ========================================================= */

const opening = document.getElementById('opening');
const book = document.getElementById('book');
const openButton = document.getElementById('openInvitation');
const body = document.body;

const canvas = document.getElementById('particleCanvas');
const ctx = canvas.getContext('2d');

let particles = [];
let animationFrame;

function resizeCanvas() {
    canvas.width = window.innerWidth * devicePixelRatio;
    canvas.height = window.innerHeight * devicePixelRatio;

    ctx.setTransform(
        devicePixelRatio, 0, 0,
        devicePixelRatio, 0, 0
    );
}

resizeCanvas();
window.addEventListener('resize', resizeCanvas);

function createGoldExplosion() {
    const centerX = window.innerWidth / 2;
    const centerY = window.innerHeight / 2;

    particles = [];

    for (let i = 0; i < 180; i++) {
        const angle = Math.random() * Math.PI * 2;
        const speed = Math.random() * 8 + 2;

        particles.push({
            x: centerX,
            y: centerY,
            vx: Math.cos(angle) * speed,
            vy: Math.sin(angle) * speed,
            size: Math.random() * 3 + 1,
            alpha: 1,
            decay: Math.random() * .015 + .008
        });
    }

    if (!animationFrame) {
        animateParticles();
    }
}

function animateParticles() {
    ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);

    particles.forEach((p) => {
        p.x += p.vx;
        p.y += p.vy;

        p.vx *= .98;
        p.vy *= .98;

        p.alpha -= p.decay;

        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);

        ctx.fillStyle = `rgba(246, 215, 126, ${Math.max(0,p.alpha)})`;
        ctx.shadowBlur = 15;
        ctx.shadowColor = '#e9c46a';
        ctx.fill();
    });

    particles = particles.filter(p => p.alpha > 0);

    if (particles.length > 0) {
        animationFrame = requestAnimationFrame(animateParticles);
    } else {
        cancelAnimationFrame(animationFrame);
        animationFrame = null;
        ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
    }
}

openButton.addEventListener('click', () => {
    book.classList.add('open');

    createGoldExplosion();

    setTimeout(() => {
        opening.classList.add('hide');
        body.classList.remove('locked');

        startMusic();
        revealObserver.observe(document.body);
        createPetals();

    }, 1600);
});

/* =========================================================
   COUNTDOWN
   ========================================================= */

const weddingDate = new Date('2026-12-23T08:00:00+07:00').getTime();

function updateCountdown() {
    const now = Date.now();
    const distance = weddingDate - now;

    if (distance <= 0) {
        document.getElementById('days').textContent = '00';
        document.getElementById('hours').textContent = '00';
        document.getElementById('minutes').textContent = '00';
        document.getElementById('seconds').textContent = '00';
        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((distance / (1000 * 60)) % 60);
    const seconds = Math.floor((distance / 1000) % 60);

    document.getElementById('days').textContent = days;
    document.getElementById('hours').textContent = String(hours).padStart(2, '0');
    document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
    document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
}

updateCountdown();
setInterval(updateCountdown, 1000);

/* =========================================================
   ANIMASI SCROLL REVEAL
   ========================================================= */

const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('show');
            revealObserver.unobserve(entry.target);
        }
    });
}, {
    threshold: .12
});

document.querySelectorAll('.reveal').forEach(element => {
    revealObserver.observe(element);
});

/* =========================================================
   EFEK 3D PARALLAX FOTO
   ========================================================= */

const parallaxPhotos = document.querySelectorAll('.parallax');

parallaxPhotos.forEach(photo => {
    photo.addEventListener('mousemove', (event) => {
        if (window.innerWidth < 768) return;

        const rect = photo.getBoundingClientRect();

        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateY = ((x - centerX) / centerX) * 8;
        const rotateX = ((centerY - y) / centerY) * 8;

        photo.style.transform =
            `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    });

    photo.addEventListener('mouseleave', () => {
        photo.style.transform =
            'perspective(900px) rotateX(0) rotateY(0)';
    });
});

/* =========================================================
   GALERI LIGHTBOX
   ========================================================= */

const galleryImages = document.querySelectorAll('.gallery-image');
const lightbox = document.getElementById('lightbox');
const lightboxImage = document.getElementById('lightboxImage');
const closeLightbox = document.getElementById('closeLightbox');

galleryImages.forEach(img => {
    img.addEventListener('click', () => {
        lightboxImage.src = img.src;
        lightbox.classList.add('active');
    });
});

closeLightbox.addEventListener('click', () => {
    lightbox.classList.remove('active');
});

lightbox.addEventListener('click', (event) => {
    if (event.target === lightbox) {
        lightbox.classList.remove('active');
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        lightbox.classList.remove('active');
    }
});

/* =========================================================
   MUSIK LATAR
   ========================================================= */

const music = document.getElementById('weddingMusic');
const musicButton = document.getElementById('musicButton');

async function startMusic() {
    try {
        await music.play();
        musicButton.classList.add('playing');
        musicButton.textContent = '♫';
    } catch (error) {
        musicButton.classList.remove('playing');
        musicButton.textContent = '♪';
    }
}

musicButton.addEventListener('click', async () => {
    if (music.paused) {
        await startMusic();
    } else {
        music.pause();
        musicButton.classList.remove('playing');
        musicButton.textContent = '♪';
    }
});

/* =========================================================
   RSVP DAN BUKU TAMU AJAX
   ========================================================= */

async function submitForm(form, messageElement) {
    const button = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);

    button.disabled = true;
    button.textContent = 'MENGIRIM...';
    messageElement.textContent = '';

    try {
        const response = await fetch(window.location.pathname, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        messageElement.textContent = result.message;
        messageElement.style.color = result.success ? '#f5d77e' : '#ff9b9b';

        if (result.success) {
            form.reset();

            if (form.id === 'guestForm') {
                setTimeout(() => location.reload(), 900);
            }
        }

    } catch (error) {
        messageElement.textContent =
            'Terjadi kesalahan. Periksa koneksi dan database.';
        messageElement.style.color = '#ff9b9b';

    } finally {
        button.disabled = false;

        button.textContent =
            form.id === 'rsvpForm'
            ? 'KIRIM KONFIRMASI'
            : 'KIRIM UCAPAN';
    }
}

document.getElementById('rsvpForm').addEventListener('submit', function(event) {
    event.preventDefault();

    submitForm(
        this,
        document.getElementById('rsvpMessage')
    );
});

document.getElementById('guestForm').addEventListener('submit', function(event) {
    event.preventDefault();

    submitForm(
        this,
        document.getElementById('guestMessage')
    );
});

/* =========================================================
   SALIN NOMOR REKENING
   ========================================================= */

async function copyBank() {
    const number = document.getElementById('bankNumber').textContent.trim();

    try {
        await navigator.clipboard.writeText(number);
        alert('Nomor rekening berhasil disalin.');
    } catch (error) {
        alert('Tidak dapat menyalin otomatis. Silakan salin nomor rekening secara manual.');
    }
}

/* =========================================================
   BAGIKAN UNDANGAN
   ========================================================= */

document.getElementById('shareButton').addEventListener('click', async () => {
    const shareData = {
        title: 'Undangan Pernikahan Saiful & Nida',
        text: 'Dengan penuh kebahagiaan, kami mengundang Anda ke pernikahan Saiful & Nida pada 23 Desember 2026.',
        url: window.location.href
    };

    try {
        if (navigator.share) {
            await navigator.share(shareData);
        } else if (navigator.clipboard) {
            await navigator.clipboard.writeText(window.location.href);
            alert('Link undangan berhasil disalin.');
        } else {
            prompt('Salin link undangan berikut:', window.location.href);
        }
    } catch (error) {
        // Pengguna mungkin membatalkan dialog berbagi.
    }
});

/* =========================================================
   ANIMASI KELopak BUNGA / PARTIKEL EMAS
   ========================================================= */

function createPetals() {
    const symbols = ['✦', '❋', '✧', '✺'];

    setInterval(() => {
        if (document.hidden) return;

        const petal = document.createElement('div');

        petal.className = 'petal';
        petal.textContent =
            symbols[Math.floor(Math.random() * symbols.length)];

        petal.style.left = Math.random() * 100 + 'vw';
        petal.style.fontSize = (Math.random() * 12 + 8) + 'px';
        petal.style.animationDuration = (Math.random() * 5 + 5) + 's';

        document.body.appendChild(petal);

        setTimeout(() => {
            petal.remove();
        }, 11000);

    }, 700);
}
</script>

</body>
</html>
