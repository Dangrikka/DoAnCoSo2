// assets/js/script.js – PHIÊN BẢN CUỐI CÙNG HOÀN HẢO NHẤT THẾ GIỚI 2025
(() => {
    // Tắt hiệu ứng nếu máy yếu
    if (/low-performance|kill-all-effects/.test(document.documentElement.className)) {
        document.body.style.opacity = '1';
        return;
    }

    const audio = new Audio();
    let playlist = [];
    let currentIndex = -1;
    let isPlaying = false;
    let isShuffle = false;
    let isRepeat = false;

    const $  = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);

    const els = {
        player: $('#musicPlayer'),
        playPause: $('.play-pause-btn'),
        prev: $('.prev-btn'),
        next: $('.next-btn'),
        shuffle: $('.shuffle-btn'),
        repeat: $('.repeat-btn'),
        progressBar: $('.progress'),
        progressContainer: $('.progress-container'),
        currentTime: $('.current-time'),
        duration: $('.duration'),
        volume: $('.volume-slider'),
        cover: $('#playerCover'),
        title: $('#playerTitle'),
        artist: $('#playerArtist'),
        favoriteBtn: $('#playerFavoriteBtn')
    };

    // === TẢI PLAYLIST ===
    const loadPlaylist = () => {
        playlist = Array.from($$('.song-card, .song-row'))
            .map(el => ({
                id: parseInt(el.dataset.songId, 10) || null,
                title: el.dataset.title || 'Không rõ',
                artist: el.dataset.artist || 'Nghệ sĩ không rõ',
                audio: el.dataset.audio || '',
                image: el.dataset.image || '../assets/songs/images/default.jpg',
                isFavorite: el.dataset.isFavorite === 'true' || el.dataset.isFavorite === '1'
            }))
            .filter(s => s.id && s.audio);

        console.log(`Playlist loaded: ${playlist.length} songs`);
    };

    // === PHÁT BÀI HÁT ===
    const playSong = (index) => {
        if (index < 0 || index >= playlist.length) return;

        const song = playlist[index];
        currentIndex = index;

        audio.src = song.audio;
        audio.load();

        audio.play().then(() => {
            isPlaying = true;
            updatePlayPauseIcon(true);
            updatePlayer(song);
            highlightCurrentSong();
            els.player?.classList.add('playing');

            // Tăng lượt nghe – an toàn tuyệt đối
            song.id && fetch('../ajax/increment_play.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'song_id=' + song.id
            }).catch(() => {});

        }).catch(err => {
            console.error('Play error:', err);
            showToast('Không thể phát bài hát này!', 'error');
            nextSong();
        });
    };

    // === CẬP NHẬT PLAYER ===
    const updatePlayer = (song) => {
        if (!song) return;
        els.title.textContent = song.title;
        els.artist.textContent = song.artist;

        if (els.cover) {
            els.cover.src = song.image;
            els.cover.onerror = () => els.cover.src = '../assets/songs/images/default.jpg';
        }

        if (els.favoriteBtn && song.id) {
            els.favoriteBtn.dataset.songId = song.id;
            const isFav = song.isFavorite;
            els.favoriteBtn.classList.toggle('active', isFav);
            const icon = els.favoriteBtn.querySelector('i');
            if (icon) icon.className = isFav ? 'fas fa-heart text-danger' : 'far fa-heart';
        }
    };

    // === HIGHLIGHT BÀI ĐANG PHÁT ===
    const highlightCurrentSong = () => {
        $$('.song-card, .song-row').forEach((el, i) => {
            el.classList.toggle('active', i === currentIndex);
        });
    };

    // === CẬP NHẬT NÚT PLAY/PAUSE ===
    const updatePlayPauseIcon = (playing) => {
        const icon = els.playPause?.querySelector('i');
        if (icon) icon.className = playing ? 'fas fa-pause' : 'fas fa-play';
    };

    // === ĐIỀU KHIỂN ===
    els.playPause?.addEventListener('click', () => {
        if (isPlaying) {
            audio.pause();
            isPlaying = false;
        } else {
            audio.play();
            isPlaying = true;
        }
        updatePlayPauseIcon(isPlaying);
    });

    const nextSong = () => {
        let next = currentIndex + 1;
        if (isShuffle) next = Math.floor(Math.random() * playlist.length);
        else if (next >= playlist.length) next = isRepeat ? 0 : currentIndex;
        playSong(next);
    };

    els.next?.addEventListener('click', nextSong);

    els.prev?.addEventListener('click', () => {
        let prev = currentIndex - 1;
        if (prev < 0) prev = isRepeat ? playlist.length - 1 : currentIndex;
        playSong(prev);
    });

    els.shuffle?.addEventListener('click', () => {
        isShuffle = !isShuffle;
        els.shuffle.classList.toggle('active', isShuffle);
        showToast(isShuffle ? 'Đã bật chế độ ngẫu nhiên' : 'Đã tắt chế độ ngẫu nhiên', 'info');
    });

    els.repeat?.addEventListener('click', () => {
        isRepeat = !isRepeat;
        els.repeat.classList.toggle('active', isRepeat);
        audio.loop = isRepeat && !isShuffle;
        showToast(isRepeat ? 'Đã bật chế độ lặp lại' : 'Đã tắt chế độ lặp lại', 'info');
    });

    // === PROGRESS BAR + VOLUME ===
    audio.addEventListener('timeupdate', () => {
        if (audio.duration) {
            const percent = (audio.currentTime / audio.duration) * 100;
            els.progressBar.style.width = percent + '%';
            els.currentTime.textContent = formatTime(audio.currentTime);
            els.duration.textContent = formatTime(audio.duration);
        }
    });

    els.progressContainer?.addEventListener('click', e => {
        if (!audio.duration) return;
        const rect = els.progressContainer.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        audio.currentTime = percent * audio.duration;
    });

    if (els.volume) {
        els.volume.value = localStorage.getItem('volume') || 0.7;
        audio.volume = els.volume.value;
        els.volume.addEventListener('input', e => {
            audio.volume = e.target.value;
            localStorage.setItem('volume', e.target.value);
        });
    }

    audio.addEventListener('ended', () => {
        if (isRepeat && !isShuffle) {
            audio.currentTime = 0;
            audio.play();
        } else {
            nextSong();
        }
    });

    const formatTime = sec => {
        if (isNaN(sec)) return '0:00';
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60);
        return `${m}:${s < 10 ? '0' + s : s}`;
    };

    // === CLICK VÀO CARD/ROW ĐỂ PHÁT ===
    document.addEventListener('click', e => {
        const card = e.target.closest('.song-card, .song-row');
        if (!card) return;
        if (e.target.closest('.btn-favorite, .btn-add-to-album, .btn-remove-from-album, button, a, .dropdown')) return;

        const index = Array.from($$('.song-card, .song-row')).indexOf(card);
        if (index !== -1) playSong(index);
    });

    // === YÊU THÍCH ===
    window.toggleFavorite = (btn, songId) => {
        if (!songId) return;
        e?.stopPropagation();

        const icon = btn.querySelector('i');
        const wasActive = btn.classList.contains('active');
        icon.className = 'fas fa-spinner fa-spin text-primary';

        fetch('../ajax/toggle_favorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'song_id=' + songId
        })
        .then(r => r.json())
        .then(data => {
            const isNowFav = data.status === 'added';

            $$(`.btn-favorite[data-song-id="${songId}"]`).forEach(b => {
                b.classList.toggle('active', isNowFav);
                b.querySelector('i').className = isNowFav ? 'fas fa-heart text-danger' : 'far fa-heart';
            });

            const songInList = playlist.find(s => s.id == songId);
            if (songInList) songInList.isFavorite = isNowFav;

            if (currentIndex >= 0 && playlist[currentIndex]?.id == songId) {
                updatePlayer(playlist[currentIndex]);
            }

            showToast(isNowFav ? 'Đã thêm vào yêu thích!' : 'Đã xóa khỏi yêu thích', isNowFav ? 'success' : 'info');
        })
        .catch(() => {
            icon.className = wasActive ? 'fas fa-heart text-danger' : 'far fa-heart';
            showToast('Lỗi kết nối!', 'error');
        });
    };

    // === THÊM VÀO ALBUM ===
    window.addToAlbum = (songId, albumId) => {
        if (!songId || !albumId) return;

        fetch('../ajax/add_to_album.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `song_id=${songId}&album_id=${albumId}`
        })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.status === 'success' ? 'success' : d.status === 'exists' ? 'warning' : 'error');
            if (d.status === 'success' || d.status === 'exists') {
                bootstrap.Modal.getInstance('#addToAlbumModal')?.hide();
            }
        })
        .catch(() => showToast('Lỗi mạng!', 'error'));
    };

    // === XÓA KHỎI ALBUM ===
    window.removeFromAlbum = (albumId, songId, element) => {
        if (!confirm('Xóa bài hát này khỏi album?')) return;

        fetch('../ajax/remove_from_album.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `album_id=${albumId}&song_id=${songId}`
        })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                element.closest('.song-row')?.remove();
                showToast('Đã xóa khỏi album!', 'success');
                if (!$$('.song-row')[0]) {
                    setTimeout(() => location.reload(), 800);
                }
            } else {
                showToast(d.message || 'Lỗi!', 'error');
            }
        })
        .catch(() => showToast('Lỗi kết nối!', 'error'));
    };

    // === PHÁT CẢ ALBUM ===
    window.playEntireAlbum = () => {
        if (playlist.length === 0) {
            showToast('Album trống!', 'warning');
            return;
        }
        playSong(0);
        showToast('Đang phát toàn bộ album!', 'success');
    };

    // === TOAST SIÊU ĐẸP ===
    window.showToast = (msg, type = 'success') => {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'success'} border-0 position-fixed bottom-0 end-0 m-3 shadow-lg`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body fw-semibold">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>`;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();
        setTimeout(() => toast.remove(), 3500);
    };

    // === KHỞI ĐỘNG ===
    loadPlaylist();
    document.body.style.opacity = '1';

    // Tự động phát lại nếu có bài đang phát (khi reload trang)
    if (currentIndex >= 0 && playlist[currentIndex]) {
        updatePlayer(playlist[currentIndex]);
        highlightCurrentSong();
    }
})();