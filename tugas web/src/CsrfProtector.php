<?php

class CsrfProtector
{
    // Generate token baru dan simpan di session
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token = bin2hex(random_bytes(32)); // 64 karakter acak
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    // Ambil token yang sudah ada, atau buat baru kalau belum ada
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            return self::generateToken();
        }
        return $_SESSION['csrf_token'];
    }

    // Verifikasi token dari form dengan yang ada di session
    public static function verifyToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        // hash_equals mencegah timing attack
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
