@php
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-second';
    $floating = LayoutSettings::floatingButtons($themeName);
@endphp

@if(!empty($floating['visible']) && !empty($floating['buttons']))
    <div class="floating-contact">
        @foreach($floating['buttons'] as $button)
            <a
                href="{{ $button['href'] }}"
                class="floating-contact__item floating-contact__item--{{ $button['type'] }}"
                @if(!empty($button['external'])) target="_blank" rel="noopener noreferrer" @endif
                aria-label="{{ $button['aria_label'] }}"
            >
                <span class="floating-contact__icon" aria-hidden="true">
                    @if($button['type'] === 'whatsapp')
                        <i class="fa fa-whatsapp"></i>
                    @else
                        <i class="fa fa-phone"></i>
                    @endif
                </span>
                <span class="floating-contact__label">{{ $button['label'] }}</span>
            </a>
        @endforeach
    </div>
@endif

@once
    <style>
        .floating-contact {
            position: fixed;
            right: 1.25rem;
            bottom: 1.25rem;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            align-items: flex-end;
            pointer-events: none;
        }

        .floating-contact__item {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.7rem 1rem;
            border-radius: 999px;
            background: #7fad39;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 14px 30px rgba(28, 28, 28, 0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            pointer-events: auto;
        }

        .floating-contact__item:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 36px rgba(28, 28, 28, 0.2);
        }

        .floating-contact__item--phone:hover {
            background: #658d2f;
        }

        .floating-contact__item--whatsapp {
            background: #25d366;
        }

        .floating-contact__item--whatsapp:hover {
            background: #1ebe5d;
        }

        .floating-contact__icon {
            font-size: 1.05rem;
            line-height: 1;
        }

        .floating-contact__label {
            white-space: nowrap;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .floating-contact {
                right: 0.9rem;
                bottom: 0.9rem;
            }

            .floating-contact__item {
                padding: 0.6rem 0.85rem;
                font-size: 0.85rem;
            }
        }
    </style>
@endonce
