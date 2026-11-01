{{-- Pastilles d'état : intégrité, doublon, visibilité. --}}
@props(['file', 'duplicate' => false, 'visibility' => true])
@if ($file->checksum_verified === true)
    <span class="badge badge-ok" title="{{ __('app.Intégrité vérifiée') }}"><i class="ri-shield-check-line"></i>{{ __('app.Vérifié') }}</span>
@elseif ($file->checksum_verified === false)
    <span class="badge badge-danger" title="{{ __('app.Empreinte non conforme !') }}"><i class="ri-shield-cross-line"></i>{{ __('app.Corrompu ?') }}</span>
@elseif (! $file->checksum)
    <span class="badge" title="{{ __('app.Checksum en cours de calcul…') }}"><i class="ri-loader-4-line animate-spin"></i>SHA-256</span>
@endif
@if ($duplicate)
    <span class="badge badge-warn" title="{{ __('app.Un fichier identique existe déjà') }}"><i class="ri-file-copy-2-line"></i>{{ __('app.Doublon') }}</span>
@endif
@if ($visibility)
    @if ($file->group)
        <span class="badge badge-info" title="{{ __('app.Groupe') }}"><i class="ri-lock-2-line"></i>{{ $file->group->name }}</span>
    @else
        <span class="badge badge-accent"><i class="ri-global-line"></i>{{ __('app.Public') }}</span>
    @endif
@endif
