<script>
  (function(){
    const typeInput = document.getElementById('promotion-type');
    const valueInput = document.getElementById('promotion-value');
    const label = document.querySelector('[data-discount-label]');
    const audienceInput = document.getElementById('promotion-audience');
    const usersGroup = document.getElementById('promotion-users-group');

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

    function updateAudienceVisibility(){
      if (!audienceInput || !usersGroup) {
        return;
      }

      if (audienceInput.value === '{{ \App\Models\Promotion::AUDIENCE_SELECTED }}') {
        usersGroup.style.display = '';
        usersGroup.setAttribute('aria-hidden', 'false');
      } else {
        usersGroup.style.display = 'none';
        usersGroup.setAttribute('aria-hidden', 'true');
      }
    }

    if (typeInput) {
      typeInput.addEventListener('change', updateLabel);
      updateLabel();
    }

    if (audienceInput) {
      audienceInput.addEventListener('change', updateAudienceVisibility);
      updateAudienceVisibility();
    }
  })();
</script>
