<?php

class FileUploader
{
    private array $allowedMimes;
    private array $allowedExtensions;
    private int $maxSize;
    private string $uploadDir;

    public function __construct(
        array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'],
        array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'],
        int $maxSize = 2 * 1024 * 1024, // 2 MB
        string $uploadDir = ''
    ) {
        $this->allowedMimes = $allowedMimes;
        $this->allowedExtensions = $allowedExtensions;
        $this->maxSize = $maxSize;
        $this->uploadDir = $uploadDir ?: __DIR__ . '/../uploads/';

        // Buat folder uploads kalau belum ada
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload(array $file): array
    {
        // 1. Cek error dari PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $pesanError = [
                UPLOAD_ERR_NO_FILE   => 'Tidak ada file yang dipilih.',
                UPLOAD_ERR_INI_SIZE  => 'Ukuran file terlalu besar.',
                UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas form.',
            ];
            $pesan = $pesanError[$file['error']] ?? 'Upload gagal (kode: ' . $file['error'] . ').';
            return ['success' => false, 'filename' => null, 'error' => $pesan];
        }

        // 2. Cek ukuran file
        if ($file['size'] > $this->maxSize) {
            $maxMB = $this->maxSize / (1024 * 1024);
            return ['success' => false, 'filename' => null, 'error' => "Ukuran file maksimal {$maxMB}MB."];
        }

        // 3. Cek apakah file benar-benar dari HTTP upload
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'filename' => null, 'error' => 'File tidak valid.'];
        }

        // 4. Cek MIME type SEBENARNYA (bukan dari browser)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);
        if (!in_array($realMime, $this->allowedMimes, true)) {
            return ['success' => false, 'filename' => null, 'error' => 'Tipe file tidak diizinkan. Hanya: ' . implode(', ', $this->allowedMimes)];
        }

        // 5. Cek ekstensi file (whitelist)
        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ekstensi, $this->allowedExtensions, true)) {
            return ['success' => false, 'filename' => null, 'error' => 'Ekstensi file tidak diizinkan.'];
        }

        // 6. Generate nama file unik agar tidak bisa ditebak
        $namaFile   = sprintf('%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(8)), $ekstensi);
        $targetPath = $this->uploadDir . $namaFile;

        // 7. Pindahkan file ke folder uploads
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'filename' => null, 'error' => 'Gagal menyimpan file.'];
        }

        // 8. Set permission file (tidak bisa dieksekusi)
        chmod($targetPath, 0644);

        return ['success' => true, 'filename' => $namaFile, 'error' => null];
    }
}
