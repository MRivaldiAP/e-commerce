@php
    use App\Support\ThemeMedia;

    $visible = $team['visible'] ?? false;
    $heading = $team['heading'] ?? 'Tim Kami';
    $description = $team['description'] ?? null;
    $members = is_array($team['members'] ?? null) ? $team['members'] : [];
@endphp
@if($visible)
<div id="team" class="container-xxl pt-5 pb-3">
    <div class="container">
        <div class="text-center">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">{{ $heading }}</h5>
            @if(!empty($description))
            <p class="text-muted mb-5">{{ $description }}</p>
            @endif
        </div>
        <div class="row g-4">
            @foreach($members as $index => $member)
            @php
                $photo = ThemeMedia::url($member['photo'] ?? null);
                $fallback = asset('storage/themes/theme-restoran/img/team-' . (($index % 4) + 1) . '.jpg');
            @endphp
            <div class="col-lg-3 col-md-6">
                <div class="team-item text-center rounded overflow-hidden h-100">
                    <div class="rounded-circle overflow-hidden m-4">
                        <img class="img-fluid" src="{{ $photo ?: $fallback }}" alt="{{ $member['name'] ?? '' }}">
                    </div>
                    <h5 class="mb-0">{{ $member['name'] ?? '' }}</h5>
                    <small>{{ $member['title'] ?? '' }}</small>
                    @if(!empty($member['description']))
                    <p class="px-3 mt-3 mb-0">{{ $member['description'] }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
