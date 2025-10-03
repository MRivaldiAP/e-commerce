<script>
(function () {
  const button = document.getElementById('generate-with-ai');
  const statusElement = document.getElementById('ai-status');
  const keywordsInput = document.getElementById('ai_keywords');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
  const fields = {
    title: document.getElementById('title'),
    slug: document.getElementById('slug'),
    excerpt: document.getElementById('excerpt'),
    content: document.getElementById('content'),
    meta_title: document.getElementById('meta_title'),
    meta_description: document.getElementById('meta_description'),
  };

  if (!button || !keywordsInput) {
    return;
  }

  let controller = null;
  const originalButtonHtml = button.innerHTML;

  const setButtonLoading = (isLoading) => {
    button.disabled = isLoading;

    if (isLoading) {
      button.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Menghasilkan...';
    } else {
      button.innerHTML = originalButtonHtml;
    }
  };

  const showStatus = (type, message) => {
    if (!statusElement) {
      return;
    }

    statusElement.classList.remove('d-none', 'alert-info', 'alert-success', 'alert-danger', 'alert-warning');
    statusElement.classList.add(`alert-${type}`);
    statusElement.textContent = message;
  };

  const handleError = (error) => {
    const fallbackMessage = 'Terjadi kesalahan saat meminta AI. Silakan coba lagi.';
    const message = typeof error === 'string' ? error : error?.message ?? fallbackMessage;
    showStatus('danger', message);
    console.error('[AI Generator] Error:', error);
  };

  button.addEventListener('click', async () => {
    const keywords = keywordsInput.value.trim();

    if (!keywords) {
      showStatus('warning', 'Mohon isi kata kunci terlebih dahulu sebelum menggunakan AI.');
      keywordsInput.focus();
      return;
    }

    if (controller) {
      controller.abort();
    }

    controller = new AbortController();
    setButtonLoading(true);
    showStatus('info', 'Sedang menghasilkan artikel dengan bantuan AI. Mohon tunggu sebentar...');

    try {
      const response = await fetch(@json(route('admin.ai.articles.generate')), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ keywords }),
        signal: controller.signal,
      });

      let payload = null;

      try {
        payload = await response.json();
      } catch (jsonError) {
        if (!response.ok) {
          throw new Error('Gagal menghubungi layanan AI. Silakan periksa koneksi Anda.');
        }
      }

      if (!response.ok) {
        const validationErrors = payload?.errors ?? {};
        const keywordsErrors = Array.isArray(validationErrors.keywords) ? validationErrors.keywords.join(' ') : null;
        const message = keywordsErrors || payload?.message || 'Gagal menghasilkan artikel dari AI.';
        throw new Error(message);
      }

      const data = payload?.data ?? {};

      Object.entries(fields).forEach(([key, element]) => {
        if (!element || typeof data[key] !== 'string') {
          return;
        }

        element.value = data[key];
        element.dispatchEvent(new Event('input'));
        element.dispatchEvent(new Event('change'));
      });

      showStatus('success', 'Artikel berhasil dibuat oleh AI. Silakan tinjau dan sesuaikan sebelum menyimpan.');
    } catch (error) {
      if (error?.name === 'AbortError') {
        return;
      }

      handleError(error);
    } finally {
      setButtonLoading(false);
      controller = null;
    }
  });
})();
</script>
