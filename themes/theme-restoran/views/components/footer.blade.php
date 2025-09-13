<footer class="bg-dark text-light pt-5 mt-5">
    <div class="container">
        <div class="text-center">
            <p>{{ $settings['footer.copyright'] ?? '© '.date('Y').' Restoran' }}@if(($settings['footer.privacy'] ?? '0') == '1') | <a href="#" class="text-light">Privacy Policy</a>@endif @if(($settings['footer.terms'] ?? '0') == '1') | <a href="#" class="text-light">Terms & Conditions</a>@endif</p>
        </div>
    </div>
</footer>
