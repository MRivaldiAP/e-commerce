<section id="contact" class="contact-form spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="contact__form__title">
                    <h2>{{ $settings['contact.heading'] ?? 'Leave Message' }}</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <form method="POST" action="#">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <input type="text" name="name" placeholder="Your name" required>
                        </div>
                        <div class="col-lg-12">
                            <input type="email" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="col-lg-12">
                            <input type="text" name="subject" placeholder="Subject" required>
                        </div>
                        <div class="col-lg-12 text-center">
                            <textarea name="message" placeholder="Your message" required></textarea>
                            <button type="submit" class="site-btn">SEND MESSAGE</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="map" style="height:100%;">
                    @if(!empty($contactMap))
                        {!! $contactMap !!}
                    @else
                        <div style="width:100%; height:100%; min-height:300px; background:#f2f2f2;"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
