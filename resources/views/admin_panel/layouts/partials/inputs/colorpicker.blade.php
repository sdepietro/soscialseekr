@props([
    'id',
    'title' => 'Color',
    'value' => '#000000',
    'required' => false,
    'col' => '12',
    'note' => '',
])

@php
    $val = old($id, $value) ?: '#000000';
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css">

<div class="col-md-{{ $col }}">
    <label for="{{ $id }}" class="form-label">
        {{ $title }} @if($required)<span class="text-danger">*</span>@endif
    </label>

    <div class="input-group">
        <input
            type="text"
            id="{{ $id }}"
            name="{{ $id }}"
            value="{{ $val }}"
            class="form-control @error($id) is-invalid @enderror"
            {{ $required ? 'required' : '' }}
            placeholder="#000000"
        >
        <span class="input-group-text">
            <!-- Este es el botón que abre Pickr -->
            <span id="{{ $id }}-swatch"
                  style="display:inline-block;width:18px;height:18px;border-radius:4px;background: {{ $val }};cursor:pointer;"></span>
        </span>
    </div>

    @if($note)
        <small class="text-muted">{{ $note }}</small>
    @endif

    @error($id)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr"></script>
    <script>
        (() => {
            const input  = document.getElementById(@json($id));
            const swatch = document.getElementById(@json($id) + '-swatch');

            const pickr = Pickr.create({
                el: swatch,                 // <- el trigger es el cuadrado
                useAsButton: true,          // no pisa el background del swatch
                theme: 'classic',
                default: input.value || '#000000',
                components: {
                    preview: true,
                    opacity: false,
                    hue: true,
                    interaction: {
                        hex: true,
                        input: true,
                        clear: true,
                        save: true
                    }
                }
            });

            function apply(hex) {
                if (!hex) return;
                input.value = hex;
                swatch.style.background = hex;
            }

            // Sincroniza al iniciar
            pickr.on('init', () => apply(pickr.getColor().toHEXA().toString()));

            // Mientras se mueve el selector
            pickr.on('change', (color) => apply(color.toHEXA().toString()));

            // Guardar y cerrar
            pickr.on('save', (color) => {
                if (color) apply(color.toHEXA().toString());
                pickr.hide();
            });

            // También abrir explícitamente al click (por si algún CSS bloquea eventos)
            swatch.addEventListener('click', () => pickr.show());

            // Si el usuario escribe a mano en el input
            input.addEventListener('input', function () {
                const v = this.value.trim();
                if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(v)) {
                    swatch.style.background = v;
                    try { pickr.setColor(v); } catch (e) {}
                }
            });
        })();
    </script>
@endpush
