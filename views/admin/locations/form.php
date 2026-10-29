<?php
/**
 * @var string $title
 * @var string $mode
 * @var array|null $location
 * @var array $categories
 * @var array $gallery
 * @var array $errors
 */
require_once dirname(__DIR__, 2) . '/layouts/admin_layout.php';

$isEdit = ($mode === 'edit');
$actionUrl = $isEdit ? url('admin/locations/edit/' . $location['id']) : url('admin/locations/create');
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi <?= $isEdit ? 'bi-pencil-square' : 'bi-plus-circle' ?> me-2 text-primary"></i>
            <?= $isEdit ? 'Chỉnh Sửa Địa Điểm: ' . htmlspecialchars($location['ten_dia_diem']) : 'Thêm Địa Điểm Mới' ?>
        </h4>
        <p class="text-muted small mb-0">Cung cấp đầy đủ thông tin vị trí tọa độ GPS và hình ảnh để hiển thị trên bản đồ tương tác</p>
    </div>
    <a href="<?= url('admin/locations') ?>" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
    </a>
</div>

<form action="<?= $actionUrl ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <!-- Cột Thông Tin Cơ Bản (Bên trái) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Thông Tin Chung</h5>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Tên địa điểm <span class="text-danger">*</span></label>
                        <input type="text" name="ten_dia_diem" class="form-control" required 
                               value="<?= htmlspecialchars($location['ten_dia_diem'] ?? '') ?>" 
                               placeholder="VD: VinWonders Nha Trang, Nem Nướng Đặng Văn Quyên...">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Danh mục <span class="text-danger">*</span></label>
                        <select name="danh_muc_id" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (($location['danh_muc_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                                    [<?= $cat['loai_danh_muc'] === 'am_thuc' ? 'Ẩm thực' : 'Du lịch' ?>] <?= htmlspecialchars($cat['ten_danh_muc']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($isEdit): ?>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Đường dẫn tĩnh (Slug URL)</label>
                            <input type="text" name="duong_dan" class="form-control" 
                                   value="<?= htmlspecialchars($location['duong_dan'] ?? '') ?>">
                            <div class="form-text small">Để trống nếu muốn hệ thống tự động sinh từ tên địa điểm.</div>
                        </div>
                    <?php endif; ?>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                        <input type="text" name="dia_chi" class="form-control" required 
                               value="<?= htmlspecialchars($location['dia_chi'] ?? '') ?>" 
                               placeholder="VD: Đảo Hòn Tre, Vĩnh Nguyên, Nha Trang, Khánh Hòa">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Vĩ độ GPS (Latitude) <span class="text-danger">*</span></label>
                        <input type="number" step="0.00000001" name="vi_do" class="form-control" required 
                               value="<?= htmlspecialchars($location['vi_do'] ?? '12.247500') ?>" placeholder="12.2xxxx">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Kinh độ GPS (Longitude) <span class="text-danger">*</span></label>
                        <input type="number" step="0.00000001" name="kinh_do" class="form-control" required 
                               value="<?= htmlspecialchars($location['kinh_do'] ?? '109.196700') ?>" placeholder="109.1xxxx">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Khoảng giá thấp nhất (VNĐ)</label>
                        <input type="number" step="1000" name="gia_thap_nhat" class="form-control" 
                               value="<?= htmlspecialchars($location['gia_thap_nhat'] ?? 0) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Khoảng giá cao nhất (VNĐ)</label>
                        <input type="number" step="1000" name="gia_cao_nhat" class="form-control" 
                               value="<?= htmlspecialchars($location['gia_cao_nhat'] ?? 0) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Giờ mở cửa</label>
                        <input type="time" name="gio_mo_cua" class="form-control" 
                               value="<?= htmlspecialchars($location['gio_mo_cua'] ?? '08:00') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Giờ đóng cửa</label>
                        <input type="time" name="gio_dong_cua" class="form-control" 
                               value="<?= htmlspecialchars($location['gio_dong_cua'] ?? '21:00') ?>">
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Nội Dung Giới Thiệu</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Mô tả ngắn (Hiển thị ở danh sách & popup bản đồ)</label>
                    <textarea name="mo_ta_ngan" rows="3" class="form-control" 
                              placeholder="Tóm tắt điểm đặc sắc nhất..."><?= htmlspecialchars($location['mo_ta_ngan'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Nội dung chi tiết (Bài viết review chi tiết)</label>
                    <textarea name="noi_dung_chi_tiet" rows="8" class="form-control" 
                              placeholder="Chi tiết kinh nghiệm, lịch sử, menu món ăn, hướng dẫn tham quan..."><?= htmlspecialchars($location['noi_dung_chi_tiet'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Cột Ảnh & Cài Đặt (Bên phải) -->
        <div class="col-lg-4">
            <!-- Cài đặt trạng thái -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">Cài Đặt Dịch Vụ</h6>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="coBanVe" name="co_ban_ve" value="1" 
                           <?= !empty($location['co_ban_ve']) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold small" for="coBanVe">
                        Kích hoạt bán vé E-Voucher trực tuyến
                    </label>
                    <div class="form-text small">Nếu bật, địa điểm sẽ xuất hiện nút "Đặt Vé Ngay" và có thể gắn các Gói vé.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Trạng thái hiển thị</label>
                    <select name="trang_thai" class="form-select">
                        <option value="hien_thi" <?= (($location['trang_thai'] ?? '') === 'hien_thi') ? 'selected' : '' ?>>Hiển thị công khai</option>
                        <option value="an" <?= (($location['trang_thai'] ?? '') === 'an') ? 'selected' : '' ?>>Tạm ẩn</option>
                    </select>
                </div>
            </div>

            <!-- Ảnh đại diện -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">Ảnh Đại Diện</h6>

                <?php if ($isEdit && !empty($location['anh_dai_dien'])): ?>
                    <div class="mb-3 text-center position-relative" id="avatarPreviewBox">
                        <div class="position-relative d-inline-block">
                            <img src="<?= htmlspecialchars(hinh_anh($location['anh_dai_dien'])) ?>" 
                                 alt="Ảnh hiện tại" id="currentAvatarImg" class="img-fluid rounded-3 shadow-sm border" style="max-height: 180px; object-fit: cover; transition: all 0.3s ease;">
                            <div id="avatarDeletedBadge" class="position-absolute top-50 start-50 translate-middle badge bg-danger fs-6 p-2 shadow d-none" style="white-space: nowrap;">
                                <i class="bi bi-trash me-1"></i>Sẽ xóa khi bấm Lưu
                            </div>
                            <button type="button" class="btn btn-danger btn-sm rounded-pill position-absolute bottom-0 end-0 m-2 shadow-sm" id="btnToggleDeleteAvatar" onclick="toggleAvatarDeletion()">
                                <i class="bi bi-trash3 me-1"></i>Xóa ảnh đại diện
                            </button>
                        </div>
                        <input type="hidden" name="xoa_anh_dai_dien" id="inputXoaAvatar" value="0">
                        <div class="small text-muted mt-1" id="avatarStatusText">Ảnh đại diện hiện tại</div>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tải lên file ảnh mới</label>
                    <input type="file" name="anh_dai_dien" class="form-control" accept="image/*">
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Hoặc nhập URL ảnh trực tiếp</label>
                    <input type="url" name="anh_dai_dien_url" class="form-control" placeholder="https://..." 
                           value="<?= ($isEdit && str_starts_with($location['anh_dai_dien'] ?? '', 'http')) ? htmlspecialchars($location['anh_dai_dien']) : '' ?>">
                </div>
            </div>

            <!-- Thư viện ảnh phụ (Gallery) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                    <h6 class="fw-bold text-dark mb-0">Thư Viện Ảnh (Gallery)</h6>
                    <?php if ($isEdit && !empty($gallery)): ?>
                        <span class="badge bg-primary-subtle text-primary rounded-pill small" id="existingCountBadge">
                            <?= count($gallery) ?> ảnh đã lưu
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($isEdit && !empty($gallery)): ?>
                    <div class="row g-2 mb-3" id="existingGalleryList">
                        <?php foreach ($gallery as $img): ?>
                            <div class="col-4 position-relative gallery-item-wrap" id="gallery-item-<?= (int)$img['id'] ?>">
                                <div class="ratio ratio-16x9 rounded-3 overflow-hidden border shadow-2xs">
                                    <img src="<?= htmlspecialchars(hinh_anh($img['duong_dan_anh'])) ?>" 
                                         alt="Gallery" class="w-100 h-100 object-fit-cover">
                                </div>
                                <button type="button" 
                                        class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center shadow" 
                                        style="width: 26px; height: 26px; font-size: 12px; z-index: 10;"
                                        onclick="deleteExistingGalleryImage(<?= (int)$img['id'] ?>, <?= (int)$location['id'] ?>, this)" 
                                        title="Xóa ảnh này khỏi thư viện">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="form-label small fw-semibold">Tải thêm nhiều ảnh vào thư viện</label>
                    <input type="file" name="gallery_images[]" id="galleryFileInput" class="form-control" accept="image/*" multiple onchange="handleNewGallerySelect(this)">
                    <div class="form-text small">Có thể chọn nhiều file ảnh cùng lúc (JPG, PNG, WebP).</div>
                </div>

                <!-- Preview container cho các ảnh VỪA CHỌN THÊM (có nút xóa từng ảnh trước khi bấm Lưu) -->
                <div id="newGalleryPreviewBox" class="mt-3 p-3 bg-light rounded-3 border d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-primary">
                            <i class="bi bi-images me-1"></i>Ảnh vừa chọn thêm (<span id="newFilesCount">0</span> file):
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11.5px;" onclick="clearAllNewGalleryFiles()">
                            <i class="bi bi-trash me-1"></i>Hủy tất cả
                        </button>
                    </div>
                    <div class="row g-2" id="newGalleryPreviewList"></div>
                </div>
            </div>

            <!-- Nút bấm lưu -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary py-3 fw-bold rounded-pill shadow-sm fs-6">
                    <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Lưu Thay Đổi' : 'Thêm Địa Điểm' ?>
                </button>
                <a href="<?= url('admin/locations') ?>" class="btn btn-outline-secondary py-2 rounded-pill">Hủy bỏ</a>
            </div>
        </div>
    </div>
</form>

<script>
// 1. Quản lý danh sách file mới chọn (hỗ trợ xóa từng file vừa chọn)
let newGalleryFilesList = [];

function handleNewGallerySelect(input) {
    if (!input.files || input.files.length === 0) return;

    // Gộp các file mới chọn vào danh sách hiện tại
    const files = Array.from(input.files);
    newGalleryFilesList = newGalleryFilesList.concat(files);
    syncFileInputAndRender();
}

function removeNewGalleryFile(index) {
    if (index >= 0 && index < newGalleryFilesList.length) {
        newGalleryFilesList.splice(index, 1);
        syncFileInputAndRender();
    }
}

function clearAllNewGalleryFiles() {
    newGalleryFilesList = [];
    syncFileInputAndRender();
}

function syncFileInputAndRender() {
    const fileInput = document.getElementById('galleryFileInput');
    const previewBox = document.getElementById('newGalleryPreviewBox');
    const previewList = document.getElementById('newGalleryPreviewList');
    const countSpan = document.getElementById('newFilesCount');

    // Đồng bộ lại đối tượng FileList của input bằng DataTransfer API
    const dt = new DataTransfer();
    newGalleryFilesList.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;

    if (newGalleryFilesList.length === 0) {
        previewBox.classList.add('d-none');
        previewList.innerHTML = '';
        fileInput.value = '';
        return;
    }

    countSpan.textContent = newGalleryFilesList.length;
    previewBox.classList.remove('d-none');
    previewList.innerHTML = '';

    newGalleryFilesList.forEach((file, idx) => {
        const objectUrl = URL.createObjectURL(file);
        const col = document.createElement('div');
        col.className = 'col-4 position-relative';
        col.innerHTML = `
            <div class="ratio ratio-16x9 rounded-3 overflow-hidden border shadow-2xs">
                <img src="${objectUrl}" class="w-100 h-100 object-fit-cover" alt="Preview">
            </div>
            <button type="button" 
                    class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center shadow" 
                    style="width: 24px; height: 24px; font-size: 11px; z-index: 10;" 
                    onclick="removeNewGalleryFile(${idx})" 
                    title="Xóa ảnh này khỏi danh sách tải lên">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="text-truncate small text-muted text-center mt-1" style="font-size: 10px;" title="${file.name}">
                ${file.name}
            </div>
        `;
        previewList.appendChild(col);
    });
}

// 2. Xóa ảnh đã lưu trong thư viện (gọi API AJAX xóa tức thì khỏi CSDL)
function deleteExistingGalleryImage(imageId, locationId, btn) {
    if (!confirm('Bạn có chắc chắn muốn xóa ảnh này khỏi thư viện?')) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 12px; height: 12px;"></span>';

    const formData = new FormData();
    formData.append('image_id', imageId);
    formData.append('location_id', locationId);
    formData.append('csrf_token', '<?= csrf_token() ?>');

    fetch('<?= url('admin/locations/delete-image') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const item = document.getElementById('gallery-item-' + imageId);
            if (item) {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '0';
                item.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    item.remove();
                    const list = document.getElementById('existingGalleryList');
                    const badge = document.getElementById('existingCountBadge');
                    if (list) {
                        const count = list.querySelectorAll('.gallery-item-wrap').length;
                        if (badge) badge.textContent = count + ' ảnh đã lưu';
                        if (count === 0) list.remove();
                    }
                }, 300);
            }
        } else {
            alert(data.message || 'Lỗi khi xóa ảnh');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash3-fill"></i>';
        }
    })
    .catch(err => {
        alert('Lỗi kết nối khi xóa ảnh.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash3-fill"></i>';
    });
}

// 3. Đánh dấu xóa ảnh đại diện hiện tại
let isAvatarMarkedDeleted = false;
function toggleAvatarDeletion() {
    const input = document.getElementById('inputXoaAvatar');
    const img = document.getElementById('currentAvatarImg');
    const badge = document.getElementById('avatarDeletedBadge');
    const btn = document.getElementById('btnToggleDeleteAvatar');
    const statusText = document.getElementById('avatarStatusText');

    isAvatarMarkedDeleted = !isAvatarMarkedDeleted;
    if (isAvatarMarkedDeleted) {
        input.value = '1';
        img.style.opacity = '0.35';
        img.style.filter = 'grayscale(100%)';
        badge.classList.remove('d-none');
        btn.className = 'btn btn-secondary btn-sm rounded-pill position-absolute bottom-0 end-0 m-2 shadow-sm';
        btn.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i>Hủy xóa';
        statusText.textContent = 'Ảnh sẽ bị xóa khi bạn bấm "Lưu Thay Đổi"';
        statusText.classList.add('text-danger');
    } else {
        input.value = '0';
        img.style.opacity = '1';
        img.style.filter = 'none';
        badge.classList.add('d-none');
        btn.className = 'btn btn-danger btn-sm rounded-pill position-absolute bottom-0 end-0 m-2 shadow-sm';
        btn.innerHTML = '<i class="bi bi-trash3 me-1"></i>Xóa ảnh đại diện';
        statusText.textContent = 'Ảnh đại diện hiện tại';
        statusText.classList.remove('text-danger');
    }
}
</script>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin_footer.php'; ?>

