# Pemrograman Web — Pertemuan 6
**Form Handling & Keamanan PHP**

Mata Kuliah: Pemrograman Web (MBKP-07.03.204)  
Program Studi: Teknik Informatika – FASTIKOM UNSIQ  
Semester: Genap 2025/2026

---

## Fitur
- Validasi server-side menggunakan class `Validator` (OOP)
- Upload file aman dengan validasi MIME type (`finfo`)
- Proteksi CSRF dengan token per session
- Sticky form (input tetap terisi saat error)
- Escape output dengan `htmlspecialchars()` (anti XSS)

## Struktur Folder
```
tugas web/
├── index.php           # Halaman utama (form registrasi)
├── src/
│   ├── Validator.php       # Class validasi server-side
│   ├── FileUploader.php    # Class upload file aman
│   └── CsrfProtector.php   # Class proteksi CSRF
├── uploads/
│   └── .htaccess           # Blok eksekusi PHP di folder uploads
└── README.md
```

## Cara Menjalankan
1. Copy folder ini ke `C:\xampp\htdocs\`
2. Jalankan Apache di XAMPP
3. Buka browser: `http://localhost/tugas web/`
