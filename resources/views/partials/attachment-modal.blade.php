<dialog id="attachment-modal" class="modal attachment-modal" aria-labelledby="attachment-title">
    <div class="modal-heading">
        <h2 id="attachment-title">Bukti transaksi</h2>
        <button type="button" class="close-modal" id="close-attachment" aria-label="Tutup lampiran">&times;</button>
    </div>
    <p id="attachment-status" class="muted" role="status">Memuat lampiran...</p>
    <div id="attachment-preview" class="attachment-preview" aria-busy="false"></div>
</dialog>
<script>
(() => {
    const dialog = document.getElementById('attachment-modal');
    const preview = document.getElementById('attachment-preview');
    const status = document.getElementById('attachment-status');
    let controller, objectUrl;

    function clearPreview() {
        controller?.abort();
        preview.replaceChildren();
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        preview.setAttribute('aria-busy', 'false');
    }

    document.addEventListener('click', async event => {
        const link = event.target.closest('[data-preview-attachment]');
        if (!link) return;
        event.preventDefault();
        clearPreview();
        controller = new AbortController();
        const request = controller;
        status.hidden = false;
        status.textContent = 'Memuat lampiran...';
        preview.setAttribute('aria-busy', 'true');
        dialog.showModal();
        document.body.classList.add('modal-open');
        try {
            const response = await fetch(link.href, {signal: request.signal, headers: {'Accept': 'application/pdf,image/*'}});
            if (!response.ok || response.redirected) throw new Error('Lampiran tidak tersedia.');
            const blob = await response.blob();
            if (request.signal.aborted) return;
            const image = ['image/jpeg', 'image/png'].includes(blob.type);
            if (!image && blob.type !== 'application/pdf') throw new Error('Format tidak didukung.');
            objectUrl = URL.createObjectURL(blob);
            const element = document.createElement(image ? 'img' : 'iframe');
            if (image) element.alt = 'Bukti pembelian transaksi';
            else element.title = 'Pratinjau bukti transaksi PDF';
            element.src = objectUrl;
            preview.append(element);
            status.hidden = true;
        } catch (error) {
            if (request.signal.aborted) return;
            status.textContent = 'Lampiran gagal dimuat. Tutup modal lalu coba kembali.';
        } finally {
            if (!request.signal.aborted) preview.setAttribute('aria-busy', 'false');
        }
    });
    document.getElementById('close-attachment').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
    });
    dialog.addEventListener('close', () => {
        clearPreview();
        if (!document.querySelector('dialog[open]')) document.body.classList.remove('modal-open');
    });
})();
</script>
