<?php
/**
 * View Hộ Chiếu Du Lịch Nha Trang (Digital Travel Passport)
 * @var string $title
 * @var array $passport
 * @var array $history
 */
require_once dirname(__DIR__, 2) . '/layouts/header.php';
require_once dirname(__DIR__, 2) . '/layouts/navbar.php';

$user = $_SESSION['user_name'] ?? 'Du Khách';
$email = $_SESSION['user_email'] ?? '';
?>

<div class="py-4 bg-light" style="min-height: 85vh;">
    <div class="container py-2">
        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= url('') ?>" class="text-decoration-none">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= url('locations') ?>" class="text-decoration-none">Địa điểm</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Hộ Chiếu Du Lịch Số</li>
            </ol>
        </nav>

        <!-- BANNER HỘ CHIẾU BÌA DA NAVY PHONG CÁCH PASSPORT SANG TRỌNG -->
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0c4a6e 100%); color: #f8fafc;">
            <div class="card-body p-4 p-md-5 position-relative">
                <!-- Họa tiết quốc huy / hoa văn chìm -->
                <div class="position-absolute end-0 top-50 translate-middle-y opacity-10 d-none d-md-block pe-4" style="pointer-events: none;">
                    <i class="bi bi-compass" style="font-size: 15rem; color: #38bdf8;"></i>
                </div>

                <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background-color: <?= htmlspecialchars($passport['badge_color'] ?? '#eab308') ?>; color: #000;">
                                <i class="bi bi-patch-check-fill me-1"></i>Hạng <?= htmlspecialchars($passport['hang'] ?? 'Đồng') ?>
                            </span>
                            <span class="badge bg-white-subtle text-light border border-white-subtle rounded-pill px-3 py-1.5 small">
                                <i class="bi bi-star-fill text-warning me-1"></i><?= number_format($passport['diem_tich_luy'] ?? 0) ?> Điểm tích lũy
                            </span>
                        </div>

                        <h1 class="h2 fw-bolder mb-1 text-white">
                            Hộ Chiếu Du Lịch Nha Trang
                        </h1>
                        <p class="text-white-50 mb-3 fs-6">
                            Chủ hộ chiếu: <strong class="text-white"><?= htmlspecialchars($user) ?></strong> &bull; Danh hiệu: <span class="text-warning fw-bold"><?= htmlspecialchars($passport['danh_hieu'] ?? 'Tân Binh Khám Phá') ?></span>
                        </p>

                        <!-- Thanh Tiến Độ Lên Cấp -->
                        <div class="mb-3" style="max-width: 550px;">
                            <div class="d-flex justify-content-between text-white-50 small mb-1">
                                <span>Tiến độ cấp bậc: <strong><?= $passport['hang'] ?></strong></span>
                                <span><?= $passport['progress_percent'] ?>%</span>
                            </div>
                            <div class="progress" style="height: 10px; background-color: rgba(255,255,255,0.15); border-radius: 999px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: <?= $passport['progress_percent'] ?>%"></div>
                            </div>
                            <div class="small text-white-50 mt-1" style="font-size: 12px;">
                                <i class="bi bi-info-circle me-1 text-info"></i><?= htmlspecialchars($passport['next_level_text'] ?? '') ?>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 pt-2">
                            <a href="<?= url('locations') ?>" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                                <i class="bi bi-geo-alt-fill me-1"></i>Tiếp tục Check-in trên Bản đồ (+50đ)
                            </a>
                        </div>
                    </div>

                    <!-- Ô Tóm Tắt Con Số -->
                    <div class="col-lg-4">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-3 rounded-3 text-center" style="background: rgba(255,255,255,0.07); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.1);">
                                    <div class="h2 fw-bold text-warning mb-0"><?= (int)($passport['tong_checkin'] ?? 0) ?></div>
                                    <small class="text-white-50">Lần Check-in</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 text-center" style="background: rgba(255,255,255,0.07); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.1);">
                                    <div class="h2 fw-bold text-info mb-0"><?= (int)($passport['so_diem_khac_nhau'] ?? 0) ?></div>
                                    <small class="text-white-50">Địa Điểm Đã Ghé</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-2.5 rounded-3 text-center" style="background: rgba(255,255,255,0.07); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.1);">
                                    <span class="text-white-50 small me-2">Mã Hộ Chiếu:</span>
                                    <span class="font-monospace fw-bold text-light">NTR-<?= str_pad((string)($_SESSION['user_id'] ?? '1'), 6, '0', STR_PAD_LEFT) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- CỘT TRÁI: BỘ SƯU TẬP 5 HUY HIỆU DANH GIÁ -->
            <div class="col-lg-5">
                <div class="card border-0 rounded-4 shadow-sm h-100 bg-white">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-award-fill text-warning"></i>Huy Hiệu Khám Phá
                            </h3>
                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small">
                                5 Huy hiệu
                            </span>
                        </div>
                        <p class="text-muted small mb-0">Mở khóa huy hiệu khi check-in các điểm đến đặc trưng</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($passport['huy_hieu_list'] as $hh): ?>
                                <div class="p-3 rounded-3 border d-flex align-items-center gap-3 <?= $hh['da_mo_khoa'] ? 'bg-light shadow-2xs' : 'opacity-60 bg-white' ?>" style="<?= $hh['da_mo_khoa'] ? 'border-left: 4px solid ' . $hh['mau'] . ' !important;' : '' ?>">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                         style="width: 52px; height: 52px; background: <?= $hh['da_mo_khoa'] ? $hh['mau'] : '#e2e8f0' ?>; color: <?= $hh['da_mo_khoa'] ? '#fff' : '#94a3b8' ?>; font-size: 1.5rem; box-shadow: <?= $hh['da_mo_khoa'] ? '0 4px 10px rgba(0,0,0,0.15)' : 'none' ?>;">
                                        <i class="bi <?= htmlspecialchars($hh['icon']) ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h4 class="h6 fw-bold mb-0 <?= $hh['da_mo_khoa'] ? 'text-dark' : 'text-muted' ?>">
                                                <?= htmlspecialchars($hh['ten']) ?>
                                            </h4>
                                            <?php if ($hh['da_mo_khoa']): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small" style="font-size: 11px;">
                                                    <i class="bi bi-check2-circle me-1"></i>Đã đạt
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill small" style="font-size: 11px;">
                                                    <i class="bi bi-lock-fill me-1"></i>Chưa mở
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="small text-muted mb-1" style="font-size: 12.5px;"><?= htmlspecialchars($hh['mo_ta']) ?></p>
                                        <small class="text-primary fw-semibold" style="font-size: 11.5px;">
                                            <i class="bi bi-flag me-1"></i>Yêu cầu: <?= htmlspecialchars($hh['yeu_cau']) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: BỘ SƯU TẬP CON DẤU MỘC ĐỎ (VISA STAMPS GALLERY) -->
            <div class="col-lg-7">
                <div class="card border-0 rounded-4 shadow-sm h-100 bg-white">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-journal-bookmark-fill text-danger"></i>Trang Đóng Dấu Du Lịch (Visa Stamps)
                            </h3>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small">
                                <?= count($history) ?> Dấu mộc
                            </span>
                        </div>
                        <p class="text-muted small mb-0">Lưu giữ dấu ấn thực tế của bạn tại các danh lam, thắng cảnh Nha Trang</p>
                    </div>

                    <div class="card-body px-4 pb-4">
                        <?php if (empty($history)): ?>
                            <div class="text-center py-5">
                                <div class="rounded-circle bg-light d-inline-flex p-4 mb-3 border">
                                    <i class="bi bi-geo-alt fs-1 text-muted"></i>
                                </div>
                                <h4 class="h6 fw-bold text-dark mb-1">Chưa có con dấu check-in nào!</h4>
                                <p class="text-muted small mb-3">Hãy ghé thăm trang chi tiết các địa điểm và bấm nút "Check-in GPS" để nhận con dấu mộc đầu tiên và +50 điểm thưởng.</p>
                                <a href="<?= url('locations') ?>" class="btn btn-primary btn-sm rounded-pill px-4">
                                    <i class="bi bi-map me-1"></i>Khám phá địa điểm ngay
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($history as $h): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-4 border position-relative overflow-hidden h-100 bg-light-subtle d-flex flex-column justify-content-between" style="border-style: dashed !important; border-width: 2px !important;">
                                            <!-- Hiệu ứng con dấu mộc tròn đỏ phong cách Xuất nhập cảnh -->
                                            <div class="position-absolute end-0 bottom-0 p-2 opacity-15" style="pointer-events: none; transform: rotate(-15deg);">
                                                <div style="width: 80px; height: 80px; border: 3px double #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #dc2626; font-weight: bold; font-size: 10px; text-align: center; text-transform: uppercase;">
                                                    NHA TRANG<br>VERIFIED
                                                </div>
                                            </div>

                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small" style="font-size: 11px;">
                                                        <?= htmlspecialchars($h['ten_danh_muc']) ?>
                                                    </span>
                                                    <span class="badge bg-success-subtle text-success rounded-pill fw-bold small">
                                                        +<?= (int)$h['diem_thuong'] ?> điểm
                                                    </span>
                                                </div>

                                                <h4 class="h6 fw-bold text-dark mb-1">
                                                    <a href="<?= url('dia-diem/' . $h['dia_diem_slug']) ?>" class="text-dark text-decoration-none">
                                                        <?= htmlspecialchars($h['ten_dia_diem']) ?>
                                                    </a>
                                                </h4>
                                                <p class="text-muted small line-clamp-1 mb-2" style="font-size: 12px;">
                                                    <i class="bi bi-geo-alt me-1 text-danger"></i><?= htmlspecialchars($h['dia_chi']) ?>
                                                </p>
                                            </div>

                                            <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center text-muted small" style="font-size: 11.5px;">
                                                <span>
                                                    <i class="bi bi-calendar-check me-1 text-primary"></i>
                                                    <?= date('d/m/Y H:i', strtotime($h['ngay_check_in'])) ?>
                                                </span>
                                                <?php if (!empty($h['khoang_cach_m'])): ?>
                                                    <span class="badge bg-light text-secondary border">
                                                        Cách <?= $h['khoang_cach_m'] < 1000 ? $h['khoang_cach_m'] . 'm' : round($h['khoang_cach_m'] / 1000, 1) . 'km' ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/layouts/footer.php'; ?>

