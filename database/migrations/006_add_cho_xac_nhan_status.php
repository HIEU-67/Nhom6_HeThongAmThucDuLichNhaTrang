<?php

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Config\Database;

try {
    $pdo = Database::getConnection();
    echo "Connected to database successfully.\n";

    // 1. Thêm giá trị 'cho_xac_nhan' vào ENUM trang_thai_thanh_toan
    $pdo->exec("ALTER TABLE `don_hang` MODIFY COLUMN `trang_thai_thanh_toan` ENUM('cho_thanh_toan', 'cho_xac_nhan', 'da_thanh_toan', 'that_bai', 'da_hoan_tien') NOT NULL DEFAULT 'cho_thanh_toan'");
    echo "Updated trang_thai_thanh_toan ENUM in don_hang.\n";

    // 2. Thêm giá trị 'cho_xac_nhan' vào ENUM trang_thai_don_hang
    $pdo->exec("ALTER TABLE `don_hang` MODIFY COLUMN `trang_thai_don_hang` ENUM('moi_tao', 'cho_xac_nhan', 'da_xac_nhan', 'hoan_thanh', 'da_huy') NOT NULL DEFAULT 'moi_tao'");
    echo "Updated trang_thai_don_hang ENUM in don_hang.\n";

    echo "Migration 006 completed successfully.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

