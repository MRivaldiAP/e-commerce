<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <style>
        #cart {
            padding: 4rem 2rem;
        }

        #cart h1 {
            text-align: center;
            margin-bottom: 1rem;
        }

        #cart p.subtitle {
            text-align: center;
            color: #4a4a4a;
            margin-bottom: 2.5rem;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border-radius: 12px;
            overflow: hidden;
        }

        .cart-table th,
        .cart-table td {
            padding: 1.25rem;
            text-align: left;
        }

        .cart-table thead {
            background: var(--color-primary);
            color: #fff;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
        }

        .quantity-control {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--color-secondary);
            border-radius: 999px;
            overflow: hidden;
        }

        .quantity-control button {
            background: transparent;
            border: none;
            padding: 0.5rem 0.9rem;
            cursor: pointer;
            font-size: 1rem;
            color: var(--color-primary);
        }

        .quantity-control input {
            width: 60px;
            text-align: center;
            border: none;
            font-size: 1rem;
            padding: 0.5rem 0;
        }

        .cart-remove {
            background: none;
            border: none;
            color: #d32f2f;
            font-size: 1.25rem;
            cursor: pointer;
        }

        .cart-summary {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
        }

        .cart-summary .total-line {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .cart-actions {
            display: flex;
            gap: 1rem;
        }

        .cart-actions .cta {
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .cart-actions .cta.is-disabled {
            pointer-events: none;
            opacity: 0.5;
        }

        .cart-actions .cta.is-disabled {
            pointer-events: none;
            opacity: 0.6;
        }

        .cart-empty {
            text-align: center;
            padding: 3rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }

        .cart-empty p {
            margin-bottom: 1.5rem;
        }

        .cart-feedback {
            text-align: right;
            color: var(--color-primary);
            min-height: 1.5rem;
        }

        .cart-feedback.error {
            color: #d32f2f;
        }

        @media (max-width: 768px) {
            .cart-table thead {
                display: none;
            }

            .cart-table tr {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
                padding: 1rem;
                border-bottom: 1px solid #e0f2f1;
            }

            .cart-table td {
                padding: 0;
            }

            .cart-table td:last-child {
                justify-self: end;
            }

            .cart-summary {
                align-items: stretch;
            }

            .cart-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
@php
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $settings = $settings ?? collect();
    $cartSummary = $cartSummary ?? Cart::summary();
    $navigation = LayoutSettings::navigation($theme);
    $footerConfig = LayoutSettings::footer($theme);
    $title = $settings['title'] ?? 'Keranjang';
    $subtitle = $settings['subtitle'] ?? 'Periksa kembali item pesanan Anda sebelum melanjutkan.';
    $emptyMessage = $settings['empty.message'] ?? 'Keranjang Anda masih kosong.';
    $emptyButton = $settings['empty.button'] ?? 'Belanja Sekarang';
    $shippingLabel = $settings['button.shipping'] ?? 'Lanjut ke Pengiriman';
    $paymentLabel = $settings['button.payment'] ?? 'Lanjut ke Pembayaran';
    $primaryButton = $shippingEnabled ? $shippingLabel : $paymentLabel;
    $actionUrl = $shippingEnabled ? route('checkout.shipping') : route('checkout.payment');
    $hasItems = !empty($cartSummary['items']);
@endphp

{!! view()->file(base_path('themes/' . $theme . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

<section id="cart">
    <h1>{{ $title }}</h1>
    <p class="subtitle">{{ $subtitle }}</p>

    <div id="cart-content" @class(['d-none' => empty($cartSummary['items'])]) style="{{ $hasItems ? '' : 'display:none;' }}">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Kuantitas</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody data-cart-body>
                @foreach($cartSummary['items'] as $item)
                    <tr data-product-id="{{ $item['product_id'] }}">
                        <td>
                            <div class="cart-item">
                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}">
                                <div>
                                    <a href="{{ $item['product_url'] }}">{{ $item['name'] }}</a>
                                </div>
                            </div>
                        </td>
                        <td>Rp <span data-item-price>{{ $item['price_formatted'] }}</span></td>
                        <td>
                            <div class="quantity-control" data-quantity-control>
                                <button type="button" data-action="decrease">-</button>
                                <input type="number" value="{{ $item['quantity'] }}" min="1">
                                <button type="button" data-action="increase">+</button>
                            </div>
                        </td>
                        <td>Rp <span data-item-subtotal>{{ $item['subtotal_formatted'] }}</span></td>
                        <td class="text-right"><button type="button" class="cart-remove" data-remove-item>&times;</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="cart-summary">
            <div class="cart-feedback" data-cart-status></div>
            <div class="total-line">Total: Rp <span data-cart-grand-total>{{ $cartSummary['total_price_formatted'] }}</span></div>
                <div class="cart-actions">
                    <a href="{{ url('/produk') }}" class="cta" style="background: transparent; color: var(--color-primary); border:1px solid var(--color-primary);">Lanjut Belanja</a>
                    <a href="{{ $actionUrl }}" class="cta {{ empty($cartSummary['items']) ? 'is-disabled' : '' }}" data-cart-action aria-disabled="{{ empty($cartSummary['items']) ? 'true' : 'false' }}">{{ $primaryButton }}</a>
                </div>
        </div>
    </div>

    <div id="cart-empty" class="cart-empty" @class(['d-none' => !empty($cartSummary['items'])]) style="{{ $hasItems ? 'display:none;' : '' }}">
        <p>{{ $emptyMessage }}</p>
        <a href="{{ url('/produk') }}" class="cta">{{ $emptyButton }}</a>
    </div>
</section>

{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script>
    (function(){
        const csrf = '{{ csrf_token() }}';
        const updateUrl = '{{ route('cart.items.update', ['product' => '__ID__']) }}';
        const destroyUrl = '{{ route('cart.items.destroy', ['product' => '__ID__']) }}';
        const paymentUrl = '{{ route('checkout.payment') }}';
        const shippingUrl = '{{ route('checkout.shipping') }}';
        const shippingEnabled = {{ $shippingEnabled ? 'true' : 'false' }};
        const cartBody = document.querySelector('[data-cart-body]');
        const cartContent = document.getElementById('cart-content');
        const emptyState = document.getElementById('cart-empty');
        const status = document.querySelector('[data-cart-status]');
        const totalDisplay = document.querySelector('[data-cart-grand-total]');
        const actionButton = document.querySelector('[data-cart-action]');
        const initialHasItems = {{ $hasItems ? 'true' : 'false' }};

        function buildRow(item){
            const tr = document.createElement('tr');
            tr.dataset.productId = item.product_id;

            const infoCell = document.createElement('td');
            const wrapper = document.createElement('div');
            wrapper.className = 'cart-item';
            const img = document.createElement('img');
            img.src = item.image_url;
            img.alt = item.name;
            const info = document.createElement('div');
            const link = document.createElement('a');
            link.href = item.product_url;
            link.textContent = item.name;
            info.appendChild(link);
            wrapper.appendChild(img);
            wrapper.appendChild(info);
            infoCell.appendChild(wrapper);

            const priceCell = document.createElement('td');
            priceCell.innerHTML = 'Rp <span data-item-price>' + item.price_formatted + '</span>';

            const quantityCell = document.createElement('td');
            const control = document.createElement('div');
            control.className = 'quantity-control';
            control.setAttribute('data-quantity-control', 'true');
            control.innerHTML = '<button type="button" data-action="decrease">-</button>' +
                '<input type="number" value="' + item.quantity + '" min="1">' +
                '<button type="button" data-action="increase">+</button>';
            quantityCell.appendChild(control);

            const subtotalCell = document.createElement('td');
            subtotalCell.innerHTML = 'Rp <span data-item-subtotal>' + item.subtotal_formatted + '</span>';

            const actionCell = document.createElement('td');
            actionCell.className = 'text-right';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'cart-remove';
            removeBtn.setAttribute('data-remove-item', 'true');
            removeBtn.textContent = '×';
            actionCell.appendChild(removeBtn);

            tr.append(infoCell, priceCell, quantityCell, subtotalCell, actionCell);
            return tr;
        }

        function showStatus(message, isError = false){
            if(!status) return;
            status.textContent = message;
            status.classList.toggle('error', !!isError);
            if(message){
                status.classList.add('visible');
                setTimeout(() => status.classList.remove('visible'), 2500);
            }
        }

        function applySummary(summary){
            if(!cartBody || !summary) return;
            const items = summary.items || [];
            cartBody.innerHTML = '';
            items.forEach(item => cartBody.appendChild(buildRow(item)));
            if(totalDisplay){
                totalDisplay.textContent = summary.total_price_formatted || '0';
            }
            setActionButtonState(items.length > 0);
            if(items.length === 0){
                if(cartContent){
                    cartContent.classList.add('d-none');
                    cartContent.style.display = 'none';
                }
                if(emptyState){
                    emptyState.classList.remove('d-none');
                    emptyState.style.display = '';
                }
            }else{
                if(cartContent){
                    cartContent.classList.remove('d-none');
                    cartContent.style.display = '';
                }
                if(emptyState){
                    emptyState.classList.add('d-none');
                    emptyState.style.display = 'none';
                }
            }
            window.dispatchEvent(new CustomEvent('cart:updated', { detail: summary }));
        }

        function handleResponse(response){
            if(!response.ok){
                throw new Error('Request failed');
            }
            return response.json();
        }

        function updateQuantity(productId, quantity){
            fetch(updateUrl.replace('__ID__', productId), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ quantity: quantity })
            })
            .then(handleResponse)
            .then(data => {
                applySummary(data.summary);
                showStatus('Jumlah produk diperbarui.');
            })
            .catch(() => showStatus('Gagal memperbarui keranjang.', true));
        }

        function removeItem(productId){
            fetch(destroyUrl.replace('__ID__', productId), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                }
            })
            .then(handleResponse)
            .then(data => {
                applySummary(data.summary);
                showStatus('Produk dihapus dari keranjang.');
            })
            .catch(() => showStatus('Gagal menghapus produk.', true));
        }

        function setActionButtonState(isEnabled){
            if(!actionButton){
                return;
            }

            const targetUrl = shippingEnabled ? shippingUrl : paymentUrl;

            if(isEnabled){
                actionButton.classList.remove('is-disabled');
                actionButton.setAttribute('aria-disabled', 'false');
                actionButton.setAttribute('href', targetUrl);
            }else{
                actionButton.classList.add('is-disabled');
                actionButton.setAttribute('aria-disabled', 'true');
                actionButton.removeAttribute('href');
            }
        }

        if(cartBody){
            cartBody.addEventListener('click', function(event){
                const button = event.target.closest('button[data-action]');
                if(button){
                    const row = button.closest('tr[data-product-id]');
                    const input = row?.querySelector('input[type="number"]');
                    if(!row || !input) return;
                    let value = parseInt(input.value || '1', 10);
                    if(button.dataset.action === 'increase') value += 1;
                    if(button.dataset.action === 'decrease') value = Math.max(1, value - 1);
                    input.value = value;
                    updateQuantity(row.dataset.productId, value);
                    return;
                }
                const remove = event.target.closest('[data-remove-item]');
                if(remove){
                    const row = remove.closest('tr[data-product-id]');
                    if(!row) return;
                    removeItem(row.dataset.productId);
                }
            });

            cartBody.addEventListener('change', function(event){
                const input = event.target;
                if(input.matches('input[type="number"]')){
                    const row = input.closest('tr[data-product-id]');
                    if(!row) return;
                    let value = parseInt(input.value || '1', 10);
                    if(isNaN(value) || value < 1){
                        value = 1;
                        input.value = value;
                    }
                    updateQuantity(row.dataset.productId, value);
                }
            });
        }

        setActionButtonState(initialHasItems);

        if(actionButton){
            actionButton.addEventListener('click', function(event){
                if(actionButton.classList.contains('is-disabled')){
                    event.preventDefault();
                }
            });
        }
    })();
</script>

{!! view()->file(base_path('themes/' . $theme . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $theme,
])->render() !!}
</body>
</html>
