@php
    // Defaults solo si no vienen por @include
    $id       = $id       ?? 'icon';
    $title    = $title    ?? 'Icono';
    $value    = $value    ?? '';
    $required = $required ?? false;
    $col      = $col      ?? '12';
    $note     = $note     ?? '';

    // Cada opción incluye la clase completa lista para usar
    $options = $options ?? [];

    $current = old($id, $value) ?: array_key_first($options);
@endphp

<div class="col-md-{{ $col }}">
    <label for="{{ $id }}" class="form-label d-block">
        {{ $title }} @if($required)<span class="text-danger">*</span>@endif
    </label>

    {{-- Preview grande --}}
    <div class="text-center my-2">
        <i id="{{ $id }}-preview" class="{{ $current }}" style="font-size:3rem;"></i>
    </div>

    {{-- Selector --}}
    <select
        class="form-select @error($id) is-invalid @enderror"
        id="{{ $id }}"
        name="{{ $id }}"
        {{ $required ? 'required' : '' }}
    >
        @foreach($options as $iconClass => $label)
            <option value="{{ $iconClass }}" {{ $current === $iconClass ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>

    @if($note)
        <small class="text-muted">{{ $note }}</small>
    @endif

    @error($id)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
    <script>
        (function () {
            const select  = document.getElementById(@json($id));
            const preview = document.getElementById(@json($id) + '-preview');

            function updatePreview() {
                const cls = select.value || 'ri ri-train-line';
                preview.className = cls;
                preview.style.fontSize = '3rem';
            }

            updatePreview();
            select.addEventListener('change', updatePreview);
        })();
    </script>
@endpush
