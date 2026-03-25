<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\CoachProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Coaches'])]
class CoachManagement extends Component
{
    use WithPagination;

    public string $search     = '';
    public string $roleFilter = 'all';
    public string $sortBy     = 'created_at';
    public string $sortDir    = 'desc';

    // Create coach modal
    public bool   $showCreateModal = false;
    public string $newName         = '';
    public string $newUsername     = '';
    public string $newPassword     = '';
    public string $newRole         = 'coach';

    // Edit profile modal
    public bool    $showEditModal        = false;
    public ?int    $editingAdminId       = null;
    public string  $editBio              = '';
    public string  $editCity             = '';
    public string  $editExperience       = '';
    public string  $editSpecializations  = '';
    public string  $editWhatsapp         = '';
    public string  $editInstagram        = '';
    public string  $editReferralCode     = '';
    public string  $editReferralComm     = '';
    public bool    $editPublicVisible    = false;

    // View detail modal
    public bool $showViewModal  = false;
    public ?int $viewingAdminId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'desc';
        }
    }

    // --- Create Coach ---

    public function openCreate(): void
    {
        $this->reset(['newName', 'newUsername', 'newPassword', 'newRole']);
        $this->newRole = 'coach';
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->showCreateModal = false;
        $this->resetErrorBag();
    }

    public function createCoach(): void
    {
        $this->validate([
            'newName'     => 'required|string|max:100',
            'newUsername'  => 'required|string|max:50|unique:admins,username',
            'newPassword'  => 'required|string|min:8|max:255',
            'newRole'      => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
        ], [
            'newName.required'     => 'El nombre es obligatorio.',
            'newUsername.required'  => 'El usuario es obligatorio.',
            'newUsername.unique'    => 'Ese usuario ya existe.',
            'newPassword.required'  => 'La contrasena es obligatoria.',
            'newPassword.min'       => 'La contrasena debe tener al menos 8 caracteres.',
        ]);

        $admin = Admin::create([
            'name'          => $this->newName,
            'username'      => $this->newUsername,
            'password_hash' => Hash::make($this->newPassword),
            'role'          => $this->newRole,
        ]);

        // Auto-create coach profile if role is coach
        if ($this->newRole === 'coach') {
            CoachProfile::create([
                'admin_id'       => $admin->id,
                'slug'           => str()->slug($this->newName),
                'public_visible' => false,
            ]);
        }

        $this->closeCreate();
    }

    // --- Edit Profile ---

    public function openEdit(int $adminId): void
    {
        $admin   = Admin::with('coachProfile')->findOrFail($adminId);
        $profile = $admin->coachProfile;

        $this->editingAdminId      = $adminId;
        $this->editBio             = $profile->bio ?? '';
        $this->editCity            = $profile->city ?? '';
        $this->editExperience      = $profile->experience ?? '';
        $this->editSpecializations = is_array($profile->specializations ?? null)
            ? implode(', ', $profile->specializations)
            : '';
        $this->editWhatsapp        = $profile->whatsapp ?? '';
        $this->editInstagram       = $profile->instagram ?? '';
        $this->editReferralCode    = $profile->referral_code ?? '';
        $this->editReferralComm    = (string) ($profile->referral_commission ?? '');
        $this->editPublicVisible   = (bool) ($profile->public_visible ?? false);

        $this->showEditModal = true;
    }

    public function closeEdit(): void
    {
        $this->showEditModal  = false;
        $this->editingAdminId = null;
        $this->resetErrorBag();
    }

    public function saveProfile(): void
    {
        $this->validate([
            'editCity'           => 'nullable|string|max:100',
            'editBio'            => 'nullable|string|max:2000',
            'editExperience'     => 'nullable|string|max:255',
            'editWhatsapp'       => 'nullable|string|max:50',
            'editInstagram'      => 'nullable|string|max:100',
            'editReferralCode'   => 'nullable|string|max:20',
            'editReferralComm'   => 'nullable|numeric|min:0|max:100',
        ]);

        $admin   = Admin::findOrFail($this->editingAdminId);
        $profile = $admin->coachProfile;

        if (! $profile) {
            $profile = CoachProfile::create([
                'admin_id' => $admin->id,
                'slug'     => str()->slug($admin->name ?? $admin->username),
            ]);
        }

        $specs = $this->editSpecializations
            ? array_map('trim', explode(',', $this->editSpecializations))
            : [];

        $profile->update([
            'bio'                 => $this->editBio ?: null,
            'city'                => $this->editCity ?: null,
            'experience'          => $this->editExperience ?: null,
            'specializations'     => $specs ?: null,
            'whatsapp'            => $this->editWhatsapp ?: null,
            'instagram'           => $this->editInstagram ?: null,
            'referral_code'       => $this->editReferralCode ?: null,
            'referral_commission' => $this->editReferralComm !== '' ? $this->editReferralComm : null,
            'public_visible'      => $this->editPublicVisible,
        ]);

        $this->closeEdit();
    }

    // --- Delete Coach ---

    public function deleteCoach(int $adminId): void
    {
        $current = auth('wellcore')->user();

        // Prevent deleting yourself
        if ($current->id === $adminId) {
            return;
        }

        $admin = Admin::find($adminId);
        if (! $admin) {
            return;
        }

        // Prevent deleting superadmins
        $roleVal = $admin->role instanceof \App\Enums\UserRole ? $admin->role->value : $admin->role;
        if ($roleVal === 'superadmin') {
            return;
        }

        // Clean up related records
        \App\Models\AuthToken::where('user_id', $adminId)->where('user_type', 'admin')->delete();
        \App\Models\CoachProfile::where('admin_id', $adminId)->delete();

        $admin->delete();
    }

    // --- Toggle visibility ---

    public function toggleVisibility(int $adminId): void
    {
        $profile = CoachProfile::where('admin_id', $adminId)->first();
        if ($profile) {
            $profile->update(['public_visible' => ! $profile->public_visible]);
        }
    }

    // --- View Detail ---

    public function openView(int $adminId): void
    {
        $this->viewingAdminId = $adminId;
        $this->showViewModal  = true;
    }

    public function closeView(): void
    {
        $this->showViewModal  = false;
        $this->viewingAdminId = null;
    }

    public function render()
    {
        $query = Admin::query()
            ->with('coachProfile')
            ->orderBy($this->sortBy, $this->sortDir);

        if ($this->roleFilter !== 'all') {
            $query->where('role', $this->roleFilter);
        }

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%");
            });
        }

        $admins = $query->paginate(20);

        // Gather client counts per coach via assigned_plans
        $adminIds    = $admins->pluck('id')->toArray();
        $clientCounts = AssignedPlan::whereIn('assigned_by', $adminIds)
            ->where('active', true)
            ->selectRaw('assigned_by, COUNT(DISTINCT client_id) as cnt')
            ->groupBy('assigned_by')
            ->pluck('cnt', 'assigned_by')
            ->toArray();

        $stats = [
            'total'        => Admin::count(),
            'coaches'      => Admin::where('role', 'coach')->count(),
            'with_profile' => CoachProfile::count(),
            'clients'      => AssignedPlan::where('active', true)->distinct('client_id')->count('client_id'),
        ];

        // Viewing admin detail
        $viewingAdmin = $this->viewingAdminId
            ? Admin::with('coachProfile')->find($this->viewingAdminId)
            : null;

        return view('livewire.admin.coach-management', [
            'admins'       => $admins,
            'stats'        => $stats,
            'clientCounts' => $clientCounts,
            'viewingAdmin' => $viewingAdmin,
        ]);
    }
}
