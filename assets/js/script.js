// assets/js/script.js – FINAL FIX DOUBLE COUNT 2025
(() => {
    // 🛑 STOP: Ngăn script chạy 2 lần nếu lỡ được include trùng lặp trong HTML
    if (window.HAS_INIT_MUSIC_PLAYER) {
        console.warn('⚠️ Music Player Script đã được tải trước đó. Bỏ qua lần khởi tạo thứ 2.');
        return;
    }
    window.HAS_INIT_MUSIC_PLAYER = true;

    /* ================= CORE ================= */
    const _domAudio = document.getElementById('audio');
    const audio = _domAudio ? _domAudio : new Audio();
    audio.preload = 'metadata';

    window.playlist = [];
    window.currentIndex = -1;

    let isPlaying = false;
    let isShuffle = false;
    let isRepeat = false;
    let isSwitchingSong = false;
    let shuffleOrder = [];
    let shufflePos = -1;

    // --- LOGIC TÍNH VIEW CHẶT CHẼ ---
    // Thay vì dùng true/false, ta lưu ID của bài hát đã đếm view phiên này
    let lastCountedSongId = null; 
    const MIN_LISTEN_TIME = 10; // Nghe trên 10s mới tính
    const lastIncrementedTime = {}; // Lưu timestamp để debounce
    const INCREMENT_SKIP_WINDOW = 60 * 1000; // 60s

    const $ = s => document.querySelector(s);
    const $$ = s => Array.from(document.querySelectorAll(s));

    const els = {
        player: $('#musicPlayer'),
        playPause: $('.play-pause-btn'),
        prev: $('.prev-btn'),
        next: $('.next-btn'),
        shuffle: $('.shuffle-btn'),
        repeat: $('.repeat-btn'),
        progress: $('.progress'),
        progressContainer: $('.progress-container'),
        currentTime: $('.current-time'),
        duration: $('.duration'),
        volume: $('.volume-slider'),
        cover: $('#playerCover'),
        title: $('#playerTitle'),
        artist: $('#playerArtist'),
        favoriteBtn: $('#playerFavoriteBtn')
    };

    /* ============== CHỐNG CLICK LAN ============== */
    els.player?.addEventListener('click', e => e.stopPropagation());

    /* ================= PLAYLIST ================= */
    function buildPlaylistFromDOM(sourceEl = null) {
        let container = null;
        if (sourceEl) container = sourceEl.closest('[data-playlist]');
        if (!container && sourceEl) container = sourceEl.closest('.song-grid, .song-list, .playlist, .songs-list') || sourceEl.parentElement;
        if (!container) container = document.querySelector('[data-playlist="main"]');

        let nodes = [];
        if (!container && sourceEl) nodes = [sourceEl];
        else if (container) nodes = Array.from(container.querySelectorAll('.song-card, .song-row, .chart-item'));

        window.playlist = nodes.map(el => ({
            id: String(el.dataset.songId),
            title: el.dataset.title || el.querySelector('.song-title')?.textContent || 'Không rõ',
            artist: el.dataset.artist || el.querySelector('.song-artist')?.textContent || 'Không rõ',
            audio: el.dataset.audio,
            image: el.dataset.image || '../assets/songs/images/default.jpg',
            isFavorite: el.dataset.isFavorite === '1'
        })).filter(s => s.id && s.audio);

        if (isShuffle) rebuildShuffleOrder();
        return window.playlist;
    }

    /* ================= PLAY SONG ================= */
    function playSong(index, autoplay = true) {
        if (isSwitchingSong) return;
        isSwitchingSong = true;

        if (!playlist.length) buildPlaylistFromDOM();
        if (!playlist.length) { isSwitchingSong = false; return; }

        if (index < 0) index = playlist.length - 1;
        if (index >= playlist.length) index = 0;

        if (index === currentIndex && isPlaying) { isSwitchingSong = false; return; }

        const song = playlist[index];
        if (!song) { isSwitchingSong = false; return; }

        audio.pause();
        audio.src = '';
        audio.currentTime = 0;

        // 🔄 RESET: Khi đổi bài, xóa trạng thái "đã đếm" của bài trước đi
        // Tuy nhiên KHÔNG xóa lastIncrementedTime để giữ debounce nếu user quay lại bài cũ ngay lập tức
        lastCountedSongId = null; 

        currentIndex = index;
        if (isShuffle) {
            if (!shuffleOrder.length || shuffleOrder.length !== playlist.length) rebuildShuffleOrder();
            shufflePos = shuffleOrder.indexOf(currentIndex);
        }

        try {
            const resolved = new URL(song.audio, location.href).href;
            audio.src = resolved;
        } catch (e) {
            audio.src = song.audio;
        }
        audio.load();
        updatePlayer(song);

        if (autoplay) {
            audio.play().then(() => {
                isPlaying = true;
                updatePlayPause(true);
            }).catch(err => {
                if (err && err.name !== 'AbortError') console.error(err);
            }).finally(() => {
                isSwitchingSong = false;
            });
        } else {
            isSwitchingSong = false;
        }
    }

    window.playSong = playSong;
    window.buildPlaylistFromDOM = buildPlaylistFromDOM;

    window.playSongFromRow = function (el) {
        buildPlaylistFromDOM(el);
        const id = el?.dataset?.songId;
        const idx = playlist.findIndex(s => String(s.id) === String(id));
        if (idx !== -1) playSong(idx, true);
        else {
            const song = {
                id: id, title: el?.dataset?.title, artist: el?.dataset?.artist,
                audio: el?.dataset?.audio, image: el?.dataset?.image
            };
            if (song.audio) { playlist.unshift(song); playSong(0, true); }
        }
    };
    window.playSongFromCard = window.playSongFromRow;

    /* ================= PLAYER UI ================= */
    function updatePlayer(song) {
        if (!song) return;
        els.title && (els.title.textContent = song.title);
        els.artist && (els.artist.textContent = song.artist);
        if (els.cover) {
            els.cover.src = song.image;
            els.cover.onerror = () => els.cover.src = '../assets/songs/images/default.jpg';
        }
        syncFavoriteUI(song.id, song.isFavorite);
        highlightCurrentSong();
    }

    function updatePlayPause(play) {
        const icon = els.playPause?.querySelector('i');
        if (icon) icon.className = play ? 'fas fa-pause' : 'fas fa-play';
    }

    function highlightCurrentSong() {
        $$('.song-card, .song-row, .chart-item').forEach(el => {
            el.classList.toggle('active', String(el.dataset.songId) === String(playlist[currentIndex]?.id));
        });
    }

    /* ================= FAVORITE ================= */
    function syncFavoriteUI(songId, isFav) {
        document.querySelectorAll(`[data-song-id="${songId}"]`).forEach(el => {
            el.dataset.isFavorite = isFav ? '1' : '0';
            const icon = el.querySelector('.favorite-btn i, .btn-favorite i, .btn-like i');
            if (icon) icon.className = isFav ? 'fas fa-heart text-danger' : 'far fa-heart';
        });
        if (els.favoriteBtn) {
            els.favoriteBtn.dataset.songId = songId;
            els.favoriteBtn.classList.toggle('active', isFav);
            const icon = els.favoriteBtn.querySelector('i');
            if (icon) icon.className = isFav ? 'fas fa-heart text-danger' : 'far fa-heart';
        }
    }

    window.toggleFavorite = (btn, songId, ev) => {
        try { ev?.stopPropagation(); } catch (e) { }
        const sid = String(songId ?? btn?.dataset?.songId ?? '');
        if (!sid) return;
        fetch('../ajax/favorite_toggle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'song_id=' + encodeURIComponent(sid)
        }).then(r => r.json()).then(data => {
            const added = data.action === 'added';
            const song = playlist.find(s => String(s.id) === String(sid));
            if (song) song.isFavorite = added;
            syncFavoriteUI(sid, added);
        }).catch(console.error);
    };

    /* ================= CONTROLS ================= */
    els.playPause?.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        if (!playlist.length || currentIndex === -1 || !audio.src) {
            buildPlaylistFromDOM();
            if (playlist.length) playSong(0, true);
            return;
        }
        if (isPlaying) { audio.pause(); isPlaying = false; }
        else { audio.play(); isPlaying = true; }
        updatePlayPause(isPlaying);
    });

    els.next?.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        if (!playlist.length) buildPlaylistFromDOM();
        if (!playlist.length) return;
        playSong((currentIndex + 1) % playlist.length, true);
    });

    els.prev?.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        if (!playlist.length) buildPlaylistFromDOM();
        if (!playlist.length) return;
        playSong((currentIndex - 1 + playlist.length) % playlist.length, true);
    });

    els.repeat?.addEventListener('click', () => {
        isRepeat = !isRepeat;
        audio.loop = isRepeat;
        els.repeat.classList.toggle('active', isRepeat);
    });

    function rebuildShuffleOrder() {
        shuffleOrder = playlist.map((_, i) => i);
        for (let i = shuffleOrder.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffleOrder[i], shuffleOrder[j]] = [shuffleOrder[j], shuffleOrder[i]];
        }
        shufflePos = shuffleOrder.indexOf(currentIndex);
    }

    els.shuffle?.addEventListener('click', () => {
        isShuffle = !isShuffle;
        els.shuffle.classList.toggle('active', isShuffle);
        if (isShuffle) rebuildShuffleOrder();
        else { shuffleOrder = []; shufflePos = -1; }
    });

    /* ================= PROGRESS & VIEW COUNT (FIXED) ================= */
    audio.addEventListener('timeupdate', () => {
        // Update UI
        if (audio.duration) {
            els.progress && (els.progress.style.width = (audio.currentTime / audio.duration) * 100 + '%');
            els.currentTime && (els.currentTime.textContent = formatTime(audio.currentTime));
            els.duration && (els.duration.textContent = formatTime(audio.duration));
        }

        // --- CORE VIEW COUNT LOGIC ---
        // Điều kiện 1: Thời gian nghe phải lớn hơn ngưỡng (ví dụ 10s)
        if (audio.currentTime > MIN_LISTEN_TIME) {
            const song = playlist[currentIndex];
            const currentId = song?.id ? String(song.id) : null;

            if (currentId) {
                // Điều kiện 2: Bài này chưa được đếm trong phiên nghe hiện tại
                if (lastCountedSongId !== currentId) {
                    
                    const now = Date.now();
                    const lastTime = lastIncrementedTime[currentId] || 0;

                    // Điều kiện 3: Không spam quá nhanh (debounce 60s)
                    if (now - lastTime >= INCREMENT_SKIP_WINDOW) {
                        
                        // ✅ ĐẠT ĐIỀU KIỆN TĂNG VIEW
                        console.log(`[View Count] Tăng view cho bài ID: ${currentId}`);
                        
                        // 1. Lock ngay lập tức để không chạy lại lần 2
                        lastCountedSongId = currentId; 
                        lastIncrementedTime[currentId] = now;

                        // 2. UI Update (Optimistic)
                        document.querySelectorAll(`[data-song-id="${currentId}"] .play-count-badge`).forEach(el => {
                            const num = parseInt(el.textContent.replace(/[^0-9]/g, '')) || 0;
                            el.textContent = (num + 1).toLocaleString();
                        });

                        // 3. Send Server Request
                        const params = new URLSearchParams();
                        params.append('song_id', currentId);
                        if (navigator.sendBeacon) {
                            navigator.sendBeacon('../ajax/increment_play.php', params);
                        } else {
                            fetch('../ajax/increment_play.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: params,
                                keepalive: true
                            }).catch(() => {});
                        }
                    } else {
                        // Đã đếm gần đây rồi -> đánh dấu đã xử lý để không check lại liên tục
                        lastCountedSongId = currentId;
                    }
                }
            }
        }
    });

    els.progressContainer?.addEventListener('click', e => {
        const rect = els.progressContainer.getBoundingClientRect();
        audio.currentTime = ((e.clientX - rect.left) / rect.width) * audio.duration;
    });

    audio.addEventListener('ended', () => {
        if (isRepeat) { playSong(currentIndex, true); return; }
        playSong((currentIndex + 1) % playlist.length, true); // Tuần tự
    });

    /* ================= OTHER UTILS ================= */
    function autoPlayFromDetail() {
        const detail = document.querySelector('.song-detail[data-song-id]');
        if (!detail) return;
        buildPlaylistFromDOM(detail);
        const idx = playlist.findIndex(s => s.id === detail.dataset.songId);
        if (idx !== -1) playSong(idx, true);
    }

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60);
        return `${m}:${s < 10 ? '0' + s : s}`;
    }

    /* DELEGATION */
    document.addEventListener('click', e => {
        if (e.target.closest('#musicPlayer, .play-pause-btn, .next-btn, .prev-btn, .favorite-btn, .btn-favorite')) return;
        const card = e.target.closest('.song-card, .song-row, .chart-item');
        if (!card) return;
        if (e.target.closest('a.song-card-link') && !e.target.closest('.play-overlay')) return;

        e.preventDefault();
        buildPlaylistFromDOM(card);
        const idx = playlist.findIndex(s => s.id === card.dataset.songId);
        if (idx !== -1) playSong(idx, true);
    });
    
    // Play Overlay Click
    document.addEventListener('click', e => {
        const overlay = e.target.closest('.play-overlay');
        if (!overlay) return;
        e.preventDefault(); e.stopPropagation();
        const card = overlay.closest('.song-card, .song-row');
        if (card) {
            buildPlaylistFromDOM(card);
            const idx = playlist.findIndex(s => s.id === card.dataset.songId);
            if (idx !== -1) playSong(idx, true);
        }
    });

    // Mobile Toggle
    const mobileToggle = document.getElementById('mobileSidebarToggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-visible');
        });
        document.addEventListener('click', e => {
            if (!e.target.closest('#sidebar') && !e.target.closest('#mobileSidebarToggle')) {
                document.body.classList.remove('sidebar-visible');
            }
        });
    }

    buildPlaylistFromDOM();
    autoPlayFromDetail();
})();