
@props(['active' => false])

<a
    {{ $attributes->class([
        'inline-flex items-center px-3 py-2 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none',
        'border-indigo-500 text-indigo-700 dark:border-indigo-400 dark:text-indigo-300' => $active,
        'border-transparent text-gray-600 dark:text-gray-300 hover:border-gray-300 hover:text-gray-800 dark:hover:border-gray-600 dark:hover:text-white' => ! $active,
    ]) }}
    @if($active) aria-current="page" @endif
>
    {{ $slot }}
</a>
