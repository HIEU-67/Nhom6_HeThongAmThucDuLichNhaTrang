<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class GoiVe
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function layTheoDiaDiem(int $diaDiemId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM goi_ve 
            WHERE dia_diem_id = :dia_diem_id AND trang_thai = 'dang_ban'
            ORDER BY gia_nguoi_lon ASC
        ");
        $stmt->execute(['dia_diem_id' => $diaDiemId]);
        return $stmt->fetchAll();
    }

    public function timTheoId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM goi_ve WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Lấy tất cả gói vé kèm tên địa điểm cho trang quản trị
     */
    public function layTatCa(): array
    {
        $stmt = $this->db->query("
            SELECT gv.*, dd.ten_dia_diem, dd.duong_dan as dia_diem_slug
            FROM goi_ve gv
            JOIN dia_diem dd ON gv.dia_diem_id = dd.id
            ORDER BY dd.ten_dia_diem ASC, gv.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số gói vé theo từ khóa (phục vụ phân trang)
     */
    public function demTatCa(?string $tuKhoa = null): int
    {
        $sql = "
            SELECT COUNT(gv.id)
            FROM goi_ve gv
            JOIN dia_diem dd ON gv.dia_diem_id = dd.id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($tuKhoa)) {
            $sql .= " AND (gv.ten_goi_ve LIKE :tk_gv OR dd.ten_dia_diem LIKE :tk_dd)";
            $pattern = '%' . trim($tuKhoa) . '%';
            $params['tk_gv'] = $pattern;
            $params['tk_dd'] = $pattern;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy danh sách gói vé có phân trang & tìm kiếm cho trang quản trị
     */
    public function layPhanTrang(int $page = 1, int $perPage = 10, ?string $tuKhoa = null): array
    {
        $sql = "
            SELECT gv.*, dd.ten_dia_diem, dd.duong_dan as dia_diem_slug
            FROM goi_ve gv
            JOIN dia_diem dd ON gv.dia_diem_id = dd.id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($tuKhoa)) {
            $sql .= " AND (gv.ten_goi_ve LIKE :tk_gv OR dd.ten_dia_diem LIKE :tk_dd)";
            $pattern = '%' . trim($tuKhoa) . '%';
            $params['tk_gv'] = $pattern;
            $params['tk_dd'] = $pattern;
        }

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql .= " ORDER BY dd.ten_dia_diem ASC, gv.id DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Thêm gói vé mới
     */
    public function them(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO goi_ve (dia_diem_id, ten_goi_ve, mo_ta, gia_nguoi_lon, gia_tre_em, trang_thai)
            VALUES (:dia_diem_id, :ten_goi_ve, :mo_ta, :gia_nguoi_lon, :gia_tre_em, :trang_thai)
        ");
        $stmt->execute([
            'dia_diem_id'   => $data['dia_diem_id'],
            'ten_goi_ve'    => $data['ten_goi_ve'],
            'mo_ta'         => $data['mo_ta'] ?? null,
            'gia_nguoi_lon' => $data['gia_nguoi_lon'],
            'gia_tre_em'    => $data['gia_tre_em'] ?? 0,
            'trang_thai'    => $data['trang_thai'] ?? 'dang_ban'
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật gói vé
     */
    public function capNhat(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE goi_ve SET
                dia_diem_id   = :dia_diem_id,
                ten_goi_ve    = :ten_goi_ve,
                mo_ta         = :mo_ta,
                gia_nguoi_lon = :gia_nguoi_lon,
                gia_tre_em    = :gia_tre_em,
                trang_thai    = :trang_thai
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'            => $id,
            'dia_diem_id'   => $data['dia_diem_id'],
            'ten_goi_ve'    => $data['ten_goi_ve'],
            'mo_ta'         => $data['mo_ta'] ?? null,
            'gia_nguoi_lon' => $data['gia_nguoi_lon'],
            'gia_tre_em'    => $data['gia_tre_em'] ?? 0,
            'trang_thai'    => $data['trang_thai'] ?? 'dang_ban'
        ]);
    }

    /**
     * Xóa hoặc tạm dừng gói vé
     */
    public function xoa(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM goi_ve WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            $stmt = $this->db->prepare("UPDATE goi_ve SET trang_thai = 'tam_dung' WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        }
    }
}
