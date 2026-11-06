/* Interaksi dashboard admin (jQuery + Bootstrap 4 dari AdminLTE) */
$(function () {
    /* ---------- Konfirmasi sebelum submit form [data-confirm] ---------- */
    let pendingForm = null;
    $(document).on('submit', 'form[data-confirm]', function (event) {
        if (this.dataset.confirmed === '1') return;
        event.preventDefault();
        pendingForm = this;
        $('#confirmModalText').text(this.dataset.confirm);
        $('#confirmModal').modal('show');
    });
    $('#confirmModalOk').on('click', function () {
        if (!pendingForm) return;
        pendingForm.dataset.confirmed = '1';
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        pendingForm.submit();
    });
    $('#confirmModal').on('hidden.bs.modal', function () {
        if (pendingForm && pendingForm.dataset.confirmed !== '1') pendingForm = null;
    });

    /* ---------- Checkbox pilih banyak (hapus massal) ---------- */
    const $items = $('[data-check-item]');
    const syncBulk = function () {
        const count = $items.filter(':checked').length;
        $('[data-bulk-count]').text(count);
        $('[data-bulk-button]').prop('disabled', count === 0);
        $('[data-check-all]').prop('checked', count > 0 && count === $items.length)
            .prop('indeterminate', count > 0 && count < $items.length);
    };
    $('[data-check-all]').on('change', function () {
        $items.prop('checked', this.checked);
        syncBulk();
    });
    $items.on('change', syncBulk);

    /* ---------- Nama file & preview gambar pada input file ---------- */
    $('.custom-file-input').on('change', function () {
        const file = this.files[0];
        $(this).next('.custom-file-label').text(file ? file.name : 'Pilih gambar...');
        const $preview = $('[data-preview-for="' + this.id + '"]');
        if (file && $preview.length && file.type.startsWith('image/')) {
            $preview.attr('src', URL.createObjectURL(file)).removeClass('d-none');
        }
    });

    /* ---------- Status draft: sembunyikan tanggal terbit ---------- */
    const syncStatus = function () {
        const isDraft = $('input[name="status"]:checked').val() === 'draft';
        $('[data-publish-date]').toggle(!isDraft);
    };
    $('input[name="status"]').on('change', syncStatus);
    syncStatus();

    /* ---------- Toolbar format isi berita ---------- */
    const textarea = document.getElementById('content');
    const wrap = function (before, after, placeholder) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selected = textarea.value.slice(start, end) || placeholder;
        textarea.setRangeText(before + selected + after, start, end, 'end');
        textarea.focus();
    };
    $('[data-format]').on('click', function () {
        if (!textarea) return;
        switch (this.dataset.format) {
            case 'heading': wrap('\n\n## ', '\n\n', 'Subjudul'); break;
            case 'bold': wrap('**', '**', 'teks tebal'); break;
            case 'center': wrap('[center]', '[/center]', 'teks'); break;
            case 'link': {
                const url = prompt('URL link:', 'https://');
                if (url) wrap('[', '](' + url + ')', 'teks link');
                break;
            }
            case 'image': {
                const url = prompt('URL gambar:', 'https://');
                if (url) wrap('\n\n![', '](' + url + ')\n\n', 'keterangan gambar');
                break;
            }
            case 'color': {
                const color = prompt('Warna (hex):', '#f81539');
                if (color && /^#[0-9a-f]{3,6}$/i.test(color)) wrap('[color=' + color + ']', '[/color]', 'teks');
                break;
            }
        }
    });

    /* Tutup otomatis notifikasi sukses */
    setTimeout(function () { $('.alert-success').alert('close'); }, 5000);
});
