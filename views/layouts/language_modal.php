<?php
/**
 * Modal Chọn Ngôn Ngữ Toàn Cầu (All Countries Language Selector)
 * Tích hợp công nghệ Dịch tự động đa ngữ Google Translate + Lưu trạng thái Cookie / LocalStorage
 */
?>

<!-- Modal Chọn Ngôn Ngữ Toàn Cầu -->
<div class="modal fade" id="languageSelectModal" tabindex="-1" aria-labelledby="languageSelectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden language-modal-content">
            <!-- Modal Header -->
            <div class="modal-header border-bottom py-3 px-4" style="background: linear-gradient(135deg, #0b2545 0%, #137a8e 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center text-white" style="width: 38px; height: 38px;">
                        <i class="bi bi-globe-americas fs-5 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="languageSelectModalLabel">
                            Chọn Ngôn Ngữ / Select Language
                        </h5>
                        <p class="small text-white-50 mb-0" style="font-size: 11.5px;">
                            Hỗ trợ dịch toàn bộ nội dung website sang tất cả các quốc gia &amp; vùng lãnh thổ trên thế giới
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <!-- Thanh Tìm Kiếm & Bộ Lọc Khu Vực -->
            <div class="p-3 bg-light border-bottom language-filter-bar">
                <div class="row g-2 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group input-group-sm shadow-xs rounded-pill overflow-hidden bg-white border">
                            <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   id="languageSearchInput" 
                                   class="form-control border-0 py-2 small" 
                                   placeholder="Tìm kiếm quốc gia, ngôn ngữ (vd: Anh, English, Nga, Pháp, Nhật, Korea...)..."
                                   autocomplete="off">
                            <button class="btn btn-link text-muted border-0 pe-3 d-none" id="clearLangSearchBtn" type="button">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <!-- Nút Đặt Lại Tiếng Việt Gốc -->
                        <div class="d-flex justify-content-md-end gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-xs" onclick="resetToVietnamese()">
                                🇻🇳 Đặt lại Tiếng Việt gốc
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabs Lọc Khu Vực Lục Địa -->
                <div class="d-flex gap-1 flex-wrap mt-2 pt-1" id="langRegionTabs">
                    <button type="button" class="btn btn-sm rounded-pill lang-tab-btn active px-3 py-1 small fw-semibold" data-region="all">
                        🌐 Tất cả (<span id="totalLangCount">100+</span>)
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill lang-tab-btn px-2 py-1 small" data-region="popular">
                        ⭐ Phổ biến Nha Trang
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill lang-tab-btn px-2 py-1 small" data-region="asia">
                        🌏 Châu Á
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill lang-tab-btn px-2 py-1 small" data-region="europe">
                        🌍 Châu Âu
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill lang-tab-btn px-2 py-1 small" data-region="americas">
                        🌎 Châu Mỹ
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill lang-tab-btn px-2 py-1 small" data-region="africa_mideast">
                        🌍 Châu Phi &amp; Trung Đông
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill lang-tab-btn px-2 py-1 small" data-region="oceania">
                        🌊 Châu Đại Dương
                    </button>
                </div>
            </div>

            <!-- Modal Body: Danh Sách Thẻ Ngôn Ngữ -->
            <div class="modal-body p-3 p-md-4" style="max-height: 60vh; overflow-y: auto;">
                <!-- Trạng thái không tìm thấy kết quả -->
                <div id="noLangMatchMessage" class="text-center py-5 d-none">
                    <i class="bi bi-search text-muted fs-1 mb-2 d-block opacity-50"></i>
                    <h6 class="fw-bold">Không tìm thấy ngôn ngữ phù hợp</h6>
                    <p class="text-muted small">Hãy thử tìm theo tên tiếng Việt (ví dụ: Nhật, Hàn, Nga) hoặc tiếng Anh (Japan, Korea, Russian).</p>
                </div>

                <!-- Lưới danh sách ngôn ngữ -->
                <div class="row g-2" id="languageGridContainer">
                    <!-- Được render tự động qua JavaScript -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top py-2 px-4 bg-light d-flex justify-content-between align-items-center">
                <div class="small text-muted d-flex align-items-center gap-1" style="font-size: 11.5px;">
                    <i class="bi bi-shield-check text-success"></i>
                    <span>Tự động ghi nhớ ngôn ngữ đã chọn cho tất cả các trang tiếp theo.</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Container Ẩn Cho Google Translate Engine -->
