<section id="team" class="about-team">
    <div class="section-header">
        <h2>{{ $settings['team.heading'] ?? 'Tim Kami' }}</h2>
        @if(!empty($settings['team.description']))
            <p>{{ $settings['team.description'] }}</p>
        @endif
    </div>
    <div class="about-team__grid">
        @foreach($teamMembers as $member)
            <div class="about-team__card">
                @php $photo = $resolveMedia($member['photo'] ?? null); @endphp
                @if($photo)
                    <img src="{{ $photo }}" alt="{{ $member['name'] ?? '' }}" class="about-team__photo">
                @endif
                <h3>{{ $member['name'] ?? '' }}</h3>
                <span class="about-team__role">{{ $member['title'] ?? '' }}</span>
                @if(!empty($member['description']))
                    <p>{{ $member['description'] }}</p>
                @endif
            </div>
        @endforeach
    </div>
</section>
