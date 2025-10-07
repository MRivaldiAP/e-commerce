<script>
(function(){
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const provincesEndpoint = '{{ url('/nusa/provinces') }}';
    const regencyEndpoint = '{{ url('/nusa/provinces') }}/';
    const districtEndpoint = '{{ url('/nusa/regencies') }}/';
    const villageEndpoint = '{{ url('/nusa/districts') }}/';
    const villageShowEndpoint = '{{ url('/nusa/villages') }}/';
    const ratesEndpoint = '{{ route('checkout.shipping.rates') }}';

    const provinceSelect = document.getElementById('province');
    const regencySelect = document.getElementById('regency');
    const districtSelect = document.getElementById('district');
    const villageSelect = document.getElementById('village');
    const postalInput = document.getElementById('postal_code');
    const methodsContainer = document.getElementById('shipping-methods');
    const shippingCostInput = document.getElementById('shipping_cost');
    const shippingCourierInput = document.getElementById('shipping_courier');
    const shippingServiceInput = document.getElementById('shipping_service');
    const shippingEtdInput = document.getElementById('shipping_etd');
    const provinceNameInput = document.getElementById('province_name');
    const regencyNameInput = document.getElementById('regency_name');
    const districtNameInput = document.getElementById('district_name');
    const villageNameInput = document.getElementById('village_name');
    const feedback = document.getElementById('shipping-feedback');
    const shippingCostDisplay = document.getElementById('shipping-cost-display');
    const grandTotalDisplay = document.getElementById('grand-total-display');
    const subtotal = {{ (float) $cartSummary['total_price'] }};

    const state = {
        province: '{{ old('province_code', $address['province_code'] ?? '') }}',
        regency: '{{ old('regency_code', $address['regency_code'] ?? '') }}',
        district: '{{ old('district_code', $address['district_code'] ?? '') }}',
        village: '{{ old('village_code', $address['village_code'] ?? '') }}',
        courier: '{{ $selectedCourier }}',
        service: '{{ $selectedService }}'
    };

    function setSelectOptions(select, items, selectedValue) {
        if (!select) return;
        const current = select.value;
        select.innerHTML = '<option value="">Pilih</option>';
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.code;
            option.textContent = item.name;
            if (selectedValue && selectedValue === item.code) {
                option.selected = true;
            }
            select.appendChild(option);
        });
        if (selectedValue) {
            select.value = selectedValue;
        } else if (current) {
            select.value = current;
        }
    }

    function fetchJSON(url) {
        return fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(response => {
                if (!response.ok) throw new Error('Gagal memuat data');
                return response.json();
            });
    }

    function updateHiddenNames() {
        const provinceText = provinceSelect.options[provinceSelect.selectedIndex]?.textContent || '';
        const regencyText = regencySelect.options[regencySelect.selectedIndex]?.textContent || '';
        const districtText = districtSelect.options[districtSelect.selectedIndex]?.textContent || '';
        const villageText = villageSelect.options[villageSelect.selectedIndex]?.textContent || '';
        if (provinceNameInput) provinceNameInput.value = provinceText;
        if (regencyNameInput) regencyNameInput.value = regencyText;
        if (districtNameInput) districtNameInput.value = districtText;
        if (villageNameInput) villageNameInput.value = villageText;
    }

    function showFeedback(message, type = 'info') {
        if (!feedback) return;
        feedback.textContent = message;
        feedback.className = 'alert alert-' + (type === 'error' ? 'error' : 'info');
        feedback.style.display = message ? 'block' : 'none';
    }

    function renderRates(rates) {
        if (!methodsContainer) return;
        methodsContainer.innerHTML = '';
        if (!rates.length) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = 'Layanan pengiriman belum tersedia untuk alamat ini.';
            methodsContainer.appendChild(empty);
            return;
        }

        rates.forEach(rate => {
            const wrapper = document.createElement('label');
            wrapper.className = 'shipping-method';
            if (rate.courier === state.courier && rate.service === state.service) {
                wrapper.classList.add('active');
            }

            const left = document.createElement('div');
            const title = document.createElement('div');
            title.style.fontWeight = '600';
            title.textContent = (rate.courier_name || rate.courier).toUpperCase() + ' - ' + rate.service;
            const desc = document.createElement('div');
            desc.style.fontSize = '0.9rem';
            desc.style.color = '#4f6f58';
            desc.textContent = rate.description || 'Pengiriman reguler';
            left.appendChild(title);
            left.appendChild(desc);

            const right = document.createElement('div');
            right.style.textAlign = 'right';
            right.innerHTML = '<strong>Rp ' + new Intl.NumberFormat('id-ID').format(rate.cost) + '</strong>' +
                (rate.etd ? '<div class="small">ETD: ' + rate.etd + '</div>' : '');

            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'shipping_option';
            radio.value = rate.courier + '|' + rate.service;
            radio.checked = rate.courier === state.courier && rate.service === state.service;

            radio.addEventListener('change', function(){
                state.courier = rate.courier;
                state.service = rate.service;
                shippingCourierInput.value = rate.courier;
                shippingServiceInput.value = rate.service;
                shippingCostInput.value = rate.cost;
                shippingEtdInput.value = rate.etd || '';
                if (shippingCostDisplay) {
                    shippingCostDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(rate.cost);
                }
                if (grandTotalDisplay) {
                    grandTotalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal + rate.cost);
                }
                methodsContainer.querySelectorAll('.shipping-method').forEach(el => el.classList.remove('active'));
                wrapper.classList.add('active');
            });

            wrapper.appendChild(radio);
            wrapper.appendChild(left);
            wrapper.appendChild(right);
            methodsContainer.appendChild(wrapper);
        });

        const selected = rates.find(rate => rate.courier === state.courier && rate.service === state.service) || rates[0];
        if (selected) {
            state.courier = selected.courier;
            state.service = selected.service;
            shippingCourierInput.value = selected.courier;
            shippingServiceInput.value = selected.service;
            shippingCostInput.value = selected.cost;
            shippingEtdInput.value = selected.etd || '';
            if (shippingCostDisplay) {
                shippingCostDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(selected.cost);
            }
            if (grandTotalDisplay) {
                grandTotalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal + selected.cost);
            }
            methodsContainer.querySelectorAll('.shipping-method').forEach(el => {
                if (el.querySelector('input')?.value === selected.courier + '|' + selected.service) {
                    el.classList.add('active');
                    el.querySelector('input').checked = true;
                }
            });
        }
    }

    function fetchRatesIfReady() {
        if (!provinceSelect.value || !regencySelect.value) {
            return;
        }
        const payload = {
            province_code: provinceSelect.value,
            regency_code: regencySelect.value,
            district_code: districtSelect.value,
            postal_code: postalInput.value
        };

        fetch(ratesEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload)
        })
        .then(res => {
            if (!res.ok) throw res;
            return res.json();
        })
        .then(data => {
            showFeedback('');
            renderRates(data.rates || []);
        })
        .catch(async error => {
            let message = 'Gagal memuat ongkir.';
            if (error.json) {
                const body = await error.json();
                message = body.message || message;
            }
            showFeedback(message, 'error');
            renderRates([]);
        });
    }

    function initRegency(provinceCode, selected) {
        if (!provinceCode) {
            setSelectOptions(regencySelect, [], '');
            setSelectOptions(districtSelect, [], '');
            setSelectOptions(villageSelect, [], '');
            return;
        }
        fetchJSON(regencyEndpoint + provinceCode + '/regencies')
            .then(response => {
                const items = response.data || response;
                setSelectOptions(regencySelect, items, selected);
                updateHiddenNames();
                if (selected) {
                    initDistrict(selected, state.district);
                } else {
                    setSelectOptions(districtSelect, [], '');
                    setSelectOptions(villageSelect, [], '');
                }
            });
    }

    function initDistrict(regencyCode, selected) {
        if (!regencyCode) {
            setSelectOptions(districtSelect, [], '');
            setSelectOptions(villageSelect, [], '');
            return;
        }
        fetchJSON(districtEndpoint + regencyCode + '/districts')
            .then(response => {
                const items = response.data || response;
                setSelectOptions(districtSelect, items, selected);
                updateHiddenNames();
                if (selected) {
                    initVillage(selected, state.village);
                } else {
                    setSelectOptions(villageSelect, [], '');
                }
            });
    }

    function initVillage(districtCode, selected) {
        if (!districtCode) {
            setSelectOptions(villageSelect, [], '');
            return;
        }
        fetchJSON(villageEndpoint + districtCode + '/villages')
            .then(response => {
                const items = response.data || response;
                setSelectOptions(villageSelect, items, selected);
                updateHiddenNames();
                if (selected) {
                    fetchVillage(selected);
                }
            });
    }

    function fetchVillage(code) {
        if (!code) return;
        fetchJSON(villageShowEndpoint + code)
            .then(response => {
                const village = response.data || response;
                if (postalInput && village.postal_code) {
                    postalInput.value = village.postal_code;
                }
                updateHiddenNames();
                fetchRatesIfReady();
            });
    }

    if (provinceSelect) {
        provinceSelect.addEventListener('change', function(){
            state.province = this.value;
            provinceNameInput.value = this.options[this.selectedIndex]?.textContent || '';
            initRegency(this.value, '');
            fetchRatesIfReady();
        });
    }
    if (regencySelect) {
        regencySelect.addEventListener('change', function(){
            state.regency = this.value;
            regencyNameInput.value = this.options[this.selectedIndex]?.textContent || '';
            initDistrict(this.value, '');
            fetchRatesIfReady();
        });
    }
    if (districtSelect) {
        districtSelect.addEventListener('change', function(){
            state.district = this.value;
            districtNameInput.value = this.options[this.selectedIndex]?.textContent || '';
            initVillage(this.value, '');
            fetchRatesIfReady();
        });
    }
    if (villageSelect) {
        villageSelect.addEventListener('change', function(){
            state.village = this.value;
            villageNameInput.value = this.options[this.selectedIndex]?.textContent || '';
            fetchVillage(this.value);
        });
    }
    if (postalInput) {
        postalInput.addEventListener('input', function(){
            fetchRatesIfReady();
        });
    }

    if (state.province) {
        initRegency(state.province, state.regency);
    }
    if (state.regency && state.district) {
        initDistrict(state.regency, state.district);
    }
    if (state.district && state.village) {
        initVillage(state.district, state.village);
    }
    if (!state.province) {
        updateHiddenNames();
    } else {
        fetchRatesIfReady();
    }
})();
</script>
