<!-- includes/add_to_album_modal.php – HOÀN HẢO 2025 -->
<div class="modal fade" id="addToAlbumModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-4 fw-bold text-gradient">
                    <i class="fas fa-plus-circle me-2"></i> Thêm vào album
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <!-- Danh sách album dạng grid đẹp như Spotify -->
                <div id="albumList" class="row g-3">
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-muted"></i>
                        <p class="mt-3 text-muted">Đang tải album của bạn...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 d-none" id="modalFooter">
                <button type="button" class="btn btn-outline-light px-4" data-bs-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>

<script>
// Biến tạm lưu song_id hiện tại
let currentSongId = null;

// Mở modal + lưu song_id
document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-add-to-album');
    if (!btn) return;

    e.stopPropagation();
    currentSongId = btn.dataset.songId;
    if (!currentSongId) {
        showToast('Lỗi: Không tìm thấy bài hát!', 'error');
        return;
    }

    loadUserAlbums();
    new bootstrap.Modal('#addToAlbumModal').show();
});

// Tải danh sách album của user (ajax – siêu nhanh)
function loadUserAlbums() {
    const container = document.getElementById('albumList');
    container.innerHTML = `
        <div class="col-12 text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-muted"></i>
            <p class="mt-3 text-muted">Đang tải album...</p>
        </div>`;

    fetch('../ajax/get_user_albums.php')
    .then(r => r.json())
    .then(data => {
        if (data.status !== 'success' || data.albums.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-compact-disc fa-5x text-muted mb-4 opacity-50"></i>
                    <h5 class="text-muted">Bạn chưa có album nào</h5>
                    <a href="create_album.php" class="btn btn-primary mt-3 px-4 rounded-pill">
                        <i class="fas fa-plus me-2"></i> Tạo album đầu tiên
                    </a>
                </div>`;
            return;
        }

        let html = '';
        data.albums.forEach(album => {
            const img = album.cover_image || '../assets/albums/default.jpg';
            html += `
            <div class="col-6 col-md-4 col-lg-3">
                <div class="album-option text-center p-3 rounded-4 border border-cyan cursor-pointer transition-all hover-scale"
                     onclick="addToAlbum(${currentSongId}, ${album.id})">
                    <img src="${img}" class="rounded-3 mb-3 shadow" width="100%" style="height:140px; object-fit:cover;"
                         onerror="this.src='../assets/albums/default.jpg'" alt="${album.name}">
                    <h6 class="text-white mb-1 text-truncate">${album.name}</h6>
                    <small class="text-muted">${album.song_count} bài hát</small>
                </div>
            </div>`;
        });
        container.innerHTML = html;
    })
    .catch(() => {
        container.innerHTML = `<div class="col-12 text-center text-danger">Lỗi tải album!</div>`;
    });
}

// Hàm thêm vào album (đã có trong script.js)
function addToAlbum(songId, albumId) {
    if (!songId || !albumId) return;

    fetch('../ajax/add_to_album.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `song_id=${songId}&album_id=${albumId}`
    })
    .then(r => r.json())
    .then(d => {
        showToast(d.message, 
            d.status === 'success' ? 'success' : 
            d.status === 'exists' ? 'warning' : 'error'
        );
        if (d.status === 'success' || d.status === 'exists') {
            bootstrap.Modal.getInstance('#addToAlbumModal')?.hide();
        }
    })
    .catch(() => showToast('Lỗi kết nối!', 'error'));
}
</script>

<style>
.album-option:hover {
    background: rgba(0, 212, 255, 0.15) !important;
    border-color: #00D4FF !important;
    transform: translateY(-4px);
}
.hover-scale { transition: all 0.3s ease; }
.hover-scale:hover { transform: scale(1.05); }
.border-cyan { border-color: rgba(0, 212, 255, 0.3) !important; }
</style>