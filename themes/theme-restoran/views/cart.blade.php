@php
    $themeName = $theme ?? 'theme-restoran';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Keranjang</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('storage/themes/theme-restoran/img/favicon.ico') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('storage/themes/theme-restoran/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/css/style.css') }}" rel="stylesheet">
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/palette.blade.php'), ['theme' => $themeName])->render() !!}
    <style>
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table thead {
            background: var(--bs-dark);
            color: #fff;
        }

        .cart-table th,
        .cart-table td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
        }

        .cart-table tbody tr {
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .cart-item img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 12px;
        }

        .quantity-control {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 999px;
            overflow: hidden;
        }

        .quantity-control button {
            background: rgba(0,0,0,0.05);
            border: none;
            padding: 0.5rem 0.9rem;
            font-size: 1rem;
            color: var(--bs-dark);
        }

        .quantity-control input {
            width: 60px;
            text-align: center;
            border: none;
            font-size: 1rem;
        }

        .cart-remove {
            background: none;
            border: none;
            font-size: 1.4rem;
            color: #dc3545;
        }

        .cart-feedback {
            color: var(--bs-primary);
            min-height: 1.5rem;
            text-align: right;
        }

        .cart-feedback.error {
            color: #dc3545;
        }

        .promo-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--theme-accent, #feA116);
            color: #fff;
            padding: 0.15rem 0.65rem;
            border-radius: 999px;
            font-size: 0.7rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 0.35rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .price-stack {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .price-stack.align-end {
            align-items: flex-end;
        }

        .price-stack .price-original {
            text-decoration: line-through;
            color: rgba(0, 0, 0, 0.45);
            font-size: 0.85rem;
        }

        .price-stack .price-current {
            color: var(--theme-accent, #feA116);
            font-weight: 700;
        }

        .cart-total-meta {
            font-size: 0.85rem;
            color: rgba(0, 0, 0, 0.65);
        }

        .cart-empty {
            text-align: center;
            padding: 3rem 1.5rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        }

        .cart-empty p {
            margin-bottom: 1.5rem;
        }

        .btn.disabled,
        .btn[aria-disabled="true"] {
            pointer-events: none;
            opacity: 0.65;
        }
    </style>
</head>
<body>
@php
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;

    $settings = $settings ?? collect();
    $cartSummary = $cartSummary ?? Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $title = $settings['title'] ?? 'Keranjang';
    $subtitle = $settings['subtitle'] ?? 'Nikmati kemudahan berbelanja dengan memeriksa pesanan Anda di sini.';
    $emptyMessage = $settings['empty.message'] ?? 'Keranjang masih kosong, mulai belanja sekarang!';
    $emptyButton = $settings['empty.button'] ?? 'Belanja Sekarang';
    $shippingLabel = $settings['button.shipping'] ?? 'Lanjut ke Pengiriman';
    $paymentLabel = $settings['button.payment'] ?? 'Lanjut ke Pembayaran';
    $primaryButton = $shippingEnabled ? $shippingLabel : $paymentLabel;
    $actionUrl = $shippingEnabled ? route('checkout.shipping') : route('checkout.payment');
    $hasItems = !empty($cartSummary['items']);

    $heroVisible = ($settings['hero.visible'] ?? '1') === '1';
    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroImage = ThemeMedia::url($settings['hero.image'] ?? null);
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');
    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }
    $heroStyle = '';
    if ($heroImage) {
        if ($heroMaskEnabled) {
            $heroStyle = "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9)), url('{$heroImage}'); background-size: cover; background-position: center;";
        } else {
            $heroStyle = "background-image: url('{$heroImage}'); background-size: cover; background-position: center;";
        }
    } else {
        $heroStyle = $heroMaskEnabled
            ? 'background: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9));'
            : 'background: var(--theme-accent);';
    }
@endphp

<div class="container-xxl position-relative p-0">
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])->render() !!}
    @if($heroVisible)
    <div id="hero" class="{{ $heroClasses }}" style="{{ $heroStyle }}">
        <div class="container text-center my-5 pt-5 pb-4">
            <h1 class="display-3 text-white mb-3">{{ $title }}</h1>
            <p class="text-white-50 mb-0">{{ $subtitle }}</p>
        </div>
    </div>
    @endif
