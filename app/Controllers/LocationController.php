<?php

namespace App\Controllers;

use App\Models\DanhMuc;
use App\Models\DiaDiem;
use App\Models\GoiVe;
use App\Models\DanhGia;
use App\Models\YeuThich;
use App\Middlewares\AuthMiddleware;

class LocationController
{
    private DiaDiem $diaDiemModel;
    private DanhMuc $danhMucModel;
    private GoiVe $goiVeModel;
    private DanhGia $danhGiaModel;
    private YeuThich $yeuThichModel;

    public function __construct()
    {
        $this->diaDiemModel = new DiaDiem();
        $this->danhMucModel = new DanhMuc();
        $this->goiVeModel = new GoiVe();
        $this->danhGiaModel = new DanhGia();
        $this->yeuThichModel = new YeuThich();
    }

    public function index(): void
    {
        $filters = [
            'loai'        => $_GET['loai'] ?? '',
            'danh_muc_id' => $_GET['danh_muc_id'] ?? '',
            'tu_khoa'     => $_GET['tu_khoa'] ?? '',
            'co_ban_ve'   => $_GET['co_ban_ve'] ?? '',
            'muc_gia'     => $_GET['muc_gia'] ?? '',
            'dang_mo_cua' => $_GET['dang_mo_cua'] ?? '',
        ];

        $diaDiemList = $this->diaDiemModel->danhSach($filters);
        $danhMucList = $this->danhMucModel->tatCa();

        view('client.locations.index', [
            'title'       => 'Khám Phá Địa Điểm Ẩm Thực & Du Lịch Nha Trang',
            'diaDiemList' => $diaDiemList,
            'danhMucList' => $danhMucList,
            'filters'     => $filters,
        ]);
    }

    public function detail(string $slug): void
    {
        $diaDiem = $this->diaDiemModel->chiTietTheoSlug($slug);
        if (!$diaDiem) {
            http_response_code(404);
            view('layouts.404', ['title' => 'Không tìm thấy địa điểm']);
            return;
        }

        // Tăng lượt xem
        $this->diaDiemModel->tangLuotXem((int)$diaDiem['id']);

        // Lấy danh sách gói vé nếu địa điểm có bán vé
        $goiVeList = [];
        if ((bool)$diaDiem['co_ban_ve']) {
            $goiVeList = $this->goiVeModel->layTheoDiaDiem((int)$diaDiem['id']);
        }

        // Lấy đánh giá cộng đồng
        $danhGiaList = $this->danhGiaModel->layTheoDiaDiem((int)$diaDiem['id']);

        // Lấy bộ sưu tập ảnh phụ (gallery)
        $gallery = $this->diaDiemModel->layHinhAnh((int)$diaDiem['id']);

        // Lấy 4 địa điểm liên quan cùng danh mục
        $diaDiemLienQuan = $this->diaDiemModel->danhSach([
            'danh_muc_id' => $diaDiem['danh_muc_id'],
            'exclude_id'  => (int)$diaDiem['id'],
            'limit'       => 4,
        ]);

        // Kiểm tra địa điểm đã được yêu thích chưa
        $isFavorite = false;
        if (!empty($_SESSION['user_id'])) {
            $isFavorite = $this->yeuThichModel->kiemTra((int)$_SESSION['user_id'], (int)$diaDiem['id']);
        }

        // Lấy đánh giá của chính người dùng hiện tại (nếu đã đăng nhập)
        $myReview = null;
        if (!empty($_SESSION['user_id'])) {
            $myReview = $this->danhGiaModel->layCuaToiTheoDiaDiem((int)$_SESSION['user_id'], (int)$diaDiem['id']);
        }

        view('client.locations.detail', [
            'title'             => $diaDiem['ten_dia_diem'] . ' - Nha Trang',
            'diaDiem'           => $diaDiem,
            'goiVeList'         => $goiVeList,
            'danhGiaList'       => $danhGiaList,
            'myReview'          => $myReview,
            'gallery'           => $gallery,
            'diaDiemLienQuan'   => $diaDiemLienQuan,
            'isFavorite'        => $isFavorite,
        ]);
    }

