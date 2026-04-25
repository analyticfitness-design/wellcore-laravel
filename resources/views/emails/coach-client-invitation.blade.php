@extends('emails.layouts.wellcore-base')

@section('title', $invitation->subject)

@section('content')

{{-- 1. Hero: foto del coach + mensaje personalizado --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
  <tr>
    @if(!empty($coachProfile?->photo_url))
    <td width="72" valign="top" style="padding-right:16px;">
      <img src="{{ $coachProfile->photo_url }}" width="60" height="60"
           alt="{{ $coach->name }}"
           style="border-radius:50%;border:2px solid #DC2626;width:60px;height:60px;object-fit:cover;">
    </td>
    @endif
    <td valign="top">
      <p style="color:#FAFAFA;font-weight:600;margin:0 0 4px;font-family:Arial,sans-serif;font-size:16px;">
        {{ $coach->name }} &middot; Coach WellCore
      </p>
      @if(!empty($invitation->intro_message))
      <p style="margin:0;color:#A1A1AA;font-family:Arial,sans-serif;font-size:15px;line-height:1.6;">
        {!! nl2br(e($invitation->intro_message)) !!}
      </p>
      @else
      <p style="margin:0;color:#A1A1AA;font-family:Arial,sans-serif;font-size:15px;">
        Te invito a unirte a WellCore Fitness y comenzar tu transformacion.
      </p>
      @endif
    </td>
  </tr>
</table>

{{-- Saludo personalizado --}}
@if(!empty($invitation->name))
<p style="color:#FAFAFA;font-size:18px;font-weight:600;font-family:Arial,sans-serif;margin-bottom:24px;">
  Hola {{ $invitation->name }}, esto es para ti
</p>
@endif

{{-- 2. Plan card --}}
<div style="background:#09090B;border-radius:8px;padding:24px;margin:0 0 24px;border:1px solid #DC2626;">
  <p style="color:#DC2626;font-weight:bold;text-transform:uppercase;margin:0 0 8px;font-family:Arial,sans-serif;font-size:12px;letter-spacing:1px;">
    PLAN SELECCIONADO
  </p>
  <h2 style="font-family:Arial,sans-serif;color:#FAFAFA;margin:0 0 8px;font-size:28px;font-weight:900;letter-spacing:1px;">
    {{ $planDetails['name'] }}
  </h2>
  <p style="color:#FAFAFA;font-size:24px;font-weight:bold;margin:0 0 16px;font-family:Arial,sans-serif;">
    ${{ number_format((float)$invitation->amount, 0, '.', '.') }} {{ $invitation->currency }}{{ $invitation->plan !== \App\Enums\PlanType::Rise ? '/mes' : '' }}
  </p>

  {{-- Features del plan --}}
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
    @foreach($planDetails['features'] as $feature)
    <tr>
      <td style="padding:8px 0;border-bottom:1px solid #27272A;color:#A1A1AA;font-family:Arial,sans-serif;font-size:14px;">
        <span style="color:#DC2626;margin-right:8px;">&#10003;</span> {{ $feature }}
      </td>
    </tr>
    @endforeach
  </table>
</div>

{{-- 3. CTA principal --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:32px 0;">
  <tr>
    <td align="center">
      <!--[if mso]>
      <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
        href="{{ $invitationUrl }}" style="height:52px;v-text-anchor:middle;width:300px;" arcsize="12%" stroke="f" fillcolor="#DC2626">
      <w:anchorlock/>
      <center style="color:#FAFAFA;font-family:Arial,sans-serif;font-size:16px;font-weight:bold;text-transform:uppercase;">
        {{ strtoupper($invitation->cta_label) }}
      </center>
      </v:roundrect>
      <![endif]-->
      <!--[if !mso]><!-->
      <a href="{{ $invitationUrl }}" class="btn-primary"
         style="display:inline-block;background-color:#DC2626;color:#FAFAFA;font-family:Arial,sans-serif;font-weight:bold;font-size:16px;text-decoration:none;padding:16px 32px;border-radius:6px;text-transform:uppercase;letter-spacing:0.5px;">
        {{ strtoupper($invitation->cta_label) }}
      </a>
      <!--<![endif]-->
      <p class="text-muted" style="margin-top:12px;font-family:Arial,sans-serif;color:#71717A;font-size:13px;">
        Oferta valida hasta {{ $invitation->expires_at->translatedFormat('d \d\e F \d\e Y') }}
      </p>
    </td>
  </tr>
</table>

{{-- 4. Trust signals --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
       style="border-top:1px solid #27272A;padding-top:20px;margin-top:20px;">
  <tr>
    <td align="center">
      <p style="color:#71717A;font-family:Arial,sans-serif;font-size:13px;margin:0;">
        Pago 100% seguro via Wompi &middot; PSE &middot; Nequi &middot; Tarjeta de credito/debito
      </p>
    </td>
  </tr>
</table>

{{-- Pixel de tracking (1x1, invisible) --}}
<img src="{{ $pixelUrl }}" width="1" height="1" alt=""
     style="display:block;width:1px;height:1px;max-height:1px;overflow:hidden;opacity:0;">

@endsection

@section('footer_extra')
<p class="text-muted" style="font-family:Arial,sans-serif;color:#71717A;font-size:13px;margin-bottom:8px;">
  Recibiste este mensaje porque {{ $coach->name }} te envio una invitacion personal a WellCore Fitness.
</p>
@endsection
