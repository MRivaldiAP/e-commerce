<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
    <style>
        .breadcrumb__text h1 {
            font-size: 46px;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .shoping__cart__table table {
            width: 100%;
        }

        .shoping__cart__table table thead {
            background: #f5f5f5;
        }

        .shoping__cart__table table thead th {
            font-size: 1rem;
            color: #1c1c1c;
            font-weight: 600;
            padding: 1rem 1.5rem;
            text-transform: uppercase;
        }

        .shoping__cart__table table tbody tr td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f2f2f2;
        }

        .cart__product__item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .cart__product__item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .quantity-control {
            display: inline-flex;
            align-items: center;
            border: 1px solid #ebebeb;
            border-radius: 999px;
            overflow: hidden;
        }

        .quantity-control button {
            background: #f5f5f5;
            border: none;
            padding: 0.5rem 0.9rem;
            font-size: 1rem;
            cursor: pointer;
            color: #1c1c1c;
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
            color: #d32f2f;
            font-size: 1.3rem;
        }

        .shoping__cart__total {
            display: flex;
            justify-content: space-between;
            font-size: 1.25rem;
            font-weight: 600;
            padding: 1rem 1.5rem;
            background: #f5f5f5;
            border-radius: 8px;
        }

        .cart-feedback {
            color: #7fad39;
            min-height: 1.5rem;
            text-align: right;
        }

        .cart-feedback.error {
            color: #d32f2f;
        }

        .cart-empty {
            text-align: center;
            padding: 3rem 1rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }

        .cart-empty p {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
@php
    use App\Support\Cart;

    $settings = $settings ?? collect();
    $cartSummary = $cartSummary ?? Cart::summary();
    $navLinks = [
        ['label' => 'Homepage', 'href' => url('/'), 'visible' => true],
        ['label' => 'Produk', 'href' => url('/produk'), 'visible' => true],
        ['label' => 'Keranjang', 'href' => url('/keranjang'), 'visible' => true],
    ];
    $title = $settings['title'] ?? 'Keranjang';
    $subtitle = $settings['subtitle'] ?? 'Periksa kembali daftar belanja Anda.';
    $emptyMessage = $settings['empty.message'] ?? 'Keranjang Anda masih kosong.';
    $emptyButton = $settings['empty.button'] ?? 'Belanja Sekarang';
    $shippingLabel = $settings['button.shipping'] ?? 'Lanjut ke Pengiriman';
    $paymentLabel = $settings['button.payment'] ?? 'Lanjut ke Pembayaran';
    $primaryButton = $shippingEnabled ? $shippingLabel : $paymentLabel;
@endphp

{!! view()->file(base_path('themes/theme-second/views/components/nav-menu.blade.php'), ['links' => $navLinks, 'cart' => $cartSummary])->render() !!}

<section class="breadcrumb-section set-bg" data-setbg="{{ asset('storage/themes/theme-second/img/breadcrumb.jpg') }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h1>{{ $title }}</h1>
                    <div class="breadcrumb__option">
                        <a href="{{ url('/') }}">Home</a>
                        <span>{{ $title }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="shoping-cart spad">
    <div class="container">
        <div class="row" id="cart-content" @class(['d-none' => empty($cartSummary['items'])])>
            <div class="col-lg-12">
                <div class="shoping__cart__table">
                    <table>
                        <thead>
                            <tr>
                                <th class="shoping__product">Produk</th>
                                <th>Harga</th>
                                <th>Kuantitas</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody data-cart-body>
                            @foreach($cartSummary['items'] as $item)
                                <tr data-product-id="{{ $item['product_id'] }}">
                                    <td class="shoping__cart__item">
                                        <div class="cart__product__item">
                                            <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}">
                                            <div class="cart__product__item__title">
                                                <h6><a href="{{ $item['product_url'] }}">{{ $item['name'] }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="shoping__cart__price">Rp <span data-item-price>{{ $item['price_formatted'] }}</span></td>
                                    <td class="shoping__cart__quantity">
                                        <div class="quantity-control" data-quantity-control>
                                            <button type="button" data-action="decrease">-</button>
                                            <input type="number" value="{{ $item['quantity'] }}" min="1">
                                            <button type="button" data-action="increase">+</button>
                                        </div>
                                    </td>
                                    <td class="shoping__cart__total">Rp <span data-item-subtotal>{{ $item['subtotal_formatted'] }}</span></td>
                                    <td class="shoping__cart__item__close"><button type="button" class="cart-remove" data-remove-item>&times;</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="cart-feedback" data-cart-status></div>
                <div class="shoping__cart__btns">
                    <a href="{{ url('/produk') }}" class="primary-btn cart-btn">Lanjut Belanja</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="shoping__checkout">
                    <div class="shoping__cart__total">
                        <span>Total</span>
                        <span>Rp <span data-cart-grand-total>{{ $cartSummary['total_price_formatted'] }}</span></span>
                    </div>
                    <button class="primary-btn" data-cart-action {{ empty($cartSummary['items']) ? 'disabled' : '' }}>{{ $primaryButton }}</button>
                </div>
            </div>
        </div>
        <div id="cart-empty" class="cart-empty" @class(['d-none' => !empty($cartSummary['items'])])>
            <h4>{{ $title }}</h4>
            <p>{{ $subtitle }}</p>
            <p>{{ $emptyMessage }}</p>
            <a href="{{ url('/produk') }}" class="primary-btn">{{ $emptyButton }}</a>
        </div>
    </div>
</section>

{!! view()->file(base_path('themes/theme-second/views/components/footer.blade.php'), ['settings' => $settings])->render() !!}

<script src="{{ asset('storage/themes/theme-second/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/mixitup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/main.js') }}"></script>
<script>
    (function(){
        const csrf = '{{ csrf_token() }}';
        const updateUrl = '{{ route('cart.items.update', ['product' => '__ID__']) }}';
        const destroyUrl = '{{ route('cart.items.destroy', ['product' => '__ID__']) }}';
        const cartBody = document.querySelector('[data-cart-body]');
        const cartContent = document.getElementById('cart-content');
        const emptyState = document.getElementById('cart-empty');
        const status = document.querySelector('[data-cart-status]');
        const totalDisplay = document.querySelector('[data-cart-grand-total]');
        const actionButton = document.querySelector('[data-cart-action]');

        function buildRow(item){
            const tr = document.createElement('tr');
            tr.dataset.productId = item.product_id;

            const productCell = document.createElement('td');
            productCell.className = 'shoping__cart__item';
            const wrapper = document.createElement('div');
            wrapper.className = 'cart__product__item';
            const img = document.createElement('img');
            img.src = item.image_url;
            img.alt = item.name;
            const info = document.createElement('div');
            info.className = 'cart__product__item__title';
            const title = document.createElement('h6');
            const link = document.createElement('a');
            link.href = item.product_url;
            link.textContent = item.name;
            title.appendChild(link);
            info.appendChild(title);
            wrapper.appendChild(img);
            wrapper.appendChild(info);
            productCell.appendChild(wrapper);

            const priceCell = document.createElement('td');
            priceCell.className = 'shoping__cart__price';
            priceCell.innerHTML = 'Rp <span data-item-price>' + item.price_formatted + '</span>';

            const quantityCell = document.createElement('td');
            quantityCell.className = 'shoping__cart__quantity';
            const control = document.createElement('div');
            control.className = 'quantity-control';
            control.setAttribute('data-quantity-control', 'true');
            control.innerHTML = '<button type="button" data-action="decrease">-</button>' +
                '<input type="number" value="' + item.quantity + '" min="1">' +
                '<button type="button" data-action="increase">+</button>';
            quantityCell.appendChild(control);

            const subtotalCell = document.createElement('td');
            subtotalCell.className = 'shoping__cart__total';
            subtotalCell.innerHTML = 'Rp <span data-item-subtotal>' + item.subtotal_formatted + '</span>';

            const actionCell = document.createElement('td');
            actionCell.className = 'shoping__cart__item__close';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'cart-remove';
            removeBtn.setAttribute('data-remove-item', 'true');
            removeBtn.textContent = '×';
            actionCell.appendChild(removeBtn);

            tr.append(productCell, priceCell, quantityCell, subtotalCell, actionCell);
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
            if(actionButton){
                actionButton.disabled = items.length === 0;
            }
            if(items.length === 0){
                cartContent?.classList.add('d-none');
                emptyState?.classList.remove('d-none');
            }else{
                cartContent?.classList.remove('d-none');
                emptyState?.classList.add('d-none');
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
    })();
</script>
</body>
</html>
