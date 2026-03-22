<?php

namespace App\Livewire\Client;

use App\Mail\ReferralInvitation;
use App\Models\Referral;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Referidos — WellCore'])]
class ReferralProgram extends Component
{
    public string $inviteEmail = '';
    public bool $showSuccess = false;
    public string $successMessage = '';

    protected function rules(): array
    {
        return [
            'inviteEmail' => 'required|email|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'inviteEmail.required' => 'El correo es obligatorio.',
            'inviteEmail.email'    => 'Ingresa un correo válido.',
        ];
    }

    public function sendInvite(): void
    {
        $this->validate();

        $user = auth('wellcore')->user();

        // Check if already referred this email
        $existing = Referral::where('referrer_id', $user->id)
            ->where('referred_email', $this->inviteEmail)
            ->first();

        if ($existing) {
            $this->addError('inviteEmail', 'Ya enviaste una invitación a este correo.');
            return;
        }

        Referral::create([
            'referrer_id'    => $user->id,
            'referred_email' => $this->inviteEmail,
            'status'         => 'pending',
            'reward_granted' => false,
            'created_at'     => now(),
        ]);

        $referralCode = base64_encode($user->id . ':' . substr(md5($user->id . $user->email), 0, 8));
        $referralLink = config('app.url') . '/inscripcion?ref=' . urlencode($referralCode);

        Mail::to($this->inviteEmail)
            ->queue(new ReferralInvitation(
                referrerName: $user->name ?? 'Tu amigo en WellCore',
                referralLink: $referralLink,
            ));

        $sentTo               = $this->inviteEmail;
        $this->inviteEmail    = '';
        $this->showSuccess    = true;
        $this->successMessage = "Invitación enviada a {$sentTo}";
    }

    public function dismissSuccess(): void
    {
        $this->showSuccess = false;
    }

    public function render()
    {
        $user = auth('wellcore')->user();

        $referrals = Referral::where('referrer_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $total     = $referrals->count();
        $converted = $referrals->where('status', 'converted')->count();
        $pending   = $referrals->where('status', 'pending')->count();
        $tasa      = $total > 0 ? round(($converted / $total) * 100, 1) : 0;

        $stats = [
            'total'     => $total,
            'converted' => $converted,
            'pending'   => $pending,
            'tasa'      => $tasa,
        ];

        $referralCode = base64_encode($user->id . ':' . substr(md5($user->id . $user->email), 0, 8));
        $referralLink = config('app.url') . '/inscripcion?ref=' . urlencode($referralCode);

        return view('livewire.client.referral-program', [
            'referrals'    => $referrals,
            'stats'        => $stats,
            'referralLink' => $referralLink,
        ]);
    }
}
