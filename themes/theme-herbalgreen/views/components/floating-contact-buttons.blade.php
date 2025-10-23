@php
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-herbalgreen';
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
                        💬
                    @else
                        📞
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
            right: 1.5rem;
            bottom: 1.5rem;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: flex-end;
            pointer-events: none;
        }

        .floating-contact__item {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.75rem 1.05rem;
            border-radius: 999px;
            background: var(--color-primary, #2e7d32);
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            pointer-events: auto;
        }

        .floating-contact__item:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.16);
        }

        .floating-contact__item--phone:hover {
            background: var(--color-accent, #4caf50);
        }

        .floating-contact__item--whatsapp {
            background: #25d366;
        }

        .floating-contact__item--whatsapp:hover {
            background: #1ebe5d;
        }

        .floating-contact__icon {
            font-size: 1.2rem;
            line-height: 1;
        }

        .floating-contact__label {
            white-space: nowrap;
            font-size: 0.95rem;
        }

        @media (max-width: 576px) {
            .floating-contact {
                right: 1rem;
                bottom: 1rem;
            }

            .floating-contact__item {
                padding: 0.65rem 0.9rem;
                font-size: 0.9rem;
            }
        }
    </style>
@endonce
