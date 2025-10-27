<div id="services" class="container-xxl py-5">
    <div class="container">
        <div class="row g-4">
            @foreach($services as $index => $svc)
            <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="{{ 0.1 + $index*0.2 }}s">
                <div class="service-item rounded pt-3">
                    <div class="p-4">
                        <i class="{{ $svc['icon'] ?? 'fa fa-3x fa-check text-primary mb-4' }}"></i>
                        <h5>{{ $svc['title'] ?? '' }}</h5>
                        <p>{{ $svc['text'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