<div id="google_translate_element" style="display:none !important; visibility:hidden !important; height:0 !important; overflow:hidden !important;"></div>

<!-- CSS Triệt Tiêu Hoàn Toàn Thanh Thông Báo & Popup Của Google Translate -->
<style>
    /* Ẩn mọi phiên bản thanh ngang trên cùng của Google Translate (cũ & mới) */
    .goog-te-banner-frame,
    .goog-te-banner-frame.skiptranslate,
    iframe.goog-te-banner-frame,
    iframe.skiptranslate,
    body > .skiptranslate,
    .VIpgJd-ZVi9od-ORHb-OEVmcd,
    .VIpgJd-ZVi9od-ORHb,
    .VIpgJd-ZVi9od-SmfZ-OEVmcd,
    .VIpgJd-ZVi9od-xl07Ob-OEVmcd,
    .VIpgJd-ZVi9od-aZ2wEe-wOHMyf,
    .VIpgJd-ZVi9od-aZ2wEe-OiiCO,
    #goog-gt-tt,
    .goog-te-balloon-frame,
    .goog-tooltip,
    .goog-tooltip:hover,
    .VIpgJd-suEOdc {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        width: 0 !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    /* Giữ cố định body và html không bị đẩy xuống 40px */
    html, body {
        top: 0px !important;
        position: static !important;
        margin-top: 0px !important;
    }

    /* Bỏ viền sáng màu vàng/xanh khi rê chuột lên chữ đã dịch */
    .goog-text-highlight,
    font[style*="background-color"] {
        background-color: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }
</style>

<script>
/**
 * Danh mục hơn 100+ Quốc Gia & Ngôn Ngữ Trên Toàn Thế Giới
 */
const WORLD_LANGUAGES = [
    // 1. Phổ biến & Khuyên dùng tại Nha Trang
    { code: 'vi', flag: '🇻🇳', name: 'Tiếng Việt', vi: 'Việt Nam (Gốc)', region: 'popular,asia', country: 'Vietnam' },
    { code: 'en', flag: '🇬🇧', name: 'English', vi: 'Tiếng Anh (UK / US / Quốc tế)', region: 'popular,europe,americas,oceania', country: 'United Kingdom / United States / Australia' },
    { code: 'ru', flag: '🇷🇺', name: 'Русский', vi: 'Tiếng Nga (Russian)', region: 'popular,europe,asia', country: 'Russia' },
    { code: 'ko', flag: '🇰🇷', name: '한국어', vi: 'Tiếng Hàn Quốc (Korean)', region: 'popular,asia', country: 'South Korea' },
    { code: 'zh-CN', flag: '🇨🇳', name: '中文 (简体)', vi: 'Tiếng Trung (Giản thể - Chinese)', region: 'popular,asia', country: 'China / Singapore' },
    { code: 'zh-TW', flag: '🇹🇼', name: '中文 (繁體)', vi: 'Tiếng Trung (Phồn thể - Đài Loan/HK)', region: 'popular,asia', country: 'Taiwan / Hong Kong / Macau' },
    { code: 'ja', flag: '🇯🇵', name: '日本語', vi: 'Tiếng Nhật (Japanese)', region: 'popular,asia', country: 'Japan' },
    { code: 'fr', flag: '🇫🇷', name: 'Français', vi: 'Tiếng Pháp (French)', region: 'popular,europe,americas,africa_mideast', country: 'France / Canada / Belgium / Switzerland' },
    { code: 'de', flag: '🇩🇪', name: 'Deutsch', vi: 'Tiếng Đức (German)', region: 'popular,europe', country: 'Germany / Austria / Switzerland' },
    { code: 'es', flag: '🇪🇸', name: 'Español', vi: 'Tiếng Tây Ban Nha (Spanish)', region: 'popular,europe,americas', country: 'Spain / Mexico / Argentina / Colombia' },
    { code: 'th', flag: '🇹🇭', name: 'ภาษาไทย', vi: 'Tiếng Thái (Thai)', region: 'popular,asia', country: 'Thailand' },
    { code: 'id', flag: '🇮🇩', name: 'Bahasa Indonesia', vi: 'Tiếng Indonesia', region: 'popular,asia', country: 'Indonesia' },
    { code: 'ms', flag: '🇲🇾', name: 'Bahasa Melayu', vi: 'Tiếng Malaysia (Malay)', region: 'popular,asia', country: 'Malaysia / Brunei' },
    { code: 'it', flag: '🇮🇹', name: 'Italiano', vi: 'Tiếng Ý (Italian)', region: 'popular,europe', country: 'Italy' },
    { code: 'ar', flag: '🇸🇦', name: 'العربية', vi: 'Tiếng Ả Rập (Arabic)', region: 'popular,africa_mideast', country: 'Saudi Arabia / UAE / Egypt / Qatar' },
    { code: 'hi', flag: '🇮🇳', name: 'हिन्दी', vi: 'Tiếng Hindi (Ấn Độ)', region: 'popular,asia', country: 'India' },
    { code: 'pt', flag: '🇵🇹', name: 'Português', vi: 'Tiếng Bồ Đào Nha / Brazil', region: 'popular,europe,americas', country: 'Portugal / Brazil' },
    { code: 'tl', flag: '🇵🇭', name: 'Filipino (Tagalog)', vi: 'Tiếng Philippines', region: 'popular,asia', country: 'Philippines' },
    { code: 'km', flag: '🇰🇭', name: 'ភាសាខ្មែរ', vi: 'Tiếng Campuchia (Khmer)', region: 'popular,asia', country: 'Cambodia' },
    { code: 'lo', flag: '🇱🇦', name: 'ພາສາລາວ', vi: 'Tiếng Lào (Lao)', region: 'popular,asia', country: 'Laos' },

    // 2. Châu Á (Asia)
    { code: 'my', flag: '🇲🇲', name: 'မြန်မာစာ', vi: 'Tiếng Miến Điện (Myanmar)', region: 'asia', country: 'Myanmar' },
    { code: 'mn', flag: '🇲🇳', name: 'Монгол', vi: 'Tiếng Mông Cổ (Mongolian)', region: 'asia', country: 'Mongolia' },
    { code: 'bn', flag: '🇧🇩', name: 'বাংলা', vi: 'Tiếng Bengali (Bangladesh / Ấn Độ)', region: 'asia', country: 'Bangladesh / India' },
    { code: 'ta', flag: '🇱🇰', name: 'தமிழ்', vi: 'Tiếng Tamil (Sri Lanka / Singapore)', region: 'asia', country: 'Sri Lanka / India / Singapore' },
    { code: 'te', flag: '🇮🇳', name: 'తెలుగు', vi: 'Tiếng Telugu (Ấn Độ)', region: 'asia', country: 'India' },
    { code: 'mr', flag: '🇮🇳', name: 'मराठी', vi: 'Tiếng Marathi (Ấn Độ)', region: 'asia', country: 'India' },
    { code: 'gu', flag: '🇮🇳', name: 'ગુજરાતી', vi: 'Tiếng Gujarati (Ấn Độ)', region: 'asia', country: 'India' },
    { code: 'kn', flag: '🇮🇳', name: 'ಕನ್ನಡ', vi: 'Tiếng Kannada (Ấn Độ)', region: 'asia', country: 'India' },
    { code: 'ml', flag: '🇮🇳', name: 'മലയാളം', vi: 'Tiếng Malayalam (Ấn Độ)', region: 'asia', country: 'India' },
    { code: 'pa', flag: '🇮🇳', name: 'ਪੰਜਾਬੀ', vi: 'Tiếng Punjabi (Ấn Độ / Pakistan)', region: 'asia', country: 'India / Pakistan' },
    { code: 'ur', flag: '🇵🇰', name: 'اردو', vi: 'Tiếng Urdu (Pakistan)', region: 'asia', country: 'Pakistan' },
    { code: 'ne', flag: '🇳🇵', name: 'नेपाली', vi: 'Tiếng Nepal (Nepali)', region: 'asia', country: 'Nepal' },
    { code: 'si', flag: '🇱🇰', name: 'සිංහල', vi: 'Tiếng Sinhala (Sri Lanka)', region: 'asia', country: 'Sri Lanka' },
    { code: 'kk', flag: '🇰🇿', name: 'Қазақ тілі', vi: 'Tiếng Kazakh (Kazakhstan)', region: 'asia', country: 'Kazakhstan' },
    { code: 'uz', flag: '🇺🇿', name: 'Oʻzbekcha', vi: 'Tiếng Uzbek (Uzbekistan)', region: 'asia', country: 'Uzbekistan' },
    { code: 'ky', flag: '🇰🇬', name: 'Кыргызча', vi: 'Tiếng Kyrgyz (Kyrgyzstan)', region: 'asia', country: 'Kyrgyzstan' },
    { code: 'tg', flag: '🇹🇯', name: 'Тоҷикӣ', vi: 'Tiếng Tajik (Tajikistan)', region: 'asia', country: 'Tajikistan' },
    { code: 'tk', flag: '🇹🇲', name: 'Türkmençe', vi: 'Tiếng Turkmen (Turkmenistan)', region: 'asia', country: 'Turkmenistan' },
    { code: 'ka', flag: '🇬🇪', name: 'ქართული', vi: 'Tiếng Georgia (Georgian)', region: 'asia,europe', country: 'Georgia' },
    { code: 'hy', flag: '🇦🇲', name: 'Հայերեն', vi: 'Tiếng Armenia (Armenian)', region: 'asia,europe', country: 'Armenia' },
    { code: 'az', flag: '🇦🇿', name: 'Azərbaycan', vi: 'Tiếng Azerbaijan', region: 'asia,europe', country: 'Azerbaijan' },

    // 3. Châu Âu (Europe)
    { code: 'nl', flag: '🇳🇱', name: 'Nederlands', vi: 'Tiếng Hà Lan (Dutch / Bỉ)', region: 'europe', country: 'Netherlands / Belgium' },
    { code: 'pl', flag: '🇵🇱', name: 'Polski', vi: 'Tiếng Ba Lan (Polish)', region: 'europe', country: 'Poland' },
    { code: 'sv', flag: '🇸🇪', name: 'Svenska', vi: 'Tiếng Thụy Điển (Swedish)', region: 'europe', country: 'Sweden' },
    { code: 'da', flag: '🇩🇰', name: 'Dansk', vi: 'Tiếng Đan Mạch (Danish)', region: 'europe', country: 'Denmark' },
    { code: 'no', flag: '🇳🇴', name: 'Norsk', vi: 'Tiếng Na Uy (Norwegian)', region: 'europe', country: 'Norway' },
    { code: 'fi', flag: '🇫🇮', name: 'Suomi', vi: 'Tiếng Phần Lan (Finnish)', region: 'europe', country: 'Finland' },
    { code: 'el', flag: '🇬🇷', name: 'Ελληνικά', vi: 'Tiếng Hy Lạp (Greek)', region: 'europe', country: 'Greece / Cyprus' },
    { code: 'tr', flag: '🇹🇷', name: 'Türkçe', vi: 'Tiếng Thổ Nhĩ Kỳ (Turkish)', region: 'europe,asia', country: 'Turkey' },
    { code: 'cs', flag: '🇨🇿', name: 'Čeština', vi: 'Tiếng Séc (Czech)', region: 'europe', country: 'Czech Republic' },
    { code: 'hu', flag: '🇭🇺', name: 'Magyar', vi: 'Tiếng Hungary (Hungarian)', region: 'europe', country: 'Hungary' },
    { code: 'ro', flag: '🇷🇴', name: 'Română', vi: 'Tiếng Romania / Moldova', region: 'europe', country: 'Romania / Moldova' },
    { code: 'uk', flag: '🇺🇦', name: 'Українська', vi: 'Tiếng Ukraina (Ukrainian)', region: 'europe', country: 'Ukraine' },
    { code: 'bg', flag: '🇧🇬', name: 'Български', vi: 'Tiếng Bulgaria (Bulgarian)', region: 'europe', country: 'Bulgaria' },
    { code: 'hr', flag: '🇭🇷', name: 'Hrvatski', vi: 'Tiếng Croatia (Croatian)', region: 'europe', country: 'Croatia' },
    { code: 'sr', flag: '🇷🇸', name: 'Српски', vi: 'Tiếng Serbia (Serbian)', region: 'europe', country: 'Serbia' },
    { code: 'sk', flag: '🇸🇰', name: 'Slovenčina', vi: 'Tiếng Slovakia (Slovak)', region: 'europe', country: 'Slovakia' },
    { code: 'sl', flag: '🇸🇮', name: 'Slovenščina', vi: 'Tiếng Slovenia (Slovenian)', region: 'europe', country: 'Slovenia' },
    { code: 'lt', flag: '🇱🇹', name: 'Lietuvių', vi: 'Tiếng Litva (Lithuanian)', region: 'europe', country: 'Lithuania' },
    { code: 'lv', flag: '🇱🇻', name: 'Latviešu', vi: 'Tiếng Latvia (Latvian)', region: 'europe', country: 'Latvia' },
    { code: 'et', flag: '🇪🇪', name: 'Eesti', vi: 'Tiếng Estonia (Estonian)', region: 'europe', country: 'Estonia' },
    { code: 'is', flag: '🇮🇸', name: 'Íslenska', vi: 'Tiếng Iceland (Icelandic)', region: 'europe', country: 'Iceland' },
    { code: 'ga', flag: '🇮🇪', name: 'Gaeilge', vi: 'Tiếng Ireland (Irish)', region: 'europe', country: 'Ireland' },
    { code: 'sq', flag: '🇦🇱', name: 'Shqip', vi: 'Tiếng Albania (Albanian)', region: 'europe', country: 'Albania / Kosovo' },
    { code: 'bs', flag: '🇧🇦', name: 'Bosanski', vi: 'Tiếng Bosnia (Bosnian)', region: 'europe', country: 'Bosnia and Herzegovina' },
    { code: 'mk', flag: '🇲🇰', name: 'Македонски', vi: 'Tiếng Bắc Macedonia', region: 'europe', country: 'North Macedonia' },
    { code: 'be', flag: '🇧🇾', name: 'Беларуская', vi: 'Tiếng Belarus (Belarusian)', region: 'europe', country: 'Belarus' },
    { code: 'mt', flag: '🇲🇹', name: 'Malti', vi: 'Tiếng Malta (Maltese)', region: 'europe', country: 'Malta' },
    { code: 'lb', flag: '🇱🇺', name: 'Lëtzebuergesch', vi: 'Tiếng Luxembourg', region: 'europe', country: 'Luxembourg' },
    { code: 'ca', flag: '🇪🇸', name: 'Català', vi: 'Tiếng Catalan (Andorra / TBN)', region: 'europe', country: 'Andorra / Spain' },
    { code: 'eu', flag: '🇪🇸', name: 'Euskara', vi: 'Tiếng Basque (Tây Ban Nha)', region: 'europe', country: 'Spain' },
    { code: 'gl', flag: '🇪🇸', name: 'Galego', vi: 'Tiếng Galicia (Tây Ban Nha)', region: 'europe', country: 'Spain' },
    { code: 'cy', flag: '🏴󠁧󠁢󠁷󠁬󠁳󠁿', name: 'Cymraeg', vi: 'Tiếng Wales (Xứ Wales)', region: 'europe', country: 'Wales / UK' },
    { code: 'gd', flag: '🏴󠁧󠁢󠁳󠁣󠁴󠁿', name: 'Gàidhlig', vi: 'Tiếng Scotland (Gaelic)', region: 'europe', country: 'Scotland / UK' },
    { code: 'la', flag: '🇻🇦', name: 'Latina', vi: 'Tiếng La-tinh (Vatican)', region: 'europe', country: 'Vatican' },
    { code: 'eo', flag: '🌐', name: 'Esperanto', vi: 'Quốc tế ngữ (Esperanto)', region: 'europe', country: 'International' },

    // 4. Châu Mỹ (Americas)
    { code: 'ht', flag: '🇭🇹', name: 'Kreyòl Ayisyen', vi: 'Tiếng Haiti (Creole)', region: 'americas', country: 'Haiti' },
    { code: 'qu', flag: '🇵🇪', name: 'Runasimi', vi: 'Tiếng Quechua (Peru / Bolivia)', region: 'americas', country: 'Peru / Bolivia / Ecuador' },
    { code: 'gn', flag: '🇵🇾', name: 'Avañe\'ẽ', vi: 'Tiếng Guarani (Paraguay)', region: 'americas', country: 'Paraguay' },
    { code: 'ay', flag: '🇧🇴', name: 'Aymar aru', vi: 'Tiếng Aymara (Bolivia / Peru)', region: 'americas', country: 'Bolivia / Peru' },

    // 5. Châu Phi & Trung Đông (Africa & Middle East)
    { code: 'fa', flag: '🇮🇷', name: 'فارسی', vi: 'Tiếng Ba Tư (Iran / Persian)', region: 'africa_mideast', country: 'Iran / Afghanistan' },
    { code: 'he', flag: '🇮🇱', name: 'עברית', vi: 'Tiếng Do Thái (Hebrew / Israel)', region: 'africa_mideast', country: 'Israel' },
    { code: 'sw', flag: '🇰🇪', name: 'Kiswahili', vi: 'Tiếng Swahili (Đông Phi)', region: 'africa_mideast', country: 'Kenya / Tanzania / Uganda' },
    { code: 'am', flag: '🇪🇹', name: 'አማርኛ', vi: 'Tiếng Amharic (Ethiopia)', region: 'africa_mideast', country: 'Ethiopia' },
    { code: 'af', flag: '🇿🇦', name: 'Afrikaans', vi: 'Tiếng Nam Phi (Afrikaans)', region: 'africa_mideast', country: 'South Africa / Namibia' },
    { code: 'zu', flag: '🇿🇦', name: 'isiZulu', vi: 'Tiếng Zulu (Nam Phi)', region: 'africa_mideast', country: 'South Africa' },
    { code: 'xh', flag: '🇿🇦', name: 'isiXhosa', vi: 'Tiếng Xhosa (Nam Phi)', region: 'africa_mideast', country: 'South Africa' },
    { code: 'st', flag: '🇱🇸', name: 'Sesotho', vi: 'Tiếng Sesotho (Lesotho / Nam Phi)', region: 'africa_mideast', country: 'Lesotho / South Africa' },
    { code: 'yo', flag: '🇳🇬', name: 'Yorùbá', vi: 'Tiếng Yoruba (Nigeria)', region: 'africa_mideast', country: 'Nigeria / Benin' },
    { code: 'ig', flag: '🇳🇬', name: 'Asụsụ Igbo', vi: 'Tiếng Igbo (Nigeria)', region: 'africa_mideast', country: 'Nigeria' },
    { code: 'ha', flag: '🇳🇬', name: 'Harshen Hausa', vi: 'Tiếng Hausa (Tây Phi)', region: 'africa_mideast', country: 'Nigeria / Niger / Ghana' },
    { code: 'so', flag: '🇸🇴', name: 'Soomaali', vi: 'Tiếng Somali (Somalia)', region: 'africa_mideast', country: 'Somalia' },
    { code: 'mg', flag: '🇲🇬', name: 'Malagasy', vi: 'Tiếng Madagascar (Malagasy)', region: 'africa_mideast', country: 'Madagascar' },
    { code: 'rw', flag: '🇷🇼', name: 'Ikinyarwanda', vi: 'Tiếng Kinyarwanda (Rwanda)', region: 'africa_mideast', country: 'Rwanda' },
    { code: 'sn', flag: '🇿🇼', name: 'chiShona', vi: 'Tiếng Shona (Zimbabwe)', region: 'africa_mideast', country: 'Zimbabwe' },
    { code: 'ny', flag: '🇲🇼', name: 'Chichewa', vi: 'Tiếng Chichewa (Malawi / Zambia)', region: 'africa_mideast', country: 'Malawi / Zambia' },
    { code: 'lg', flag: '🇺🇬', name: 'Luganda', vi: 'Tiếng Luganda (Uganda)', region: 'africa_mideast', country: 'Uganda' },
    { code: 'ku', flag: '🇮🇶', name: 'Kurdî', vi: 'Tiếng Kurdish (Kurd)', region: 'africa_mideast', country: 'Iraq / Kurdistan' },
    { code: 'ps', flag: '🇦🇫', name: 'پښتو', vi: 'Tiếng Pashto (Afghanistan)', region: 'africa_mideast', country: 'Afghanistan' },
    { code: 'sd', flag: '🇵🇰', name: 'سنڌي', vi: 'Tiếng Sindhi', region: 'asia,africa_mideast', country: 'Pakistan / India' },

    // 6. Châu Đại Dương (Oceania)
    { code: 'mi', flag: '🇳🇿', name: 'Te Reo Māori', vi: 'Tiếng Maori (New Zealand)', region: 'oceania', country: 'New Zealand' },
    { code: 'sm', flag: '🇼🇸', name: 'Gagana Sāmoa', vi: 'Tiếng Samoa', region: 'oceania', country: 'Samoa' },
    { code: 'haw', flag: '🇺🇸', name: 'ʻŌlelo Hawaiʻi', vi: 'Tiếng Hawaii', region: 'oceania,americas', country: 'Hawaii / USA' }
];

// Khởi tạo hiển thị danh sách ngôn ngữ khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', function() {
    const countEl = document.getElementById('totalLangCount');
    if (countEl) countEl.textContent = WORLD_LANGUAGES.length;
    renderLanguageGrid('all');
    updateNavbarCurrentLangDisplay();
    initLanguageSearchAndFilters();
});

