// Interaksi halaman user Portal Berita WinniCode

// Sembunyikan layar loading setelah semua aset termuat
window.addEventListener('load', () => {
    document.querySelector('[data-page-loader]')?.classList.add('is-hidden');
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

/** Tampilkan notifikasi kecil (toast) dari JavaScript. */
function showToast(message) {
    document.querySelectorAll('[data-toast]').forEach((t) => t.remove());
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.setAttribute('role', 'status');
    toast.dataset.toast = '';
    toast.innerHTML = '<i class="fa-solid fa-circle-check"></i><span></span>';
    toast.querySelector('span').textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

document.addEventListener('DOMContentLoaded', () => {
    /* ---------- Tag kategori: geser ke kanan ---------- */
    const track = document.querySelector('[data-tags-track]');
    document.querySelector('[data-tags-next]')?.addEventListener('click', () => {
        const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
        track.scrollTo({ left: atEnd ? 0 : track.scrollLeft + 194 * 2 });
    });

    /* ---------- Slider berita utama ---------- */
    const slider = document.querySelector('[data-slider]');
    if (slider) {
        const slides = slider.querySelectorAll('[data-slide]');
        const dots = slider.querySelectorAll('[data-slider-dot]');
        let current = 0;
        let timer;
        const show = (index) => {
            slides[current].classList.remove('is-active');
            dots[current]?.classList.remove('is-active');
            current = (index + slides.length) % slides.length;
            slides[current].classList.add('is-active');
            dots[current]?.classList.add('is-active');
        };
        const restart = () => {
            clearInterval(timer);
            if (slides.length > 1) timer = setInterval(() => show(current + 1), 6000);
        };
        slider.querySelector('[data-slider-prev]')?.addEventListener('click', () => { show(current - 1); restart(); });
        slider.querySelector('[data-slider-next]')?.addEventListener('click', () => { show(current + 1); restart(); });
        dots.forEach((dot) => dot.addEventListener('click', () => { show(Number(dot.dataset.sliderDot)); restart(); }));
        restart();
    }

    /* ---------- Carousel kartu (Popular / Related Posts) ---------- */
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const strip = carousel.querySelector('[data-carousel-track]');
        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');
        const update = () => {
            prev?.classList.toggle('is-disabled', strip.scrollLeft <= 2);
            next?.classList.toggle('is-disabled', strip.scrollLeft + strip.clientWidth >= strip.scrollWidth - 2);
        };
        const step = () => strip.clientWidth + 24;
        prev?.addEventListener('click', () => strip.scrollBy({ left: -step(), behavior: 'smooth' }));
        next?.addEventListener('click', () => strip.scrollBy({ left: step(), behavior: 'smooth' }));
        strip.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });

    /* ---------- Ganti liga pada klasemen ---------- */
    const switcher = document.querySelector('[data-league-switcher]');
    if (switcher) {
        const leagues = switcher.querySelectorAll('[data-league]');
        let index = 0;
        const go = (i) => {
            leagues[index].classList.remove('is-active');
            index = (i + leagues.length) % leagues.length;
            leagues[index].classList.add('is-active');
        };
        switcher.querySelector('[data-league-prev]')?.addEventListener('click', () => go(index - 1));
        switcher.querySelector('[data-league-next]')?.addEventListener('click', () => go(index + 1));
    }

    /* ---------- Dropdown (Categories, user, urutan pencarian) ---------- */
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach((dd) => {
        const toggle = dd.querySelector('[data-dropdown-toggle]');
        toggle?.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = !dd.classList.contains('is-open');
            dropdowns.forEach((d) => d.classList.remove('is-open'));
            dd.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', String(open));
        });
        dd.querySelector('.dropdown__menu')?.addEventListener('click', (e) => e.stopPropagation());
    });
    document.addEventListener('click', () => dropdowns.forEach((d) => d.classList.remove('is-open')));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') dropdowns.forEach((d) => d.classList.remove('is-open'));
    });

    /* ---------- Toast dari server ---------- */
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        toast.querySelector('[data-toast-close]')?.addEventListener('click', () => toast.remove());
        setTimeout(() => toast.remove(), 6000);
    });

    /* ---------- Konfirmasi sebelum hapus ---------- */
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    /* ---------- Bookmark (AJAX) ---------- */
    document.querySelectorAll('[data-bookmark]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            try {
                const res = await fetch(btn.dataset.bookmark, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                });
                if (res.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                const { bookmarked } = await res.json();
                // Samakan semua tombol bookmark untuk berita yang sama
                document.querySelectorAll(`[data-bookmark="${btn.dataset.bookmark}"]`).forEach((b) => {
                    b.classList.toggle('is-active', bookmarked);
                    b.setAttribute('aria-pressed', String(bookmarked));
                    const icon = b.querySelector('i');
                    icon.classList.toggle('fa-solid', bookmarked);
                    icon.classList.toggle('fa-regular', !bookmarked);
                });
                showToast(bookmarked ? 'Berita disimpan ke Marked.' : 'Berita dihapus dari Marked.');
            } finally {
                btn.disabled = false;
            }
        });
    });

    /* ---------- Bagikan artikel ---------- */
    document.querySelectorAll('[data-share]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const data = { title: document.title, url: location.href.split('#')[0] };
            if (navigator.share) {
                try { await navigator.share(data); } catch (e) { /* dibatalkan */ }
            } else if (navigator.clipboard) {
                await navigator.clipboard.writeText(data.url);
                showToast('Link berita disalin.');
            }
        });
    });

    /* ---------- Laporkan berita ---------- */
    document.querySelectorAll('[data-report-form]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            const reason = prompt('Alasan melaporkan berita ini:');
            if (!reason || !reason.trim()) {
                e.preventDefault();
                return;
            }
            form.querySelector('[data-report-reason]').value = reason.trim().slice(0, 255);
        });
    });

    /* ---------- Form komentar: rating & balas ---------- */
    document.querySelectorAll('[data-comment-form]').forEach((form) => {
        const input = form.querySelector('[data-rate-input]');
        const buttons = form.querySelectorAll('[data-rate]');
        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const same = input.value === btn.dataset.rate;
                buttons.forEach((b) => b.classList.toggle('is-active', !same && b === btn));
                input.value = same ? '' : btn.dataset.rate;
            });
        });

        const replyInput = form.querySelector('[data-reply-input]');
        const replyInfo = form.querySelector('[data-reply-info]');
        const textarea = form.querySelector('textarea');
        document.querySelectorAll('[data-reply]').forEach((btn) => {
            btn.addEventListener('click', () => {
                replyInput.value = btn.dataset.reply;
                replyInfo.hidden = false;
                replyInfo.querySelector('[data-reply-name]').textContent = btn.dataset.replyTo;
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                textarea.focus({ preventScroll: true });
            });
        });
        form.querySelector('[data-reply-cancel]')?.addEventListener('click', () => {
            replyInput.value = '';
            replyInfo.hidden = true;
        });
    });

    /* ---------- Dropzone unggah gambar: klik, drag & drop, paste ---------- */
    document.querySelectorAll('[data-dropzone]').forEach((zone) => {
        const input = zone.querySelector('[data-dropzone-input]');
        const preview = zone.querySelector('[data-dropzone-preview]');
        const content = zone.querySelector('[data-dropzone-content]');
        const showFile = (file, assign = true) => {
            if (!file || !file.type.startsWith('image/')) return;
            if (assign) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            }
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
            content.hidden = true;
        };
        input.addEventListener('change', () => showFile(input.files[0], false));
        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('is-dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('is-dragover'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('is-dragover');
            showFile(e.dataTransfer.files[0]);
        });
        zone.addEventListener('paste', (e) => showFile(e.clipboardData.files[0]));
    });

    /* ---------- Toolbar editor ---------- */
    document.querySelectorAll('.editor').forEach((editor) => {
        const textarea = editor.querySelector('textarea');
        const insert = (before, after = '', placeholder = '') => {
            const { selectionStart: s, selectionEnd: e, value } = textarea;
            const selected = value.slice(s, e) || placeholder;
            textarea.value = value.slice(0, s) + before + selected + after + value.slice(e);
            textarea.focus();
            textarea.selectionStart = s + before.length;
            textarea.selectionEnd = s + before.length + selected.length;
        };
        editor.querySelectorAll('[data-editor-action]').forEach((btn) => {
            btn.addEventListener('click', () => {
                switch (btn.dataset.editorAction) {
                    case 'image': {
                        const url = prompt('URL gambar (https://...)');
                        if (url) insert(`\n\n![gambar](${url.trim()})\n\n`);
                        break;
                    }
                    case 'link': {
                        const url = prompt('URL tautan (https://...)');
                        if (url) insert('[', `](${url.trim()})`, 'teks tautan');
                        break;
                    }
                    case 'color': {
                        const color = prompt('Kode warna (contoh: #f81539)', '#f81539');
                        if (color) insert(`[color=${color.trim()}]`, '[/color]', 'teks');
                        break;
                    }
                    case 'bold':
                        insert('**', '**', 'teks tebal');
                        break;
                    case 'heading':
                        insert('\n\n## ', '\n\n', 'Judul bagian');
                        break;
                }
            });
        });
    });

    /* ---------- Form kirim berita: tab Post / Video, tag, preview ---------- */
    const writeForm = document.querySelector('[data-write-form]');
    if (writeForm) {
        const typeInput = writeForm.querySelector('[data-post-type-input]');
        const videoField = writeForm.querySelector('[data-video-field]');
        document.querySelectorAll('[data-post-type]').forEach((tab) => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('[data-post-type]').forEach((t) => t.classList.toggle('is-active', t === tab));
                typeInput.value = tab.dataset.postType;
                videoField.hidden = tab.dataset.postType !== 'video';
            });
        });

        writeForm.querySelector('[data-tags-add]')?.addEventListener('click', () => {
            const tags = writeForm.querySelector('[data-tags-input]');
            if (tags.value.trim() && !tags.value.trim().endsWith(',')) tags.value = tags.value.trim() + ', ';
            tags.focus();
        });

        const dialog = document.querySelector('[data-preview-dialog]');
        writeForm.querySelector('[data-preview-open]')?.addEventListener('click', () => {
            dialog.querySelector('[data-preview-title]').textContent = writeForm.querySelector('[name="title"]').value || '(Tanpa judul)';
            const img = writeForm.querySelector('[name="image"]').closest('[data-dropzone]').querySelector('[data-dropzone-preview]');
            const previewImg = dialog.querySelector('[data-preview-image]');
            previewImg.hidden = img.hidden || !img.src;
            previewImg.src = img.src || '';
            const body = dialog.querySelector('[data-preview-content]');
            body.innerHTML = '';
            writeForm.querySelector('[name="content"]').value.split(/\n{2,}/).forEach((block) => {
                if (!block.trim()) return;
                const el = document.createElement(block.startsWith('## ') ? 'h2' : 'p');
                el.className = block.startsWith('## ') ? 'article__heading' : 'article__text';
                el.textContent = block.replace(/^## /, '');
                body.appendChild(el);
            });
            dialog.showModal();
        });
        dialog?.querySelector('[data-preview-close]')?.addEventListener('click', () => dialog.close());
    }

    /* ---------- Ganti tampilan grid / list di halaman kategori ---------- */
    document.querySelector('[data-view-toggle]')?.addEventListener('click', (e) => {
        const target = document.querySelector('[data-view-target]');
        const list = target?.classList.toggle('is-list');
        e.currentTarget.querySelector('i').className = list ? 'fa-solid fa-list' : 'fa-solid fa-grip';
    });
});
