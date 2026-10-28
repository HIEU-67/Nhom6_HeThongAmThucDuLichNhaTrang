<?php

namespace App\Controllers\Admin;

use App\Config\Database;
use App\Middlewares\AuthMiddleware;
use Exception;
use PDO;

class OrderManageController
{
    public function index(): void
    {
        AuthMiddleware::checkAdmin();

        $db = Database::getConnection();
        $tuKhoa = trim($_GET['q'] ?? '');
        $trangThaiDon = trim($_GET['trang_thai_don'] ?? '');
        $trangThaiTt = trim($_GET['trang_thai_tt'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $baseWhere = " WHERE dh.ngay_xoa IS NULL";
        $params = [];

        if (!empty($tuKhoa)) {
            $baseWhere .= " AND (dh.ma_don_hang LIKE :q_ma OR dh.sdt_khach_hang LIKE :q_sdt OR dh.ten_khach_hang LIKE :q_ten OR dh.email_khach_hang LIKE :q_email)";
            $params['q_ma']    = '%' . $tuKhoa . '%';
            $params['q_sdt']   = '%' . $tuKhoa . '%';
            $params['q_ten']   = '%' . $tuKhoa . '%';
            $params['q_email'] = '%' . $tuKhoa . '%';
        }

        if (!empty($trangThaiDon)) {
            $baseWhere .= " AND dh.trang_thai_don_hang = :tt_don";
            $params['tt_don'] = $trangThaiDon;
        }

        if (!empty($trangThaiTt)) {
            $baseWhere .= " AND dh.trang_thai_thanh_toan = :tt_tt";
            $params['tt_tt'] = $trangThaiTt;
        }

        // Đếm tổng số đơn hàng thỏa mãn điều kiện
        $countSql = "
            SELECT COUNT(*)
            FROM don_hang dh
            JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id
            {$baseWhere}
        ";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $totalOrders = (int)$countStmt->fetchColumn();

        // Lấy danh sách có phân trang
        $offset = ($page - 1) * $perPage;
        $sql = "
            SELECT dh.*, nd.ho_ten as ten_tai_khoan
            FROM don_hang dh
            JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id
            {$baseWhere}
            ORDER BY dh.id DESC
            LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        view('admin.orders.index', [
            'title'        => 'Quản Lý Đơn Hàng & Soát Vé E-Voucher',
            'orders'       => $orders,
            'tuKhoa'       => $tuKhoa,
            'trangThaiDon' => $trangThaiDon,
            'trangThaiTt'  => $trangThaiTt,
            'page'         => $page,
            'perPage'      => $perPage,
            'totalOrders'  => $totalOrders,
        ]);
    }

    public function capNhatTrangThai(int $donHangId, string $trangThaiMoi): bool
    {
        AuthMiddleware::checkAdmin();

        $danhSachHopLe = ['moi_tao', 'da_xac_nhan', 'hoan_thanh', 'da_huy'];
        if (!in_array($trangThaiMoi, $danhSachHopLe)) {
            throw new Exception("Trạng thái đơn hàng không hợp lệ.");
        }

        $db = Database::getConnection();

        // 1. Kiểm tra đơn hàng tồn tại
        $stmtCheck = $db->prepare("SELECT * FROM don_hang WHERE id = :id AND ngay_xoa IS NULL");
        $stmtCheck->execute(['id' => $donHangId]);
        $order = $stmtCheck->fetch();

        if (!$order) {
            throw new Exception("Không tìm thấy đơn hàng cần cập nhật.");
        }

        // 2. NGHIỆP VỤ BẢO VỆ: Nếu là đổi vé vào cổng (hoàn thành)
        if ($trangThaiMoi === 'hoan_thanh') {
            // Đối với đơn hàng thanh toán online (chuyển khoản, vnpay, momo): BẮT BUỘC phải thanh toán trước
            if ($order['phuong_thuc_thanh_toan'] !== 'tai_quay' && $order['trang_thai_thanh_toan'] !== 'da_thanh_toan') {
                throw new Exception("Không thể đổi vé! Đơn hàng trực tuyến #" . $order['ma_don_hang'] . " chưa được thanh toán. Vui lòng bấm 'Xác nhận đã nhận tiền' trước.");
            }

            // Đối với đơn hàng thanh toán tại quầy: Khi Admin bấm Đổi vé (thu tiền mặt), tự động cập nhật luôn trạng thái thanh toán sang 'da_thanh_toan'
            if ($order['phuong_thuc_thanh_toan'] === 'tai_quay' && $order['trang_thai_thanh_toan'] !== 'da_thanh_toan') {
                $db->beginTransaction();
                try {
                    $stmtUpdate = $db->prepare("
                        UPDATE don_hang 
                        SET trang_thai_don_hang = 'hoan_thanh',
                            trang_thai_thanh_toan = 'da_thanh_toan' 
                        WHERE id = :id AND ngay_xoa IS NULL
                    ");
                    $stmtUpdate->execute(['id' => $donHangId]);

                    // Ghi nhận giao dịch thanh toán tiền mặt tại quầy
                    $maGd = 'CASH' . date('YmdHis') . rand(100, 999);
                    $stmtTx = $db->prepare("
                        INSERT INTO giao_dich_thanh_toan 
                        (don_hang_id, cong_thanh_toan, ma_giao_dich, so_tien, loai_tien_te, du_lieu_phan_hoi, trang_thai) 
                        VALUES 
                        (:don_hang_id, 'tai_quay', :ma_gd, :so_tien, 'VND', :phan_hoi, 'thanh_cong')
                    ");
                    $stmtTx->execute([
                        'don_hang_id' => $donHangId,
                        'ma_gd'       => $maGd,
                        'so_tien'     => $order['tong_tien'],
                        'phan_hoi'    => json_encode([
                            'hinh_thuc' => 'Thanh toán tiền mặt tại quầy soát vé',
                            'thoi_gian' => date('Y-m-d H:i:s'),
                            'admin_id'  => $_SESSION['user_id'] ?? 0
                        ], JSON_UNESCAPED_UNICODE)
                    ]);

                    $db->commit();
                    return true;
                } catch (Exception $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    throw $e;
                }
            }
        }

        $stmt = $db->prepare("
            UPDATE don_hang 
            SET trang_thai_don_hang = :trang_thai 
            WHERE id = :id AND ngay_xoa IS NULL
        ");
        return $stmt->execute([
            'trang_thai' => $trangThaiMoi,
            'id'         => $donHangId
        ]);
    }

    public function updateStatusAction(): void
    {
        AuthMiddleware::checkAdmin();

        if (!validate_csrf()) {
            flash('error', 'Phiên làm việc hết hạn hoặc yêu cầu không hợp lệ.');
            redirect(url('admin/orders'));
            return;
        }

        $donHangId = (int)($_POST['don_hang_id'] ?? 0);
        $trangThai = $_POST['trang_thai'] ?? '';

        try {
            $this->capNhatTrangThai($donHangId, $trangThai);
            flash('success', 'Đã cập nhật trạng thái đơn hàng thành công!');
        } catch (Exception $e) {
            flash('error', 'Lỗi: ' . $e->getMessage());
        }

        redirect(url('admin/orders'));
    }

    /**
     * Xác nhận đã nhận tiền chuyển khoản / thanh toán online cho đơn hàng từ phía Quản trị viên
     */
    public function confirmPaymentAction(): void
    {
        AuthMiddleware::checkAdmin();

        if (!validate_csrf()) {
            flash('error', 'Phiên làm việc hết hạn hoặc yêu cầu không hợp lệ.');
            redirect(url('admin/orders'));
            return;
        }

        $donHangId = (int)($_POST['don_hang_id'] ?? 0);
        if ($donHangId <= 0) {
            flash('error', 'Mã đơn hàng không hợp lệ.');
            redirect(url('admin/orders'));
            return;
        }

        $db = Database::getConnection();
        $stmtCheck = $db->prepare("SELECT * FROM don_hang WHERE id = :id AND ngay_xoa IS NULL");
        $stmtCheck->execute(['id' => $donHangId]);
        $order = $stmtCheck->fetch();

        if (!$order) {
            flash('error', 'Không tìm thấy đơn hàng cần xác nhận thanh toán.');
            redirect(url('admin/orders'));
            return;
        }

        if ($order['trang_thai_don_hang'] === 'da_huy') {
            flash('error', 'Không thể xác nhận thanh toán cho đơn hàng đã bị hủy.');
            redirect(url('admin/orders'));
            return;
        }

        if ($order['trang_thai_thanh_toan'] === 'da_thanh_toan') {
            flash('warning', 'Đơn hàng #' . $order['ma_don_hang'] . ' đã được thanh toán từ trước.');
            redirect(url('admin/orders'));
            return;
        }

        try {
            $db->beginTransaction();

            // Cập nhật trạng thái thanh toán sang da_thanh_toan, trạng thái đơn sang da_xac_nhan nếu đang là moi_tao hoặc cho_xac_nhan
            $trangThaiDonMoi = in_array($order['trang_thai_don_hang'], ['moi_tao', 'cho_xac_nhan']) ? 'da_xac_nhan' : $order['trang_thai_don_hang'];
            $stmtUpdate = $db->prepare("
                UPDATE don_hang 
                SET trang_thai_thanh_toan = 'da_thanh_toan',
                    trang_thai_don_hang = :trang_thai_don 
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                'trang_thai_don' => $trangThaiDonMoi,
                'id'             => $donHangId
            ]);

            // Thêm bản ghi giao dịch thanh toán
            $maGiaoDich = 'ADM' . date('YmdHis') . rand(100, 999);
            $stmtTx = $db->prepare("
                INSERT INTO giao_dich_thanh_toan 
                (don_hang_id, cong_thanh_toan, ma_giao_dich, so_tien, loai_tien_te, du_lieu_phan_hoi, trang_thai) 
                VALUES 
                (:don_hang_id, :cong, :ma_gd, :so_tien, 'VND', :phan_hoi, 'thanh_cong')
            ");
            $stmtTx->execute([
                'don_hang_id' => $donHangId,
                'cong'        => $order['phuong_thuc_thanh_toan'],
                'ma_gd'       => $maGiaoDich,
                'so_tien'     => $order['tong_tien'],
                'phan_hoi'    => json_encode([
                    'hinh_thuc'       => 'Admin quầy tiếp đón đối soát & xác nhận nhận tiền',
                    'thoi_gian'       => date('Y-m-d H:i:s'),
                    'nguoi_duyet_id'  => $_SESSION['user_id'] ?? 0,
                    'nguoi_duyet_ten' => $_SESSION['user_name'] ?? 'Admin'
                ], JSON_UNESCAPED_UNICODE)
            ]);

            $db->commit();

            // Đảm bảo mã QR E-Voucher đã được tạo
            \App\Helpers\QrHelper::taoQrDonHang($order['ma_don_hang']);

            // Gửi email xác nhận thanh toán kèm vé điện tử cho khách
            $donHangModel = new \App\Models\DonHang();
            $chiTiet = $donHangModel->chiTiet($donHangId);
            $order['trang_thai_thanh_toan'] = 'da_thanh_toan';
            $order['trang_thai_don_hang'] = $trangThaiDonMoi;
            \App\Helpers\MailHelper::guiEmailXacNhan($order, $chiTiet);

            flash('success', 'Xác nhận thanh toán thành công cho đơn hàng #' . $order['ma_don_hang'] . '! Hệ thống đã kích hoạt E-Voucher và gửi email vé cho khách.');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            flash('error', 'Lỗi khi xác nhận thanh toán: ' . $e->getMessage());
        }

        redirect(url('admin/orders'));
    }
}