</div>

<div class="container py-5">
    <div id="cart-content" @class(['d-none' => empty($cartSummary['items'])]) style="{{ $hasItems ? '' : 'display:none;' }}">
        <div class="table-responsive mb-4">
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
                                        <h5 class="mb-1"><a href="{{ $item['product_url'] }}" class="text-dark">{{ $item['name'] }}</a></h5>
                                        @if(!empty($item['has_promo']) && !empty($item['promo_label']))
                                            <span class="promo-label">{{ $item['promo_label'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="price-stack align-end">
                                    @if(!empty($item['has_promo']))
                                        <span class="price-original">Rp {{ $item['original_price_formatted'] }}</span>
                                    @endif
                                    <span class="price-current" data-item-price>Rp {{ $item['price_formatted'] }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="quantity-control" data-quantity-control>
                                    <button type="button" data-action="decrease">-</button>
                                    <input type="number" value="{{ $item['quantity'] }}" min="1">
                                    <button type="button" data-action="increase">+</button>
                                </div>
                            </td>
                            <td>
                                <div class="price-stack align-end">
                                    @if(!empty($item['has_promo']))
                                        <span class="price-original">Rp {{ $item['original_subtotal_formatted'] }}</span>
                                    @endif
                                    <span class="price-current" data-item-subtotal>Rp {{ $item['subtotal_formatted'] }}</span>
                                </div>
                            </td>
                            <td class="text-end"><button type="button" class="cart-remove" data-remove-item>&times;</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <div class="cart-feedback" data-cart-status></div>
                <a href="{{ url('/produk') }}" class="btn btn-outline-primary">Lanjut Belanja</a>
            </div>
            <div class="col-lg-6 text-lg-end">
                <div class="d-inline-flex flex-column align-items-end gap-3">
                    <div class="cart-total-meta {{ ($cartSummary['discount_total'] ?? 0) > 0 ? '' : 'd-none' }}" data-cart-original-wrapper>
                        Harga Normal: Rp <span data-cart-original-total>{{ $cartSummary['original_total_formatted'] }}</span>
                    </div>
                    <div class="cart-total-meta text-success {{ ($cartSummary['discount_total'] ?? 0) > 0 ? '' : 'd-none' }}" data-cart-discount-wrapper>
                        Hemat Promo: -Rp <span data-cart-discount-total>{{ $cartSummary['discount_total_formatted'] }}</span>
                    </div>
                    <div class="fs-4 fw-bold">Total: Rp <span data-cart-grand-total>{{ $cartSummary['total_price_formatted'] }}</span></div>
                    <a href="{{ $actionUrl }}" class="btn btn-primary btn-lg {{ empty($cartSummary['items']) ? 'disabled' : '' }}" data-cart-action aria-disabled="{{ empty($cartSummary['items']) ? 'true' : 'false' }}" tabindex="{{ empty($cartSummary['items']) ? '-1' : '0' }}">{{ $primaryButton }}</a>
                </div>
            </div>
        </div>
    </div>
    <div id="cart-empty" class="cart-empty" @class(['d-none' => !empty($cartSummary['items'])]) style="{{ $hasItems ? 'display:none;' : '' }}">
        <h3 class="mb-3">{{ $title }}</h3>
        <p class="text-muted">{{ $subtitle }}</p>
        <p>{{ $emptyMessage }}</p>
        <a href="{{ url('/produk') }}" class="btn btn-primary">{{ $emptyButton }}</a>
    </div>
</div>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/wow/wow.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/counterup/counterup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/js/main.js') }}"></script>
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
        const originalWrapper = document.querySelector('[data-cart-original-wrapper]');
        const originalDisplay = document.querySelector('[data-cart-original-total]');
        const discountWrapper = document.querySelector('[data-cart-discount-wrapper]');
        const discountDisplay = document.querySelector('[data-cart-discount-total]');
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
            const title = document.createElement('h5');
            title.className = 'mb-1';
            const link = document.createElement('a');
            link.href = item.product_url;
            link.className = 'text-dark';
            link.textContent = item.name;
            title.appendChild(link);
            info.appendChild(title);
            if (item.has_promo && item.promo_label) {
                const badge = document.createElement('span');
                badge.className = 'promo-label';
                badge.textContent = item.promo_label;
                info.appendChild(badge);
            }
            wrapper.appendChild(img);
            wrapper.appendChild(info);
            infoCell.appendChild(wrapper);

            const priceCell = document.createElement('td');
            const priceStack = document.createElement('div');
            priceStack.className = 'price-stack align-end';
            if (item.has_promo && item.original_price_formatted) {
                const originalPrice = document.createElement('span');
                originalPrice.className = 'price-original';
                originalPrice.textContent = 'Rp ' + item.original_price_formatted;
                priceStack.appendChild(originalPrice);
            }
            const currentPrice = document.createElement('span');
            currentPrice.className = 'price-current';
            currentPrice.textContent = 'Rp ' + item.price_formatted;
            currentPrice.setAttribute('data-item-price', 'true');
            priceStack.appendChild(currentPrice);
            priceCell.appendChild(priceStack);

            const quantityCell = document.createElement('td');
            const control = document.createElement('div');
            control.className = 'quantity-control';
            control.setAttribute('data-quantity-control', 'true');
            control.innerHTML = '<button type="button" data-action="decrease">-</button>' +
                '<input type="number" value="' + item.quantity + '" min="1">' +
                '<button type="button" data-action="increase">+</button>';
            quantityCell.appendChild(control);

            const subtotalCell = document.createElement('td');
            const subtotalStack = document.createElement('div');
            subtotalStack.className = 'price-stack align-end';
            if (item.has_promo && item.original_subtotal_formatted) {
                const originalSubtotal = document.createElement('span');
                originalSubtotal.className = 'price-original';
                originalSubtotal.textContent = 'Rp ' + item.original_subtotal_formatted;
                subtotalStack.appendChild(originalSubtotal);
            }
            const currentSubtotal = document.createElement('span');
            currentSubtotal.className = 'price-current';
            currentSubtotal.textContent = 'Rp ' + item.subtotal_formatted;
            currentSubtotal.setAttribute('data-item-subtotal', 'true');
            subtotalStack.appendChild(currentSubtotal);
            subtotalCell.appendChild(subtotalStack);

            const actionCell = document.createElement('td');
            actionCell.className = 'text-end';
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
                setTimeout(() => status.textContent = '', 2500);
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
            if (originalDisplay) {
                originalDisplay.textContent = summary.original_total_formatted || '0';
            }
            if (discountDisplay) {
                discountDisplay.textContent = summary.discount_total_formatted || '0';
            }
            if (originalWrapper) {
                originalWrapper.classList.toggle('d-none', (summary.discount_total || 0) <= 0);
            }
            if (discountWrapper) {
                discountWrapper.classList.toggle('d-none', (summary.discount_total || 0) <= 0);
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
                throw response;
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
                showStatus('Jumlah diperbarui.');
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
                actionButton.classList.remove('disabled');
                actionButton.setAttribute('aria-disabled', 'false');
                actionButton.setAttribute('href', targetUrl);
                actionButton.setAttribute('tabindex', '0');
            }else{
                actionButton.classList.add('disabled');
                actionButton.setAttribute('aria-disabled', 'true');
                actionButton.removeAttribute('href');
                actionButton.setAttribute('tabindex', '-1');
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
                if(actionButton.classList.contains('disabled')){
                    event.preventDefault();
                }
            });
        }
    })();
</script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
