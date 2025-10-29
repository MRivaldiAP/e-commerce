@php
    $currentValue = $settings[$element['id']] ?? '';
    $mediaCollection = $mediaAssets instanceof \Illuminate\Support\Collection ? $mediaAssets : collect($mediaAssets);
    $hasCurrentInOptions = $currentValue && $mediaCollection->contains(function ($asset) use ($currentValue) {
        return ($asset->file_path ?? null) === $currentValue;
    });
@endphp

<label>{{ $element['label'] }}</label>
<select class="form-control" data-key="{{ $element['id'] }}">
    <option value="">{{ $placeholder ?? 'Pilih media' }}</option>
    @foreach ($mediaCollection as $asset)
        <option value="{{ $asset->file_path }}" {{ $currentValue === $asset->file_path ? 'selected' : '' }}>
            {{ $asset->name }}
        </option>
    @endforeach
    @if ($currentValue && ! $hasCurrentInOptions)
        <option value="{{ $currentValue }}" selected>{{ $currentValue }}</option>
    @endif
</select>
@if ($currentValue)
    @if (! empty($showPreview))
        <img src="{{ asset('storage/' . $currentValue) }}" alt="Preview" class="img-fluid mt-2 rounded" style="max-height: 120px; object-fit: contain;">
    @else
        <small class="form-text text-muted">
            <a href="{{ asset('storage/' . $currentValue) }}" target="_blank" rel="noopener">Lihat media saat ini</a>
        </small>
    @endif
@endif
