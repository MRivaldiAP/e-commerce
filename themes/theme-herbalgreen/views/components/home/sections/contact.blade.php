<section id="contact" class="contact">
    <h2>{{ $settings['contact.heading'] ?? 'Contact' }}</h2>
    <form>
        <input type="text" placeholder="Name" required>
        <input type="email" placeholder="Email" required>
        <textarea placeholder="Message" required></textarea>
        <button type="submit">Send</button>
    </form>
    @if(!empty($settings['contact.map']))
        <div class="map-container">{!! $settings['contact.map'] !!}</div>
    @endif
</section>
