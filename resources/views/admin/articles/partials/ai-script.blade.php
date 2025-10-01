<script>
  document.addEventListener('DOMContentLoaded', () => {
    const generateButton = document.getElementById('generate-with-ai');
    const keywordsInput = document.getElementById('ai_keywords');
    const statusElement = document.getElementById('generate-with-ai-status');

    if (!generateButton || !keywordsInput || !statusElement) {
      return;
    }

    const fields = {
      title: document.getElementById('title'),
      slug: document.getElementById('slug'),
      excerpt: document.getElementById('excerpt'),
      content: document.getElementById('content'),
      meta_title: document.getElementById('meta_title'),
      meta_description: document.getElementById('meta_description'),
    };

    const defaultButtonLabel = generateButton.innerHTML;

    generateButton.addEventListener('click', async () => {
      const keywords = keywordsInput.value.trim();

      if (!keywords) {
        statusElement.textContent = 'Masukkan minimal satu kata kunci untuk menggunakan AI.';
        statusElement.classList.remove('text-muted', 'text-success');
        statusElement.classList.add('text-danger');
        keywordsInput.focus();
        return;
      }

      const hasExistingContent = Object.values(fields).some((field) => field && field.value.trim().length > 0);

      if (hasExistingContent) {
        const confirmation = confirm('Konten yang sudah ada akan digantikan oleh hasil AI. Lanjutkan?');
        if (!confirmation) {
          return;
        }
      }

      generateButton.disabled = true;
      generateButton.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Meminta AI...';
      statusElement.textContent = 'Sedang membuat artikel berbasis kata kunci Anda...';
      statusElement.classList.remove('text-danger', 'text-success');
      statusElement.classList.add('text-muted');

      try {
        const response = await fetch('{{ route('admin.articles.generate-ai') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
          },
          body: JSON.stringify({ keywords }),
        });

        const payload = await response.json();

        if (!response.ok) {
          throw new Error(payload.message || 'Gagal menghasilkan artikel dengan AI.');
        }

        Object.entries(payload.data || {}).forEach(([key, value]) => {
          if (fields[key]) {
            fields[key].value = value ?? '';
          }
        });

        statusElement.textContent = 'Konten berhasil dihasilkan. Tinjau dan sesuaikan sebelum menerbitkan.';
        statusElement.classList.remove('text-muted', 'text-danger');
        statusElement.classList.add('text-success');
      } catch (error) {
        statusElement.textContent = error.message || 'Terjadi kesalahan saat berkomunikasi dengan AI.';
        statusElement.classList.remove('text-muted', 'text-success');
        statusElement.classList.add('text-danger');
      } finally {
        generateButton.disabled = false;
        generateButton.innerHTML = defaultButtonLabel;
      }
    });
  });
</script>
