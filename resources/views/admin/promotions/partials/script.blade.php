<script>
  (function(){
    const typeInput = document.getElementById('promotion-type');
    const valueInput = document.getElementById('promotion-value');
    const label = document.querySelector('[data-discount-label]');

    function updateLabel(){
      if (!typeInput || !label) {
        return;
      }

      const type = typeInput.value;
      if (type === '{{ \App\Models\Promotion::TYPE_FIXED }}') {
        label.textContent = 'Rp';
        if (valueInput) {
          valueInput.step = '1';
        }
      } else {
        label.textContent = '%';
        if (valueInput) {
          valueInput.step = '0.01';
        }
      }
    }

    if (typeInput) {
      typeInput.addEventListener('change', updateLabel);
      updateLabel();
    }
  })();
</script>
