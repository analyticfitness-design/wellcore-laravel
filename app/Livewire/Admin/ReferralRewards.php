<?php

namespace App\Livewire\Admin;

use App\Models\Referral;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Recompensas de Referidos'])]
class ReferralRewards extends Component
{
    use WithPagination;

    public string $statusFilter = 'pending';

    public function approveReward(int $referralId): void
    {
        $referral = Referral::find($referralId);

        if (!$referral || $referral->reward_granted) {
            return;
        }

        $referral->update([
            'reward_granted' => true,
            'status'         => 'converted',
            'converted_at'   => now(),
        ]);
    }

    public function denyReward(int $referralId): void
    {
        $referral = Referral::find($referralId);

        if (!$referral) {
            return;
        }

        $referral->update(['status' => 'denied']);
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Referral::with(['referrer', 'referred'])
            ->orderByDesc('created_at');

        if ($this->statusFilter === 'pending') {
            $query->where('reward_granted', false)
                  ->whereNotIn('status', ['denied']);
        } elseif ($this->statusFilter === 'approved') {
            $query->where('reward_granted', true);
        }

        $referrals = $query->paginate(20);

        $stats = [
            'pending'  => Referral::where('reward_granted', false)
                ->whereNotIn('status', ['denied'])
                ->count(),
            'approved' => Referral::where('reward_granted', true)->count(),
            'total'    => Referral::count(),
        ];

        return view('livewire.admin.referral-rewards', compact('referrals', 'stats'));
    }
}
