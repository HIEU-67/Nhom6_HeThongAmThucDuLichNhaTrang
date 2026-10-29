<?php

// Script tự động nạp dữ liệu Địa điểm, Ảnh gallery và Gói vé sau khi git pull về máy

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;

try {
    $pdo = Database::getConnection();
    echo "=== ĐỒNG BỘ DỮ LIỆU ĐỊA ĐIỂM TỪ NHÓM VÀO CƠ SỞ DỮ LIỆU CỤC BỘ ===\n";

    $syncFile = __DIR__ . '/seeds/locations_sync.sql';
    if (!file_exists($syncFile)) {
        die("[LỖI] Không tìm thấy file locations_sync.sql. Hãy đảm bảo bạn đã git pull đầy đủ.\n");
    }

    echo "Đang nạp file database/seeds/locations_sync.sql...\n";
    $sql = file_get_contents($syncFile);

    $pdo->exec($sql);

    // Thống kê kết quả
    $locCount = $pdo->query("SELECT COUNT(*) FROM dia_diem")->fetchColumn();
    $imgCount = $pdo->query("SELECT COUNT(*) FROM hinh_anh_dia_diem")->fetchColumn();
    $pkgCount = $pdo->query("SELECT COUNT(*) FROM goi_ve")->fetchColumn();

    echo "[THÀNH CÔNG] Dữ liệu địa điểm đã được đồng bộ hoàn tất!\n";
    echo "  - Tổng số địa điểm hiện tại: {$locCount}\n";
    echo "  - Tổng số ảnh gallery: {$imgCount}\n";
    echo "  - Tổng số gói vé: {$pkgCount}\n";
    echo "Bây giờ bạn có thể mở website trên trình duyệt để kiểm tra địa điểm mới!\n";

} catch (Exception $e) {
    echo "[LỖI ĐỒNG BỘ] " . $e->getMessage() . "\n";
    exit(1);
}