    /**
     * Endpoint API JSON cung cấp danh sách tọa độ phục vụ Leaflet.js và AJAX filter
     */
    public function apiLocations(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $filters = [
            'loai'        => $_GET['loai'] ?? '',
            'danh_muc_id' => $_GET['danh_muc_id'] ?? '',
            'tu_khoa'     => $_GET['tu_khoa'] ?? '',
            'co_ban_ve'   => $_GET['co_ban_ve'] ?? '',
            'muc_gia'     => $_GET['muc_gia'] ?? '',
            'dang_mo_cua' => $_GET['dang_mo_cua'] ?? '',
        ];

        $markers = $this->diaDiemModel->layToaDoMap($filters);
        echo json_encode([
            'status' => 'success',
            'total'  => count($markers),
            'data'   => $markers
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Xử lý gửi/cập nhật đánh giá cộng đồng cho địa điểm
     */
    public function submitReview(): void
    {
        AuthMiddleware::checkCustomer();

        $slug = trim($_POST['slug'] ?? '');

        if (!validate_csrf()) {
            flash('error', 'Phiên làm việc hết hạn hoặc yêu cầu không hợp lệ.');
            redirect(url('dia-diem/' . $slug . '#reviewSection'));
            return;
        }

        $diaDiemId = (int)($_POST['dia_diem_id'] ?? 0);
        $soSao = (int)($_POST['so_sao'] ?? 5);
        $noiDung = trim($_POST['noi_dung'] ?? '');

        if ($diaDiemId <= 0 || empty($noiDung)) {
            flash('error', 'Vui lòng nhập nội dung đánh giá.');
            redirect(url('dia-diem/' . $slug . '#reviewSection'));
            return;
        }

        $soSao = max(1, min(5, $soSao));
        $nguoiDungId = (int)$_SESSION['user_id'];
        $existingReview = $this->danhGiaModel->layCuaToiTheoDiaDiem($nguoiDungId, $diaDiemId);

        // Kiểm tra số lượng ảnh tải lên (nếu có)
        $uploadedPhotos = [];
        if (!empty($_FILES['hinh_anh']['name'][0])) {
            $rawFiles = array_values(array_filter($_FILES['hinh_anh']['name']));
            $rawCount = count($rawFiles);

            if ($rawCount > 3) {
                flash('error', 'Bạn chỉ được đính kèm tối đa 3 hình ảnh cho mỗi đánh giá. Vui lòng chọn lại tối đa 3 ảnh.');
                redirect(url('dia-diem/' . $slug . '#reviewSection'));
                return;
            }

            // Kiểm tra dung lượng từng ảnh <= 5MB
            $maxBytes = 5 * 1024 * 1024;
            for ($i = 0; $i < $rawCount; $i++) {
                if ($_FILES['hinh_anh']['error'][$i] === UPLOAD_ERR_OK && $_FILES['hinh_anh']['size'][$i] > $maxBytes) {
                    $fileName = htmlspecialchars($_FILES['hinh_anh']['name'][$i]);
                    $fileSizeMb = round($_FILES['hinh_anh']['size'][$i] / (1024 * 1024), 1);
                    flash('error', "Ảnh '{$fileName}' có dung lượng {$fileSizeMb}MB vượt quá giới hạn 5MB cho phép. Vui lòng chọn ảnh nhẹ hơn.");
                    redirect(url('dia-diem/' . $slug . '#reviewSection'));
                    return;
                }
            }

            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/reviews';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $fileCount = min(3, $rawCount);

            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['hinh_anh']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['hinh_anh']['tmp_name'][$i];
                    $fileSize = $_FILES['hinh_anh']['size'][$i];
                    $mime = mime_content_type($tmpName);

                    if (in_array($mime, $allowedMimes) && $fileSize <= $maxBytes) {
                        $ext = pathinfo($_FILES['hinh_anh']['name'][$i], PATHINFO_EXTENSION);
                        $fileName = 'rev_' . $nguoiDungId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
                        $targetPath = $uploadDir . '/' . $fileName;

                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $uploadedPhotos[] = 'uploads/reviews/' . $fileName;
                        }
                    }
                }
            }
        }

        // Xử lý logic xóa ảnh cũ hoặc cập nhật ảnh mới
        $xoaAnh = !empty($_POST['xoa_anh']) && $_POST['xoa_anh'] === '1';
        $hinhAnhJson = null;

        if (!empty($uploadedPhotos)) {
            // Có upload ảnh mới -> dùng ảnh mới và dọn dẹp file ảnh cũ nếu có
            $hinhAnhJson = json_encode($uploadedPhotos, JSON_UNESCAPED_SLASHES);
            if ($existingReview && !empty($existingReview['hinh_anh'])) {
                $this->unlinkOldReviewPhotos($existingReview['hinh_anh']);
            }
        } elseif ($xoaAnh) {
            // Khách hàng bấm xóa ảnh -> xóa sạch ảnh cũ và đặt về null (chỉ để lại bình luận chữ)
            $hinhAnhJson = null;
            if ($existingReview && !empty($existingReview['hinh_anh'])) {
                $this->unlinkOldReviewPhotos($existingReview['hinh_anh']);
            }
        } elseif ($existingReview && !empty($existingReview['hinh_anh'])) {
            // Giữ nguyên ảnh đã có trước đó
            $hinhAnhJson = $existingReview['hinh_anh'];
        }

        $thanhCong = $this->danhGiaModel->themMoi($nguoiDungId, $diaDiemId, $soSao, $noiDung, $hinhAnhJson);
        if ($thanhCong) {
            flash('success', $existingReview ? 'Đã cập nhật đánh giá của bạn thành công!' : 'Cảm ơn bạn đã gửi đánh giá trải nghiệm!');
        } else {
            flash('error', 'Có lỗi xảy ra khi lưu đánh giá. Vui lòng thử lại.');
        }

        redirect(url('dia-diem/' . $slug . '#reviewSection'));
    }

