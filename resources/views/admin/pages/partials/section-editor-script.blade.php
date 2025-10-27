<script>
(() => {
  const container = document.querySelector('[data-section-editor="true"]');
  if (!container) {
    return;
  }

  const updateUrl = container.getAttribute('data-update');
  if (!updateUrl) {
    return;
  }

  const csrfToken = container.getAttribute('data-csrf') ||
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const previewFrame = document.getElementById('page-preview');

  const parseJsonArray = (value) => {
    if (!value) {
      return [];
    }

    try {
      const parsed = JSON.parse(value);
      if (!Array.isArray(parsed)) {
        return [];
      }

      return parsed.filter((item, index, arr) => typeof item === 'string' && arr.indexOf(item) === index);
    } catch (error) {
      return [];
    }
  };

  const parseJsonObject = (value) => {
    if (!value) {
      return {};
    }

    try {
      const parsed = JSON.parse(value);
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (error) {
      return {};
    }
  };

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

  const escapeAttr = (value) => escapeHtml(value).replace(/"/g, '&quot;');

  let composition = parseJsonArray(container.getAttribute('data-composition'));
  const defaultComposition = parseJsonArray(container.getAttribute('data-default-composition'));
  const labelMap = parseJsonObject(container.getAttribute('data-labels'));

  container.setAttribute('data-composition', JSON.stringify(composition));

  let isUpdatingSections = false;

  const debounce = (fn, delay = 300) => {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(null, args), delay);
    };
  };

  const refreshPreview = debounce(() => {
    if (!previewFrame) {
      return;
    }

    try {
      const frameWindow = previewFrame.contentWindow;
      if (frameWindow && frameWindow.location) {
        const current = frameWindow.location.href;
        frameWindow.location.replace(current);
      } else {
        previewFrame.src = previewFrame.src;
      }
    } catch (error) {
      previewFrame.src = previewFrame.src;
    }
  }, 400);

  const postValue = (key, value) => {
    const formData = new FormData();
    formData.append('key', key);

    if (value instanceof Blob) {
      formData.append('value', value);
    } else if (value !== undefined && value !== null) {
      formData.append('value', value);
    } else {
      formData.append('value', '');
    }

    return fetch(updateUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
      },
      body: formData,
    }).then((response) => {
      if (!response.ok) {
        throw new Error('Request failed with status ' + response.status);
      }

      return response;
    });
  };

  const resetButton = container.querySelector('[data-reset-sections]');
  const addSelect = container.querySelector('[data-section-picker]');
  const addButton = container.querySelector('[data-add-section]');

  const isDefaultOrder = () => {
    if (defaultComposition.length !== composition.length) {
      return false;
    }

    return defaultComposition.every((item, index) => item === composition[index]);
  };

  const updateResetState = () => {
    if (!resetButton) {
      return;
    }

    if (!defaultComposition.length) {
      resetButton.disabled = true;
      return;
    }

    resetButton.disabled = isUpdatingSections || isDefaultOrder();
  };

  const toggleAddState = () => {
    if (!addButton || !addSelect) {
      return;
    }

    addButton.disabled = isUpdatingSections || !addSelect.value;
  };

  const persistComposition = (nextOrder) => {
    const previous = composition.slice();
    const next = Array.isArray(nextOrder) ? nextOrder.filter((item, index, arr) => arr.indexOf(item) === index) : [];

    composition = next;
    container.setAttribute('data-composition', JSON.stringify(composition));
    isUpdatingSections = true;
    updateResetState();
    toggleAddState();

    postValue('__sections', JSON.stringify(composition))
      .then(() => {
        window.location.reload();
      })
      .catch((error) => {
        console.error(error);
        composition = previous;
        container.setAttribute('data-composition', JSON.stringify(composition));
        isUpdatingSections = false;
        updateResetState();
        toggleAddState();
        window.alert('Gagal memperbarui susunan seksi. Silakan coba lagi.');
      });
  };

  if (resetButton) {
    resetButton.addEventListener('click', () => {
      if (resetButton.disabled || isUpdatingSections) {
        return;
      }

      persistComposition(defaultComposition.slice());
    });
  }

  if (addSelect && addButton) {
    addSelect.addEventListener('change', toggleAddState);
    addButton.addEventListener('click', () => {
      if (isUpdatingSections) {
        return;
      }

      const selected = addSelect.value;
      if (!selected || composition.includes(selected)) {
        return;
      }

      persistComposition([...composition, selected]);
    });

    toggleAddState();
  }

  container.querySelectorAll('[data-remove-section]').forEach((button) => {
    button.addEventListener('click', () => {
      if (isUpdatingSections) {
        return;
      }

      const key = button.getAttribute('data-remove-section');
      if (!key) {
        return;
      }

      const label = labelMap[key] || key;
      if (!window.confirm(`Hapus seksi "${label}" dari halaman?`)) {
        return;
      }

      persistComposition(composition.filter((section) => section !== key));
    });
  });

  updateResetState();

  container.querySelectorAll('[data-key]').forEach((input) => {
    input.addEventListener('change', function () {
      const fieldKey = this.getAttribute('data-key');
      if (!fieldKey) {
        return;
      }

      if (this.type === 'file') {
        if (!this.files || !this.files[0]) {
          return;
        }

        postValue(fieldKey, this.files[0])
          .then(() => refreshPreview())
          .catch((error) => {
            console.error(error);
            window.alert('Gagal menyimpan perubahan. Silakan coba lagi.');
          });

        return;
      }

      const value = this.type === 'checkbox' ? (this.checked ? 1 : 0) : this.value;
      postValue(fieldKey, value)
        .then(() => refreshPreview())
        .catch((error) => {
          console.error(error);
          window.alert('Gagal menyimpan perubahan. Silakan coba lagi.');
        });
    });
  });

  container.querySelectorAll('[data-repeatable]').forEach((wrapper) => {
    const itemsContainer = wrapper.querySelector('.repeatable-items');
    const hidden = wrapper.querySelector('[data-key]');
    const fieldsDefinition = wrapper.getAttribute('data-fields');

    if (!itemsContainer || !hidden) {
      return;
    }

    let fields = [];
    try {
      const parsed = JSON.parse(fieldsDefinition || '[]');
      if (Array.isArray(parsed)) {
        fields = parsed;
      }
    } catch (error) {
      fields = [];
    }

    const renderField = (field, value) => {
      if (!field || typeof field !== 'object') {
        return '';
      }

      const name = field.name || '';
      if (!name) {
        return '';
      }

      const placeholder = field.placeholder || '';
      const type = field.type || 'text';
      const currentValue = value ?? '';

      if (type === 'textarea') {
        return `<textarea class="form-control mb-2" data-field="${escapeAttr(name)}" placeholder="${escapeAttr(placeholder)}">${escapeHtml(currentValue)}</textarea>`;
      }

      if (type === 'select') {
        const options = Array.isArray(field.options) ? field.options : [];
        let optionsHtml = `<option value="">${escapeHtml(placeholder || 'Pilih opsi')}</option>`;
        let hasMatchingValue = currentValue === '';

        options.forEach((option) => {
          if (option === null || option === undefined) {
            return;
          }

          let optionValue;
          let optionLabel;

          if (typeof option === 'object') {
            optionValue = option.value;
            optionLabel = option.label ?? option.value;
          } else {
            optionValue = option;
            optionLabel = option;
          }

          if (optionValue === undefined) {
            return;
          }

          const selected = String(optionValue) === String(currentValue);
          if (selected) {
            hasMatchingValue = true;
          }

          optionsHtml += `<option value="${escapeAttr(optionValue)}"${selected ? ' selected' : ''}>${escapeHtml(optionLabel)}</option>`;
        });

        if (!hasMatchingValue && currentValue !== '' && currentValue !== null && currentValue !== undefined) {
          optionsHtml += `<option value="${escapeAttr(currentValue)}" selected>${escapeHtml(currentValue)}</option>`;
        }

        return `<select class="form-control mb-2" data-field="${escapeAttr(name)}">${optionsHtml}</select>`;
      }

      return `<input type="text" class="form-control mb-2" data-field="${escapeAttr(name)}" placeholder="${escapeAttr(placeholder)}" value="${escapeAttr(currentValue)}">`;
    };

    const buildItem = (data = {}) => {
      const item = document.createElement('div');
      item.className = 'repeatable-item mb-3 border rounded p-3';

      let content = '';
      fields.forEach((field) => {
        content += renderField(field, data[field?.name]);
      });
      content += '<div class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-item">Hapus</button></div>';

      item.innerHTML = content;
      return item;
    };

    const sync = ({ notify = true } = {}) => {
      const data = [];
      itemsContainer.querySelectorAll('.repeatable-item').forEach((item) => {
        const row = {};
        item.querySelectorAll('[data-field]').forEach((input) => {
          row[input.getAttribute('data-field')] = input.value;
        });
        data.push(row);
      });

      hidden.value = JSON.stringify(data);
      if (notify) {
        hidden.dispatchEvent(new Event('change'));
      }
    };

    const addButton = wrapper.querySelector('.add-item');
    if (addButton) {
      addButton.addEventListener('click', () => {
        itemsContainer.appendChild(buildItem());
      });
    }

    itemsContainer.addEventListener('input', () => sync());
    itemsContainer.addEventListener('change', () => sync());
    itemsContainer.addEventListener('click', (event) => {
      if (event.target.classList.contains('remove-item')) {
        event.target.closest('.repeatable-item')?.remove();
        sync();
      }
    });

    let initialItems = [];
    try {
      const parsed = JSON.parse(hidden.value || '[]');
      if (Array.isArray(parsed)) {
        initialItems = parsed;
      }
    } catch (error) {
      initialItems = [];
    }

    if (initialItems.length) {
      initialItems.forEach((item) => {
        itemsContainer.appendChild(buildItem(item || {}));
      });
      sync({ notify: false });
    }
  });

  const highlightSection = (sectionId, active) => {
    if (!sectionId || !previewFrame) {
      return;
    }

    try {
      const frameWindow = previewFrame.contentWindow;
      if (!frameWindow || !frameWindow.document) {
        return;
      }

      const target = frameWindow.document.getElementById(sectionId);
      if (!target) {
        return;
      }

      target.style.outline = active ? '2px dashed #ff9800' : '';
      if (active) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    } catch (error) {
      // ignore cross-frame access issues
    }
  };

  container.querySelectorAll('.card[data-section]').forEach((card) => {
    card.addEventListener('mouseenter', () => {
      const sectionId = card.getAttribute('data-section');
      highlightSection(sectionId, true);
    });
    card.addEventListener('mouseleave', () => {
      const sectionId = card.getAttribute('data-section');
      highlightSection(sectionId, false);
    });
  });
})();
</script>
