<?php

namespace App\Helpers;

class LangHelper
{
    private static array $translations = [];
    private static ?string $currentLang = null;

    /**
     * Lấy ngôn ngữ hiện tại của người dùng (mặc định 'vi', hỗ trợ mã ngôn ngữ quốc tế IETF)
     */
    public static function getLang(): string
    {
        if (self::$currentLang !== null) {
            return self::$currentLang;
        }

        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['app_lang'])) {
            $code = trim((string)$_SESSION['app_lang']);
            self::$currentLang = preg_match('/^[a-z]{2,3}(-[a-zA-Z]{2,4})?$/', $code) ? $code : 'vi';
        } elseif (!empty($_COOKIE['googtrans'])) {
            $parts = explode('/', trim((string)$_COOKIE['googtrans'], '/'));
            $code = end($parts);
            self::$currentLang = ($code && preg_match('/^[a-z]{2,3}(-[a-zA-Z]{2,4})?$/', $code)) ? $code : 'vi';
        } else {
            self::$currentLang = 'vi';
        }

        return self::$currentLang;
    }

    /**
     * Thiết lập ngôn ngữ cho phiên làm việc (hỗ trợ tất cả mã ngôn ngữ trên thế giới)
     */
    public static function setLang(string $lang): void
    {
        $lang = trim($lang);
        if (!preg_match('/^[a-z]{2,3}(-[a-zA-Z]{2,4})?$/', $lang)) {
            $lang = 'vi';
        }
        self::$currentLang = $lang;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['app_lang'] = $lang;
        }
    }

    /**
     * Lấy chuỗi bản dịch theo key
     */
    public static function trans(string $key, ?string $default = null): string
    {
        $lang = self::getLang();

        if (!isset(self::$translations[$lang])) {
            $langFile = dirname(__DIR__) . '/Lang/' . $lang . '.php';
            if (file_exists($langFile)) {
                self::$translations[$lang] = require $langFile;
            } else {
                self::$translations[$lang] = [];
            }
        }

        return self::$translations[$lang][$key] ?? ($default !== null ? $default : $key);
    }
}
