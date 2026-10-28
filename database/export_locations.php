<?php

// Script tự động xuất dữ liệu Địa điểm, Ảnh gallery và Gói vé để đồng bộ qua Git cho nhóm

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;

try {
    $pdo = Database::getConnection();
    echo "=== XUẤT DỮ LIỆU ĐỊA ĐIỂM ĐỂ ĐỒNG BỘ CHO CẢ NHÓM ===\n";

    $outputFile = __DIR__ . '/seeds/locations_sync.sql';
    $sqlContent = "-- Tự động sinh bởi export_locations.php lúc " . date('Y-m-d H:i:s') . "\n";
    $sqlContent .= "SET NAMES utf8mb4;\n";
    $sqlContent .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    // 1. Xuất bảng dia_diem
    $stmt = $pdo->query("SELECT * FROM dia_diem ORDER BY id ASC");
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $locCount = count($locations);

    if (!empty($locations)) {
        $exportedIds = array_column($locations, 'id');
        $idListStr = implode(',', $exportedIds);

        $sqlContent .= "-- 1. DỮ LIỆU BẢNG DIA_DIEM ({$locCount} địa điểm)\n";
        $sqlContent .= "-- Đồng bộ xóa những địa điểm không còn tồn tại trên máy người xuất\n";
        $sqlContent .= "DELETE FROM `dia_diem` WHERE `id` NOT IN ({$idListStr});\n";

        foreach ($locations as $loc) {
            $cols = array_keys($loc);
            $colNames = implode('`, `', $cols);
            $values = array_map(function ($val) use ($pdo) {
                if ($val === null) return 'NULL';
                return $pdo->quote((string)$val);
            }, array_values($loc));
            $valStr = implode(', ', $values);

            // Sinh câu lệnh INSERT ... ON DUPLICATE KEY UPDATE
            $updates = [];
            foreach ($cols as $col) {
                if ($col === 'id') continue;
                $updates[] = "`{$col}` = VALUES(`{$col}`)";
            }
            $updateStr = implode(', ', $updates);

            $sqlContent .= "INSERT INTO `dia_diem` (`{$colNames}`) VALUES ({$valStr}) ON DUPLICATE KEY UPDATE {$updateStr};\n";
        }
        $sqlContent .= "\n";
    }

    // 2. Xuất bảng hinh_anh_dia_diem
    $stmtImg = $pdo->query("SELECT * FROM hinh_anh_dia_diem ORDER BY id ASC");
    $images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
    $imgCount = count($images);

    if (!empty($images)) {
        $imgIds = array_column($images, 'id');
        $imgIdStr = implode(',', $imgIds);
        $sqlContent .= "-- 2. DỮ LIỆU BẢNG HINH_ANH_DIA_DIEM ({$imgCount} ảnh gallery)\n";
        $sqlContent .= "DELETE FROM `hinh_anh_dia_diem` WHERE `id` NOT IN ({$imgIdStr});\n";
        foreach ($images as $img) {
            $cols = array_keys($img);
            $colNames = implode('`, `', $cols);
            $values = array_map(function ($val) use ($pdo) {
                if ($val === null) return 'NULL';
                return $pdo->quote((string)$val);
            }, array_values($img));
            $valStr = implode(', ', $values);

            $updates = [];
            foreach ($cols as $col) {
                if ($col === 'id') continue;
                $updates[] = "`{$col}` = VALUES(`{$col}`)";
            }
            $updateStr = implode(', ', $updates);

            $sqlContent .= "INSERT INTO `hinh_anh_dia_diem` (`{$colNames}`) VALUES ({$valStr}) ON DUPLICATE KEY UPDATE {$updateStr};\n";
        }
        $sqlContent .= "\n";
    }

    // 3. Xuất bảng goi_ve
    $stmtGoi = $pdo->query("SELECT * FROM goi_ve ORDER BY id ASC");
    $packages = $stmtGoi->fetchAll(PDO::FETCH_ASSOC);
    $pkgCount = count($packages);

    if (!empty($packages)) {
        $pkgIds = array_column($packages, 'id');
        $pkgIdStr = implode(',', $pkgIds);
        $sqlContent .= "-- 3. DỮ LIỆU BẢNG GOI_VE ({$pkgCount} gói vé)\n";
        $sqlContent .= "DELETE FROM `goi_ve` WHERE `id` NOT IN ({$pkgIdStr});\n";
        foreach ($packages as $pkg) {
            $cols = array_keys($pkg);
            $colNames = implode('`, `', $cols);
            $values = array_map(function ($val) use ($pdo) {
                if ($val === null) return 'NULL';
                return $pdo->quote((string)$val);
            }, array_values($pkg));
            $valStr = implode(', ', $values);

            $updates = [];
            foreach ($cols as $col) {
                if ($col === 'id') continue;
                $updates[] = "`{$col}` = VALUES(`{$col}`)";
            }
            $updateStr = implode(', ', $updates);

            $sqlContent .= "INSERT INTO `goi_ve` (`{$colNames}`) VALUES ({$valStr}) ON DUPLICATE KEY UPDATE {$updateStr};\n";
        }
        $sqlContent .= "\n";
    }

    $sqlContent .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    file_put_contents($outputFile, $sqlContent);

    echo "[THÀNH CÔNG] Đã xuất thành công:\n";
    echo "  - {$locCount} địa điểm\n";
    echo "  - {$imgCount} ảnh gallery\n";
    echo "  - {$pkgCount} gói vé tham quan\n";
    echo "File lưu tại: database/seeds/locations_sync.sql\n\n";
    echo "Bây giờ bạn chỉ cần commit file này và thư mục ảnh lên Git:\n";
    echo "  git add database/seeds/locations_sync.sql public/uploads/locations/\n";
    echo "  git commit -m \"feat: Cap nhat thong tin dia diem va anh moi\"\n";
    echo "  git push\n";

} catch (Exception $e) {
    echo "[LỖI] " . $e->getMessage() . "\n";
    exit(1);
}

