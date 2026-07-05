@props([
    'icon'       => 'bi-graph-up',
    'color'      => 'blue',
    'label'      => '',
    'value'      => 0,
    'sub'        => null,
    'pct'        => null,
    'analysis'   => null,
    'compliance' => null,
    'alert'      => false,
    'link'       => null,
])
@php
$colors = [
    'blue'   => ['bg'=>'#e0f2fe','ic'=>'#0284c7','brd'=>'#7dd3fc'],
    'indigo' => ['bg'=>'#e0e7ff','ic'=>'#4338ca','brd'=>'#a5b4fc'],
    'purple' => ['bg'=>'#f3e8ff','ic'=>'#9333ea','brd'=>'#c4b5fd'],
    'pink'   => ['bg'=>'#fce7f3','ic'=>'#db2777','brd'=>'#f9a8d4'],
    'red'    => ['bg'=>'#fee2e2','ic'=>'#dc2626','brd'=>'#fca5a5'],
    'orange' => ['bg'=>'#ffedd5','ic'=>'#ea580c','brd'=>'#fdba74'],
    'yellow' => ['bg'=>'#fef9c3','ic'=>'#ca8a04','brd'=>'#fde047'],
    'green'  => ['bg'=>'#dcfce7','ic'=>'#16a34a','brd'=>'#86efac'],
    'teal'   => ['bg'=>'#ccfbf1','ic'=>'#0f766e','brd'=>'#5eead4'],
    'slate'  => ['bg'=>'#f1f5f9','ic'=>'#475569','brd'=>'#cbd5e1'],
];
$c   = $colors[$color] ?? $colors['blue'];
$tag = $link ? 'a' : 'div';
$alertBorder = $alert ? '2px solid #ef4444' : "1px solid {$c['brd']}";
$alertBg     = $alert ? '#fef2f2' : '#fff';
@endphp

<{{ $tag }} @if($link) href="{{ $link }}" @endif
    style="display:flex;flex-direction:column;justify-content:space-between;
           background:{{ $alertBg }};border:{{ $alertBorder }};border-radius:14px;
           padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.04);
           {{ $link ? 'text-decoration:none;transition:box-shadow .2s,transform .15s;cursor:pointer;' : '' }}"
    @if($link) onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)';this.style.transform='translateY(-2px)'"
               onmouseout="this.style.boxShadow='0 1px 4px rgba(0,0,0,.04)';this.style.transform='translateY(0)'" @endif>

    {{-- Top row: label + icon --}}
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
        <div>
            @if($compliance)
            <span style="display:inline-block;background:{{ $c['bg'] }};color:{{ $c['ic'] }};
                         font-size:9px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;
                         padding:2px 7px;border-radius:99px;margin-bottom:6px;">{{ $compliance }}</span>
            @endif
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;
                        color:{{ $alert ? '#991b1b' : '#475569' }};">{{ $label }}</div>
        </div>
        <div style="width:42px;height:42px;border-radius:10px;background:{{ $c['bg'] }};
                    color:{{ $c['ic'] }};display:flex;align-items:center;justify-content:center;
                    font-size:20px;flex-shrink:0;">
            <i class="bi {{ $icon }}"></i>
        </div>
    </div>

    {{-- Value --}}
    <div style="font-size:32px;font-weight:900;color:{{ $alert ? '#991b1b' : '#0f172a' }};
                line-height:1;margin-bottom:4px;">{{ number_format($value) }}</div>

    {{-- Percentage / sub-text --}}
    @if($pct !== null)
    <div style="font-size:12px;font-weight:700;color:{{ $c['ic'] }};margin-bottom:2px;">
        {{ $pct }}%
    </div>
    @endif
    @if($sub)
    <div style="font-size:11px;color:#64748b;font-weight:600;margin-bottom:6px;">{{ $sub }}</div>
    @endif

    {{-- Analysis footer --}}
    @if($analysis)
    <div style="margin-top:10px;padding-top:10px;border-top:1px solid {{ $c['brd'] }};
                font-size:10.5px;color:#64748b;line-height:1.5;">
        <i class="bi bi-info-circle" style="color:{{ $c['ic'] }};margin-right:4px;"></i>{{ $analysis }}
    </div>
    @endif

</{{ $tag }}>
