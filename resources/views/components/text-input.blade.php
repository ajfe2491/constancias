@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-base-100 text-base-content border-base-300 focus:border-primary focus:ring-primary rounded-md shadow-sm']) }}>
