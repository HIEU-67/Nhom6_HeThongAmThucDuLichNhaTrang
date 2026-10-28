<?php
/**
 * @var string $title
 * @var array $orders
 * @var string $tuKhoa
 * @var string $trangThaiDon
 * @var string $trangThaiTt
 * @var int|null $page
 * @var int|null $perPage
 * @var int|null $totalOrders
 */
require_once dirname(__DIR__, 2) . '/layouts/admin_layout.php';
?>

<!-- Bộ Lọc & Tra Cứu Quét Mã E-Voucher -->
<div class="card border-0 shadow-sm p-4 mb-4 rounded-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-qr-code-scan me-2 text-primary"></i>Soát Vé & Tra Cứu Đơn Hàng Tại Quầy Tiếp Đón
        </h5>
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">Quầy Lễ Tân / Front-Desk</span>
    </div>

    <form action="<?= url('admin/orders') ?>" method="GET" class="row g-3">
        <!-- Ô tìm kiếm từ khóa hoặc quét mã QR -->
        <div class="col-lg-5">
            <label class="form-label small fw-semibold text-muted">Mã Đơn / Mã QR / Số điện thoại / Tên khách</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Quét hoặc nhập: NT26... / 0901234567" value="<?= htmlspecialchars($tuKhoa ?? '') ?>" autofocus>
            </div>
        </div>

        <!-- Lọc theo trạng thái đơn -->
        <div class="col-sm-6 col-lg-3">
            <label class="form-label small fw-semibold text-muted">Trạng thái vé</label>
            <select name="trang_thai_don" class="form-select">
                <option value="">-- Tất cả trạng thái đơn --</option>
                <option value="cho_xac_nhan" <?= ($trangThaiDon ?? '') === 'cho_xac_nhan' ? 'selected' : '' ?>>Chờ xác nhận</option>
                <option value="moi_tao" <?= ($trangThaiDon ?? '') === 'moi_tao' ? 'selected' : '' ?>>Mới tạo</option>
                <option value="da_xac_nhan" <?= ($trangThaiDon ?? '') === 'da_xac_nhan' ? 'selected' : '' ?>>Đã xác nhận (Hợp lệ)</option>
                <option value="hoan_thanh" <?= ($trangThaiDon ?? '') === 'hoan_thanh' ? 'selected' : '' ?>>Đã soát vé (Hoàn thành)</option>
                <option value="da_huy" <?= ($trangThaiDon ?? '') === 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
            </select>
        </div>

        <!-- Lọc theo trạng thái thanh toán -->
        <div class="col-sm-6 col-lg-2">
            <label class="form-label small fw-semibold text-muted">Thanh toán</label>
            <select name="trang_thai_tt" class="form-select">
                <option value="">-- Tất cả --</option>
                <option value="da_thanh_toan" <?= ($trangThaiTt ?? '') === 'da_thanh_toan' ? 'selected' : '' ?>>Đã thanh toán</option>
                <option value="cho_xac_nhan" <?= ($trangThaiTt ?? '') === 'cho_xac_nhan' ? 'selected' : '' ?>>Chờ Admin xác nhận</option>
                <option value="cho_thanh_toan" <?= ($trangThaiTt ?? '') === 'cho_thanh_toan' ? 'selected' : '' ?>>Chờ thanh toán</option>
            </select>
        </div>

        <!-- Nút tìm kiếm & Đặt lại -->
        <div class="col-lg-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary w-100 fw-semibold rounded-pill">
                <i class="bi bi-funnel me-1"></i>Lọc
            </button>
            <?php if (!empty($tuKhoa) || !empty($trangThaiDon) || !empty($trangThaiTt)): ?>
                <a href="<?= url('admin/orders') ?>" class="btn btn-outline-secondary rounded-pill" title="Đặt lại bộ lọc">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Danh Sách Đơn Hàng -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-list-check me-2 text-secondary"></i>Danh Sách Đơn Đặt Vé (<?= number_format($totalOrders ?? count($orders)) ?> đơn)
        </h5>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Mã Đơn Hàng</th>
                    <th>Khách Hàng</th>
                    <th>Số Điện Thoại</th>
                    <th>Tổng Tiền</th>
                    <th>Thanh Toán</th>
                    <th>Trạng Thái Vé</th>
                    <th>Ngày Đặt</th>
                    <th class="text-end">Thao Tác Soát Vé</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            Không tìm thấy đơn hàng nào phù hợp với điều kiện tìm kiếm.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <strong class="text-primary fs-6"><?= htmlspecialchars($o['ma_don_hang']) ?></strong>
                                <span class="badge bg-light text-secondary border d-block mt-1" style="width: fit-content; font-size: 11px;">
                                    <?= strtoupper($o['phuong_thuc_thanh_toan']) ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($o['ten_khach_hang']) ?></strong><br>
                                <span class="text-muted small"><?= htmlspecialchars($o['email_khach_hang']) ?></span>
                            </td>
                            <td>
                                <a href="tel:<?= htmlspecialchars($o['sdt_khach_hang']) ?>" class="text-decoration-none fw-semibold text-dark">
                                    <i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($o['sdt_khach_hang']) ?>
                                </a>
                            </td>
                            <td class="fw-bold text-danger"><?= \App\Helpers\FormatHelper::tienTe((float)$o['tong_tien']) ?></td>
                            <?php 
                                $isPaid = ($o['trang_thai_thanh_toan'] === 'da_thanh_toan');
                                $isWaitVerify = ($o['trang_thai_thanh_toan'] === 'cho_xac_nhan' || $o['trang_thai_don_hang'] === 'cho_xac_nhan');
                            ?>
                            <td>
                                <?php if ($isPaid): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Đã thanh toán</span>
                                <?php elseif ($isWaitVerify): ?>
                                    <span class="badge bg-warning text-dark border border-warning shadow-xs">
                                        <i class="bi bi-bell-fill text-danger me-1"></i>Khách báo đã chuyển
                                    </span>
                                    <?php if ($o['trang_thai_don_hang'] !== 'da_huy'): ?>
                                        <form action="<?= url('admin/orders/confirm-payment') ?>" method="POST" class="mt-1" onsubmit="return confirm('Xác nhận bạn đã kiểm tra tài khoản và nhận đủ tiền <?= \App\Helpers\FormatHelper::tienTe((float)$o['tong_tien']) ?> cho đơn hàng <?= htmlspecialchars($o['ma_don_hang']) ?>?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="don_hang_id" value="<?= (int)$o['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success py-0.5 px-2.5 rounded-pill fw-bold shadow-xs d-block mt-1" style="font-size: 11px;" title="Xác nhận đối soát nhận tiền">
                                                <i class="bi bi-cash-coin me-1"></i>Xác nhận tiền
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-light text-secondary border">
                                        <i class="bi bi-clock me-1"></i>Chờ thanh toán
                                    </span>
                                    <?php if ($o['trang_thai_don_hang'] !== 'da_huy' && $o['phuong_thuc_thanh_toan'] !== 'tai_quay'): ?>
                                        <form action="<?= url('admin/orders/confirm-payment') ?>" method="POST" class="mt-1" onsubmit="return confirm('Xác nhận bạn đã kiểm tra tài khoản và nhận đủ tiền <?= \App\Helpers\FormatHelper::tienTe((float)$o['tong_tien']) ?> cho đơn hàng <?= htmlspecialchars($o['ma_don_hang']) ?>?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="don_hang_id" value="<?= (int)$o['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-semibold shadow-xs d-block mt-1" style="font-size: 10.5px;" title="Xác nhận đã nhận tiền">
                                                <i class="bi bi-cash-coin me-1"></i>Xác nhận nhận tiền
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($o['trang_thai_don_hang'] === 'hoan_thanh'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Đã nhận vé</span>
                                <?php elseif ($o['trang_thai_don_hang'] === 'da_xac_nhan'): ?>
                                    <span class="badge bg-info text-dark"><i class="bi bi-shield-check me-1"></i>Hợp lệ (Chờ soát)</span>
                                <?php elseif ($isWaitVerify): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning"><i class="bi bi-hourglass-split me-1"></i>Chờ xác nhận</span>
                                <?php elseif ($o['trang_thai_don_hang'] === 'da_huy'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($o['trang_thai_don_hang']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= \App\Helpers\FormatHelper::ngayThang($o['ngay_tao']) ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <a href="<?= url('voucher/' . $o['ma_don_hang']) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-2.5" title="Xem chi tiết E-Voucher">
                                        <i class="bi bi-eye me-1"></i>Phiếu
                                    </a>
                                    
                                    <?php if ($o['trang_thai_don_hang'] !== 'hoan_thanh' && $o['trang_thai_don_hang'] !== 'da_huy'): ?>
                                        <?php if ($o['phuong_thuc_thanh_toan'] !== 'tai_quay' && !$isPaid): ?>
                                            <!-- Vé online chưa thanh toán: KHÔNG cho đổi vé, hướng dẫn xác nhận tiền -->
                                            <form action="<?= url('admin/orders/confirm-payment') ?>" method="POST" onsubmit="return confirm('Đơn hàng này khách đã báo chuyển khoản. Xác nhận bạn đã nhận đủ số tiền <?= \App\Helpers\FormatHelper::tienTe((float)$o['tong_tien']) ?> để kích hoạt vé?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="don_hang_id" value="<?= (int)$o['id'] ?>">
                                                <button type="submit" class="btn btn-sm <?= $isWaitVerify ? 'btn-success' : 'btn-warning text-dark' ?> rounded-pill px-3 fw-bold shadow-xs" title="Xác nhận tiền trước khi đổi vé">
                                                    <i class="bi bi-cash-coin me-1"></i>Xác nhận tiền
                                                </button>
                                            </form>
                                        <?php elseif ($o['phuong_thuc_thanh_toan'] === 'tai_quay' && !$isPaid): ?>
                                            <!-- Vé thanh toán tại quầy: Admin thu tiền mặt và đổi vé cùng lúc -->
                                            <form action="<?= url('admin/orders/update-status') ?>" method="POST" onsubmit="return confirm('Xác nhận ĐÃ THU TIỀN MẶT <?= \App\Helpers\FormatHelper::tienTe((float)$o['tong_tien']) ?> tại quầy và trao vé vào cổng cho khách hàng <?= htmlspecialchars($o['ten_khach_hang']) ?>?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="don_hang_id" value="<?= (int)$o['id'] ?>">
                                                <input type="hidden" name="trang_thai" value="hoan_thanh">
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-xs">
                                                    <i class="bi bi-cash me-1"></i>Thu tiền & Đổi vé
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Đã thanh toán đầy đủ: Đổi vé vào cổng bình thường -->
                                            <form action="<?= url('admin/orders/update-status') ?>" method="POST" onsubmit="return confirm('Xác nhận đã kiểm tra E-Voucher hợp lệ và trao vé vào cổng cho khách hàng <?= htmlspecialchars($o['ten_khach_hang']) ?>?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="don_hang_id" value="<?= (int)$o['id'] ?>">
                                                <input type="hidden" name="trang_thai" value="hoan_thanh">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold shadow-xs">
                                                    <i class="bi bi-check2-circle me-1"></i>Đổi Vé
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Phân Trang Dữ Liệu -->
    <div class="pb-3">
        <?= phan_trang($totalOrders ?? count($orders), $perPage ?? 10, $page ?? 1, 'admin/orders', $_GET) ?>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin_footer.php'; ?>
