<label for="{{ $id }}" class="mb-1 block">{{ $label }}</label>
<select id="{{ $id }}" class="mb-4 w-[210px] p-2 border rounded-md">
    <option value="">{{ $placeholder }}</option>
    @foreach ($options as $value => $text)
        <option value="{{ $value }}">{{ $text }}</option>
    @endforeach
</select>
