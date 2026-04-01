@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-ccpl-blue focus:ring-ccpl-blue rounded-md shadow-sm']) !!}>
