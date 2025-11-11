@props([
    'id',
    'title' => '',
    'value' => '',
    'type' => 'text',
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

    <input
        type="{{ $type }}"
        class="form-control @error($id) is-invalid @enderror"
        id="{{ $id }}"
        name="{{ $id }}"
        value="{{ old($id, $value) }}"
        {{ $required ? 'required' : '' }}
        placeholder="{{ $placeholder }}"
    >

    @if($note)
        <small class="text-muted">{{ $note }}</small>
    @endif

    @error($id)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
