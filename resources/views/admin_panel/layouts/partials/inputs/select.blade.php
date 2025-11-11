@props([
    'id',
    'title' => '',
    'options' => [],
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'col' => '12',
    'note' => '',
])

<div class="col-md-{{ $col }}">
    <label for="{{ $id }}" class="form-label">
        {{ $title }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    <select
        class="form-select @error($id) is-invalid @enderror"
        id="{{ $id }}"
        name="{{ $id }}"
        {{ $required ? 'required' : '' }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $key => $label)
            <option value="{{ $key }}" {{ old($id, $value) == $key ? 'selected' : '' }}>
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
