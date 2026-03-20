<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\CoachProfile;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\ReferralStat;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Mi Perfil'])]
class CoachProfilePage extends Component
{
    public string $activeTab = 'profile';

    // Profile fields
    public string $bio = '';
    public string $city = '';
    public string $experience = '';
    public string $whatsapp = '';
    public string $instagram = '';
    public string $specializations_input = '';
    public string $color_primary = '#E31E24';
    public bool $public_visible = true;
    public string $referral_code = '';
    public string $photo_url = '';
    public string $logo_url = '';

    // Coach info
    public string $coachName = '';
    public int $coachId = 0;
    public int $profileId = 0;
    public string $slug = '';

    // Referral stats
    public int $totalReferrals = 0;
    public int $pendingReferrals = 0;
    public int $registeredReferrals = 0;
    public int $convertedReferrals = 0;
    public array $referralsList = [];
    public string $referralLink = '';
    public string $commissionRate = '5.00';

    // Referral stats (link clicks)
    public int $totalClicks = 0;
    public int $convertedClicks = 0;

    // Revenue data
    public float $totalRevenue = 0;
    public int $revenueActiveClients = 0;
    public float $estimatedCommission = 0;
    public array $monthlyRevenue = [];
    public array $clientContributions = [];

    // Flash
    public bool $saved = false;

    public function mount(): void
    {
        $coach = auth('wellcore')->user();
        $this->coachId = $coach->id;
        $this->coachName = $coach->name ?? 'Coach';

        $profile = CoachProfile::where('admin_id', $this->coachId)->first();

        if (! $profile) {
            $profile = CoachProfile::create([
                'admin_id' => $this->coachId,
                'slug' => Str::slug($this->coachName) . '-' . Str::random(4),
                'referral_code' => strtoupper(Str::random(8)),
                'color_primary' => '#E31E24',
                'public_visible' => true,
            ]);
        }

        $this->profileId = $profile->id;
        $this->slug = $profile->slug;
        $this->bio = $profile->bio ?? '';
        $this->city = $profile->city ?? '';
        $this->experience = $profile->experience ?? '';
        $this->whatsapp = $profile->whatsapp ?? '';
        $this->instagram = $profile->instagram ?? '';
        $this->color_primary = $profile->color_primary ?? '#E31E24';
        $this->public_visible = (bool) $profile->public_visible;
        $this->referral_code = $profile->referral_code ?? '';
        $this->photo_url = $profile->photo_url ?? '';
        $this->logo_url = $profile->logo_url ?? '';
        $this->commissionRate = $profile->referral_commission ?? '5.00';

        $specs = $profile->specializations;
        $this->specializations_input = is_array($specs) ? implode(', ', $specs) : '';

        $this->referralLink = url('/inscripcion?ref=' . $this->referral_code);

        $this->loadReferrals();
        $this->loadRevenue();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveProfile(): void
    {
        $specs = array_map('trim', explode(',', $this->specializations_input));
        $specs = array_values(array_filter($specs));

        CoachProfile::where('id', $this->profileId)->update([
            'bio' => $this->bio,
            'city' => $this->city,
            'experience' => $this->experience,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
            'specializations' => json_encode($specs),
            'color_primary' => $this->color_primary,
            'public_visible' => $this->public_visible,
        ]);

        $this->saved = true;
    }

    protected function loadReferrals(): void
    {
        // Coach referral_stats (link click tracking)
        $this->totalClicks = ReferralStat::where('coach_id', $this->coachId)->count();
        $this->convertedClicks = ReferralStat::where('coach_id', $this->coachId)->where('converted', true)->count();

        // Referrals from referrals table — coaches use admin_id as referrer_id
        $referrals = Referral::where('referrer_id', $this->coachId)->get();

        $this->totalReferrals = $referrals->count();
        $this->pendingReferrals = $referrals->where('status', 'pending')->count();
        $this->registeredReferrals = $referrals->where('status', 'registered')->count();
        $this->convertedReferrals = $referrals->where('status', 'converted')->count();

        $this->referralsList = [];
        foreach ($referrals->sortByDesc('created_at')->take(20) as $ref) {
            $client = $ref->referred_id ? Client::find($ref->referred_id) : null;
            $this->referralsList[] = [
                'email' => $ref->referred_email,
                'status' => $ref->status,
                'client_name' => $client?->name ?? '-',
                'created_at' => $ref->created_at?->format('d/m/Y') ?? '-',
                'converted_at' => $ref->converted_at?->format('d/m/Y') ?? '-',
                'reward_granted' => $ref->reward_granted,
            ];
        }
    }

    protected function loadRevenue(): void
    {
        // Get client IDs assigned to this coach
        $clientIds = AssignedPlan::where('assigned_by', $this->coachId)
            ->pluck('client_id')
            ->unique()
            ->values();

        $this->revenueActiveClients = Client::whereIn('id', $clientIds)
            ->where('status', 'activo')
            ->count();

        // Total approved payments from those clients
        $payments = Payment::whereIn('client_id', $clientIds)
            ->where('status', 'approved')
            ->get();

        $this->totalRevenue = (float) $payments->sum('amount');
        $this->estimatedCommission = $this->totalRevenue * ((float) $this->commissionRate / 100);

        // Monthly breakdown — last 6 months
        $this->monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthTotal = Payment::whereIn('client_id', $clientIds)
                ->where('status', 'approved')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');

            $this->monthlyRevenue[] = [
                'label' => $date->translatedFormat('M Y'),
                'amount' => (float) $monthTotal,
            ];
        }

        // Client contribution breakdown
        $this->clientContributions = [];
        $clientPayments = Payment::whereIn('client_id', $clientIds)
            ->where('status', 'approved')
            ->selectRaw('client_id, SUM(amount) as total, COUNT(*) as payment_count, MAX(created_at) as last_payment')
            ->groupBy('client_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        foreach ($clientPayments as $cp) {
            $client = Client::find($cp->client_id);
            if (! $client) continue;

            $this->clientContributions[] = [
                'name' => $client->name,
                'plan' => $client->plan?->value ?? '-',
                'total' => (float) $cp->total,
                'payments' => $cp->payment_count,
                'last_payment' => Carbon::parse($cp->last_payment)->format('d/m/Y'),
            ];
        }
    }

    public function render()
    {
        return view('livewire.coach.coach-profile-page');
    }
}
