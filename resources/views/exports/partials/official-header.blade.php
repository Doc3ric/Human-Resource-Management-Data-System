{{--
    Official PHRMO letterhead — the single source of truth for the header used
    on every generated PDF report/letter. @include this right after <body>;
    it brings its own scoped <style>, so it works regardless of the host
    document's own font/base styles. Do not duplicate this markup elsewhere —
    update this file and every document picks up the change.
--}}
<style>
    .official-pdf-header { width: 100%; text-align: center; font-family: 'Times New Roman', Times, serif; color: #000; margin-bottom: 10px; }
    .official-pdf-header .official-pdf-header-logos { margin-bottom: 6px; }
    .official-pdf-header .official-pdf-header-logos img { height: 68px; vertical-align: middle; margin: 0 14px; }
    .official-pdf-header .official-pdf-header-text { font-size: 11pt; line-height: 1.35; }
    .official-pdf-header .official-pdf-header-text .republic { font-size: 11pt; }
    .official-pdf-header .official-pdf-header-text .province { font-size: 13pt; font-weight: bold; text-transform: uppercase; }
    .official-pdf-header .official-pdf-header-text .capitol { font-size: 11pt; }
    .official-pdf-header .official-pdf-header-text .office { font-size: 12.5pt; font-weight: bold; text-transform: uppercase; margin-top: 4px; }
    .official-pdf-header-divider { border-bottom: 2px solid #000; margin: 8px 0 16px; }
</style>

<div class="official-pdf-header">
    <div class="official-pdf-header-logos">
        @if(file_exists(public_path('img/logo.png')))
            <img src="{{ public_path('img/logo.png') }}" alt="Province of Bukidnon Official Seal">
        @endif
        @if(file_exists(public_path('img/phrmologo.png')))
            <img src="{{ public_path('img/phrmologo.png') }}" alt="PHRMO Logo">
        @endif
        @if(file_exists(public_path('img/bagong-pilipinas.png')))
            <img src="{{ public_path('img/bagong-pilipinas.png') }}" alt="Bagong Pilipinas">
        @endif
    </div>
    <div class="official-pdf-header-text">
        <div class="republic">Republic of the Philippines</div>
        <div class="province">Province of Bukidnon</div>
        <div class="capitol">Provincial Capitol</div>
        <div class="office">Provincial Human Resource Management Office</div>
    </div>
</div>
<div class="official-pdf-header-divider"></div>