// Hàm render thẻ ngôn ngữ theo khu vực và từ khóa
function renderLanguageGrid(regionFilter = 'all', searchQuery = '') {
    const container = document.getElementById('languageGridContainer');
    const noMatch = document.getElementById('noLangMatchMessage');
    if (!container) return;

    const currentLangCode = getCurrentLangCode();
    const cleanQuery = searchQuery.trim().toLowerCase();

    const filtered = WORLD_LANGUAGES.filter(item => {
        if (regionFilter !== 'all' && !item.region.split(',').includes(regionFilter)) {
            return false;
        }
        if (cleanQuery) {
            const matchName = item.name.toLowerCase().includes(cleanQuery);
            const matchVi = item.vi.toLowerCase().includes(cleanQuery);
            const matchCountry = item.country.toLowerCase().includes(cleanQuery);
            const matchCode = item.code.toLowerCase().includes(cleanQuery);
            return matchName || matchVi || matchCountry || matchCode;
        }
        return true;
    });

    if (filtered.length === 0) {
        container.innerHTML = '';
        if (noMatch) noMatch.classList.remove('d-none');
        return;
    }

    if (noMatch) noMatch.classList.add('d-none');

    let html = '';
    filtered.forEach(lang => {
        const isActive = (lang.code === currentLangCode);
        html += `
            <div class="col-6 col-md-4 col-lg-3">
                <div class="lang-card p-2 rounded-3 border d-flex align-items-center gap-2 h-100 ${isActive ? 'active-lang' : ''}" 
                     role="button" 
                     tabindex="0"
                     onclick="selectWorldLanguage('${lang.code}', '${escapeLangJs(lang.name)}', '${lang.flag}')">
                    <span class="lang-flag fs-3 flex-shrink-0">${lang.flag}</span>
                    <div class="overflow-hidden flex-grow-1">
                        <strong class="d-block text-truncate lang-native-name small fw-bold">${escapeLangHtml(lang.name)}</strong>
                        <span class="d-block text-truncate text-muted extra-small" style="font-size: 11px;">${escapeLangHtml(lang.vi)}</span>
                    </div>
                    ${isActive ? '<i class="bi bi-check-circle-fill text-primary ms-auto fs-6"></i>' : ''}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Bắt sự kiện tìm kiếm và chuyển tab khu vực
function initLanguageSearchAndFilters() {
    const searchInput = document.getElementById('languageSearchInput');
    const clearBtn = document.getElementById('clearLangSearchBtn');
    let currentRegion = 'all';

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value;
            if (clearBtn) {
                clearBtn.classList.toggle('d-none', !query);
            }
            renderLanguageGrid(currentRegion, query);
        });
    }

    if (clearBtn && searchInput) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.classList.add('d-none');
            renderLanguageGrid(currentRegion, '');
            searchInput.focus();
        });
    }

    const tabBtns = document.querySelectorAll('.lang-tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentRegion = this.getAttribute('data-region') || 'all';
            const query = searchInput ? searchInput.value : '';
            renderLanguageGrid(currentRegion, query);
        });
    });
}

// Lấy mã ngôn ngữ hiện hành từ Cookie hoặc LocalStorage
function getCurrentLangCode() {
    const cookies = document.cookie.split(';');
    for (let c of cookies) {
        c = c.trim();
        if (c.startsWith('googtrans=')) {
            const val = decodeURIComponent(c.substring('googtrans='.length));
            const parts = val.split('/');
            const code = parts[parts.length - 1];
            if (code && code !== 'vi') return code;
        }
    }
    return localStorage.getItem('nhatrang_selected_lang') || 'vi';
}

// Cập nhật nhãn và quốc kỳ trên thanh navbar
function updateNavbarCurrentLangDisplay() {
    const code = getCurrentLangCode();
    const langObj = WORLD_LANGUAGES.find(l => l.code === code) || WORLD_LANGUAGES[0];

    const flagEl = document.getElementById('currentLangFlag');
    const labelEl = document.getElementById('currentLangLabel');

    if (flagEl) flagEl.textContent = langObj.flag;
    if (labelEl) labelEl.textContent = langObj.name;
}

// Xử lý khi du khách bấm chọn một ngôn ngữ
function selectWorldLanguage(code, name, flag) {
    if (code === 'vi') {
        resetToVietnamese();
        return;
    }

    // 1. Ghi nhớ vào localStorage
    localStorage.setItem('nhatrang_selected_lang', code);

    // 2. Thiết lập cookie chuẩn của Google Translate cho toàn bộ domain và đường dẫn gốc
    const host = window.location.hostname;
    document.cookie = `googtrans=/vi/${code}; path=/;`;
    if (host && host !== 'localhost' && host !== '127.0.0.1') {
        document.cookie = `googtrans=/vi/${code}; domain=.${host}; path=/;`;
    }

    // 3. Đóng modal
    const modalEl = document.getElementById('languageSelectModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }

    // 4. Kích hoạt dịch trực tiếp qua Google Translate Combo nếu đã sẵn sàng
    const combo = document.querySelector('.goog-te-combo');
    if (combo) {
        combo.value = code;
        combo.dispatchEvent(new Event('change'));
        updateNavbarCurrentLangDisplay();
        renderLanguageGrid('all');
    } else {
        window.location.reload();
    }
}

// Khôi phục về Tiếng Việt gốc
function resetToVietnamese() {
    localStorage.removeItem('nhatrang_selected_lang');

    const host = window.location.hostname;
    document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    if (host && host !== 'localhost' && host !== '127.0.0.1') {
        document.cookie = `googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=.${host}; path=/;`;
    }

    const modalEl = document.getElementById('languageSelectModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }
    window.location.reload();
}

function escapeLangHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function escapeLangJs(str) {
    if (!str) return '';
    return str.replace(/'/g, "\\'");
}
</script>

<!-- Tích hợp Google Translate API Engine tự động -->
<script type="text/javascript">
function googleTranslateElementInit() {
    if (typeof google === 'undefined' || !google.translate) return;
    new google.translate.TranslateElement({
        pageLanguage: 'vi',
        autoDisplay: false,
        multilanguagePage: true
    }, 'google_translate_element');
    suppressGoogleTranslateBar();
}

// Tự động theo dõi và gỡ bỏ thanh thông báo Google Translate ngay khi vừa xuất hiện
function suppressGoogleTranslateBar() {
    const cleanUp = () => {
        if (document.body && document.body.style.top && document.body.style.top !== '0px') {
            document.body.style.setProperty('top', '0px', 'important');
        }
        if (document.documentElement && document.documentElement.style.height) {
            document.documentElement.style.removeProperty('height');
        }
        const banners = document.querySelectorAll(
            'iframe.goog-te-banner-frame, iframe.skiptranslate, body > .skiptranslate, .VIpgJd-ZVi9od-ORHb-OEVmcd, .VIpgJd-ZVi9od-aZ2wEe-wOHMyf'
        );
        banners.forEach(el => {
            if (el.id !== 'google_translate_element') {
                el.style.setProperty('display', 'none', 'important');
                el.style.setProperty('visibility', 'hidden', 'important');
                el.style.setProperty('height', '0px', 'important');
            }
        });
    };

    cleanUp();
    const observer = new MutationObserver(cleanUp);
    if (document.documentElement) {
        observer.observe(document.documentElement, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['style', 'class']
        });
    }
}

document.addEventListener('DOMContentLoaded', suppressGoogleTranslateBar);
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer></script>

