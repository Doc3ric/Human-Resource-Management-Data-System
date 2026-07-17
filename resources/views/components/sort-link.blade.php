{{--
    Enhancement Spec Sec. 1 — sort control for one column. Preserves every
    other current query param (filters, per_page, etc.) and resets pagination
    to page 1, since a fresh sort order makes the old page number meaningless.
--}}
@props(['column'])
@php
    $currentSort = request('sort');
    $currentDir = request('direction', 'asc');
    $isActive = $currentSort === $column;
    $nextDir = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';
    $href = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir, 'page' => null]);
@endphp
<a href="{{ $href }}" style="color:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
    {{ $slot }}
    <i class="bi {{ $isActive ? ($currentDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill') : 'bi-caret-down' }} tcc-sort-icon"></i>
</a>
