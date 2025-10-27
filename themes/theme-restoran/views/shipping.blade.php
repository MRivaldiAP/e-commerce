@php
    $themeName = $theme ?? 'theme-restoran';
    \Illuminate\Support\Facades\View::addNamespace('themeRestoran', base_path('themes/' . $themeName . '/views'));
@endphp

@include('themeRestoran::components.shipping.page', [
    'theme' => $themeName,
    'shippingData' => $shippingData ?? [],
    'shippingConfig' => $shippingConfig ?? [],
    'checkoutTotals' => $checkoutTotals ?? [],
    'cartSummary' => $cartSummary ?? null,
])
