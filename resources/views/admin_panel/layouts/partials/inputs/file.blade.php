@props([
    'id',
    'title' => '',
    'note' => '',
    'accept' => 'image/*',
    'model' => null,
    'preview' => null,
    'col' => '12'
])

<div class="col-md-{{ $col }}">
    <label for="{{ $id }}" class="form-label">{{ $title }}</label>

    @if($preview)
        <div class="mb-2">
            <img src="{{ $preview }}" alt="{{ $model?->name ?? 'Imagen' }}" class="img-thumbnail" width="100">
        </div>
    @endif

    <input
        type="file"
        class="form-control @error($id) is-invalid @enderror"
        id="{{ $id }}"
        name="{{ $id }}"
        accept="{{ $accept }}"
    >

    @if($note)
        <small class="text-muted">{{ $note }}</small>
    @endif

    @error($id)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