    /**
     * Khách hàng tự xóa đánh giá của chính mình từ trang chi tiết địa điểm
     */
    public function deleteReview(): void
    {
        AuthMiddleware::checkCustomer();

        $slug = trim($_POST['slug'] ?? '');

        if (!validate_csrf()) {
            flash('error', 'Phiên làm việc hết hạn hoặc yêu cầu không hợp lệ.');
            redirect(url('dia-diem/' . $slug . '#reviewSection'));
            return;
        }

        $danhGiaId = (int)($_POST['danh_gia_id'] ?? 0);
        $userId = (int)$_SESSION['user_id'];

        if ($danhGiaId > 0) {
            $deleted = $this->danhGiaModel->xoaBoiNguoiDung($danhGiaId, $userId);
            if ($deleted) {
                flash('success', 'Đã xóa bài đánh giá của bạn thành công!');
            } else {
                flash('error', 'Không tìm thấy bài đánh giá hoặc bạn không có quyền xóa.');
            }
        }

        redirect(url('dia-diem/' . $slug . '#reviewSection'));
    }

    /**
     * Xóa các file ảnh đánh giá cũ khỏi thư mục public/uploads/reviews
     */
    private function unlinkOldReviewPhotos(string $hinhAnhJson): void
    {
        $photos = json_decode($hinhAnhJson, true);
        if (is_array($photos)) {
            $baseDir = dirname(__DIR__, 2) . '/public/';
            foreach ($photos as $p) {
                $fullPath = $baseDir . $p;
                if (file_exists($fullPath) && is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }
    }

    /**
     * API AJAX Bật/Tắt yêu thích địa điểm
     */
    public function toggleFavorite(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['user_id'])) {
            echo json_encode([
                'success'       => false,
                'require_login' => true,
                'message'       => 'Vui lòng đăng nhập để lưu địa điểm yêu thích!'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Đọc dữ liệu từ JSON body hoặc POST
        $rawInput = file_get_contents('php://input');
        $jsonInput = json_decode($rawInput, true) ?? [];
        $diaDiemId = (int)($jsonInput['dia_diem_id'] ?? $_POST['dia_diem_id'] ?? 0);
        $token = $jsonInput['csrf_token'] ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            echo json_encode([
                'success' => false,
                'message' => 'Phiên bảo mật không hợp lệ (CSRF error).'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($diaDiemId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Địa điểm không hợp lệ.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $isFavorited = $this->yeuThichModel->toggle($userId, $diaDiemId);
        $totalFav = $this->yeuThichModel->demTheoNguoiDung($userId);

        echo json_encode([
            'success'     => true,
            'is_favorite' => $isFavorited,
            'total_fav'   => $totalFav,
            'message'     => $isFavorited ? 'Đã thêm vào danh sách yêu thích!' : 'Đã bỏ khỏi danh sách yêu thích.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Endpoint API JSON gợi ý tức thì (Live Auto-Suggest Search)
     */
    public function searchSuggest(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $query = trim($_GET['q'] ?? '');

        if (mb_strlen($query, 'UTF-8') < 1) {
            echo json_encode(['status' => 'success', 'data' => []]);
            exit;
        }

        $results = $this->diaDiemModel->timKiemGoiY($query, 6);

        // Chuẩn hóa dữ liệu trả về cho frontend UI
        $formatted = array_map(function ($item) {
            return [
                'id'            => $item['id'],
                'ten_dia_diem'  => $item['ten_dia_diem'],
                'duong_dan'     => $item['duong_dan'],
                'url'           => url('dia-diem/' . $item['duong_dan']),
                'dia_chi'       => $item['dia_chi'],
                'anh_dai_dien'  => hinh_anh($item['anh_dai_dien']),
                'ten_danh_muc'  => $item['ten_danh_muc'],
                'loai_danh_muc' => $item['loai_danh_muc'],
                'gia_thap_nhat' => (float)$item['gia_thap_nhat'],
                'gia_cao_nhat'  => (float)$item['gia_cao_nhat'],
                'co_ban_ve'     => (bool)$item['co_ban_ve'],
                'diem_danh_gia' => (float)$item['diem_danh_gia'],
                'dang_mo_cua'   => (bool)$item['dang_mo_cua'],
            ];
        }, $results);

        echo json_encode([
            'status' => 'success',
            'query'  => $query,
            'total'  => count($formatted),
            'data'   => $formatted
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
