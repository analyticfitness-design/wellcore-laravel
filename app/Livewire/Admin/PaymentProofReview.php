<?php

namespace App\Livewire\Admin;

use App\Enums\PaymentProofStatus;
use App\Models\Admin;
use App\Models\PaymentProof;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Comprobantes de Pago'])]
class PaymentProofReview extends Component
{
    use WithPagination;

    public string $status = '';

    public string $coachId = '';

    public ?int $selectedProofId = null;

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCoachId(): void
    {
        $this->resetPage();
    }

    /**
     * Generate a signed, time-limited view URL for a proof file and
     * dispatch it to the browser via a JS event so the view can open it.
     */
    public function getFileUrl(int $id): void
    {
        $token = Str::random(40);

        Cache::put("proof_view_{$token}", $id, now()->addMinutes(5));

        $url = route('admin.payment-proofs.view', ['token' => $token]);

        $this->dispatch('file-url-ready', url: $url);
    }

    public function render(): View
    {
        $proofs = PaymentProof::with(['coach:id,name'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->coachId, fn ($q) => $q->where('coach_id', $this->coachId))
            ->latest('submitted_at')
            ->paginate(15);

        $coaches = Admin::orderBy('name')
            ->select(['id', 'name'])
            ->get();

        $statusOptions = PaymentProofStatus::cases();

        return view('livewire.admin.payment-proof-review', compact(
            'proofs',
            'coaches',
            'statusOptions',
        ));
    }
}
