<?php
// Muat semua class yang dibutuhkan
require_once __DIR__ . '/src/Validator.php';
require_once __DIR__ . '/src/FileUploader.php';
require_once __DIR__ . '/src/CsrfProtector.php';

session_start();

$errors  = [];
$old     = [];
$sukses  = false;

// Helper escape output agar aman dari XSS
function e(string $val): string
{
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

// ============================================
// PROSES FORM SAAT SUBMIT
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Verifikasi CSRF token dulu sebelum proses apapun
    if (!CsrfProtector::verifyToken($_POST['csrf_token'] ?? null)) {
        die('CSRF token tidak valid. Silakan muat ulang halaman.');
    }

    // 2. Ambil semua input & simpan untuk sticky form
    $old = [
        'nama'     => trim($_POST['nama'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'telepon'  => trim($_POST['telepon'] ?? ''),
        'usia'     => trim($_POST['usia'] ?? ''),
        'kota'     => $_POST['kota'] ?? '',
        'pesan'    => trim($_POST['pesan'] ?? ''),
    ];

    // 3. Validasi semua input menggunakan class Validator
    $validator = new Validator($_POST);
    $validator
        ->required('nama', 'Nama')
        ->minLength('nama', 3, 'Nama')
        ->maxLength('nama', 100, 'Nama')
        ->required('email', 'Email')
        ->email('email', 'Email')
        ->required('telepon', 'Telepon')
        ->pattern('telepon', '/^08[0-9]{8,11}$/', 'Format telepon tidak valid (contoh: 081234567890)')
        ->required('usia', 'Usia')
        ->numericBetween('usia', 17, 100, 'Usia')
        ->required('kota', 'Kota')
        ->required('pesan', 'Pesan')
        ->minLength('pesan', 10, 'Pesan')
        ->maxLength('pesan', 500, 'Pesan');

    $errors = $validator->getErrors();

    // 4. Proses upload foto kalau ada file yang dipilih
    $namaFile = null;
    if (!empty($_FILES['foto']['name'])) {
        $uploader = new FileUploader(
            allowedMimes:      ['image/jpeg', 'image/png', 'image/webp'],
            allowedExtensions: ['jpg', 'jpeg', 'png', 'webp'],
            maxSize:           2 * 1024 * 1024,
            uploadDir:         __DIR__ . '/uploads/'
        );
        $hasilUpload = $uploader->upload($_FILES['foto']);
        if (!$hasilUpload['success']) {
            $errors['foto'] = $hasilUpload['error'];
        } else {
            $namaFile = $hasilUpload['filename'];
        }
    }

    // 5. Kalau tidak ada error, tampilkan sukses
    if (empty($errors)) {
        $sukses = true;
        $old    = []; // Kosongkan form setelah sukses
    }
}

// Generate CSRF token untuk form
$csrfToken = CsrfProtector::getToken();

// Pilihan kota
$kotaOptions = [
    'wonosobo' => 'Wonosobo',
    'magelang' => 'Magelang',
    'purworejo' => 'Purworejo',
    'temanggung' => 'Temanggung',
    'banjarnegara' => 'Banjarnegara',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Registrasi - Pertemuan 6</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 560px;
        }

        h1 {
            font-size: 1.6rem;
            color: #1a202c;
            margin-bottom: 6px;
        }

        .subtitle {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: 600;
            font-size: 0.875rem;
            color: #2d3748;
            margin-bottom: 6px;
        }

        label .opsional {
            font-weight: 400;
            color: #a0aec0;
            font-size: 0.8rem;
        }

        input[type=text],
        input[type=email],
        input[type=number],
        input[type=file],
        select,
        textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #2d3748;
            transition: border-color 0.2s;
            background: #fff;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66,153,225,0.15);
        }

        input.error-input, select.error-input, textarea.error-input {
            border-color: #fc8181;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .error-msg {
            color: #e53e3e;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #4299e1;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 8px;
        }

        .btn-submit:hover {
            background: #3182ce;
        }

        .alert-success {
            background: #c6f6d5;
            border: 1px solid #9ae6b4;
            color: #276749;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.9rem;
        }

        .alert-success strong {
            display: block;
            margin-bottom: 4px;
            font-size: 1rem;
        }

        .badge {
            display: inline-block;
            background: #bee3f8;
            color: #2b6cb0;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 99px;
            margin-bottom: 20px;
        }

        .foto-preview {
            margin-top: 10px;
        }

        .foto-preview img {
            max-width: 120px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
        }
    </style>
</head>
<body>
<div class="container">
    <span class="badge">Pertemuan 6 — Form Handling PHP</span>
    <h1>Form Registrasi</h1>
    <p class="subtitle">Isi data dengan lengkap dan benar.</p>

    <?php if ($sukses): ?>
        <div class="alert-success">
            <strong>✅ Pendaftaran Berhasil!</strong>
            Data kamu berhasil dikirim dan sudah diproses dengan aman.
            <?php if ($namaFile): ?>
                <br>Foto disimpan sebagai: <strong><?= e($namaFile) ?></strong>
                <div class="foto-preview">
                    <img src="uploads/<?= e($namaFile) ?>" alt="Foto Upload">
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php" enctype="multipart/form-data">

        <!-- CSRF Token (wajib ada di setiap form POST) -->
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <!-- Nama -->
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama"
                   value="<?= e($old['nama'] ?? '') ?>"
                   class="<?= isset($errors['nama']) ? 'error-input' : '' ?>"
                   placeholder="Contoh: Budi Santoso">
            <?php if (isset($errors['nama'])): ?>
                <div class="error-msg"><?= e($errors['nama']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= e($old['email'] ?? '') ?>"
                   class="<?= isset($errors['email']) ? 'error-input' : '' ?>"
                   placeholder="Contoh: budi@gmail.com">
            <?php if (isset($errors['email'])): ?>
                <div class="error-msg"><?= e($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Telepon -->
        <div class="form-group">
            <label for="telepon">No. Telepon</label>
            <input type="text" id="telepon" name="telepon"
                   value="<?= e($old['telepon'] ?? '') ?>"
                   class="<?= isset($errors['telepon']) ? 'error-input' : '' ?>"
                   placeholder="Contoh: 081234567890">
            <?php if (isset($errors['telepon'])): ?>
                <div class="error-msg"><?= e($errors['telepon']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Usia -->
        <div class="form-group">
            <label for="usia">Usia</label>
            <input type="number" id="usia" name="usia"
                   value="<?= e($old['usia'] ?? '') ?>"
                   class="<?= isset($errors['usia']) ? 'error-input' : '' ?>"
                   placeholder="17 - 100" min="17" max="100">
            <?php if (isset($errors['usia'])): ?>
                <div class="error-msg"><?= e($errors['usia']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Kota (dropdown) -->
        <div class="form-group">
            <label for="kota">Kota</label>
            <select id="kota" name="kota"
                    class="<?= isset($errors['kota']) ? 'error-input' : '' ?>">
                <option value="">-- Pilih Kota --</option>
                <?php foreach ($kotaOptions as $val => $label): ?>
                    <option value="<?= e($val) ?>"
                        <?= (($old['kota'] ?? '') === $val) ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['kota'])): ?>
                <div class="error-msg"><?= e($errors['kota']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Pesan (textarea) -->
        <div class="form-group">
            <label for="pesan">Pesan</label>
            <textarea id="pesan" name="pesan"
                      class="<?= isset($errors['pesan']) ? 'error-input' : '' ?>"
                      placeholder="Tulis pesanmu di sini (10-500 karakter)..."><?= e($old['pesan'] ?? '') ?></textarea>
            <?php if (isset($errors['pesan'])): ?>
                <div class="error-msg"><?= e($errors['pesan']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Upload Foto (opsional) -->
        <div class="form-group">
            <label for="foto">Foto <span class="opsional">(opsional, JPG/PNG/WEBP, maks 2MB)</span></label>
            <input type="file" id="foto" name="foto" accept="image/*"
                   class="<?= isset($errors['foto']) ? 'error-input' : '' ?>">
            <?php if (isset($errors['foto'])): ?>
                <div class="error-msg"><?= e($errors['foto']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-submit">Daftar Sekarang</button>
    </form>
</div>
</body>
</html>
