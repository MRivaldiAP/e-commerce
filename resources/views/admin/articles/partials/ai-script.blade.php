<script>
  (function () {
    const button = document.getElementById('generate-with-ai');
    if (!button) {
      return;
    }

    const keywordsInput = document.getElementById('ai_keywords');
    const statusEl = document.getElementById('ai-status');
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const excerptInput = document.getElementById('excerpt');
    const contentInput = document.getElementById('content');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescriptionInput = document.getElementById('meta_description');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value;
    const originalHtml = button.innerHTML;

    const showStatus = (type, message) => {
      if (!statusEl) return;
      statusEl.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
      statusEl.classList.add('alert', `alert-${type}`);
      statusEl.textContent = message;
    };

    button.addEventListener('click', async () => {
      const keywords = keywordsInput.value.trim();
      if (!keywords) {
        showStatus('warning', 'Mohon isi kata kunci yang ingin digunakan.');
        keywordsInput.focus();
        return;
      }

      button.disabled = true;
      button.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Menghasilkan...';
      showStatus('info', 'Meminta AI menyiapkan artikel Anda...');

      try {
        const response = await fetch('{{ route('admin.ai.articles.generate') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ keywords }),
        });

        const payload = await response.json();

        if (!response.ok) {
          const message = payload?.message || 'Gagal menghasilkan artikel.';
          showStatus('danger', message);
          return;
        }

        const data = payload.data || {};

        if (titleInput) titleInput.value = data.title || '';
        if (slugInput) slugInput.value = data.slug || '';
        if (excerptInput) excerptInput.value = data.excerpt || '';
        if (contentInput) contentInput.value = data.content || '';
        if (metaTitleInput) metaTitleInput.value = data.meta_title || '';
        if (metaDescriptionInput) metaDescriptionInput.value = data.meta_description || '';

        showStatus('success', 'Artikel berhasil dihasilkan. Silakan tinjau sebelum dipublikasikan.');
      } catch (error) {
        console.error(error);
        showStatus('danger', 'Terjadi kesalahan jaringan saat menghubungi AI.');
      } finally {
        button.disabled = false;
        button.innerHTML = originalHtml;
      }
    });
  })();
</script>
