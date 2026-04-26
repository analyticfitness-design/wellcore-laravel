<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\PaymentProof;

/**
 * PaymentProofPolicy
 *
 * Gate users are always Admin records (coaches, jefe, admin, superadmin).
 * Clients have no access to PaymentProof at all — they are blocked at the
 * route/controller layer and this policy is never invoked for them.
 *
 * Rules (from PAYMENT_PROOF_FEATURE_PLAN.md §6):
 *   view      → superadmin/admin/jefe see all; coach only sees own (coach_id match)
 *   viewAny   → superadmin/admin/jefe/coach can list (coach filters in controller)
 *   create    → coach, admin, jefe, superadmin (contract check is in middleware)
 *   update    → NOBODY — immutable once uploaded
 *   delete    → superadmin only
 *   approve   → superadmin/admin/jefe
 *   reject    → superadmin/admin/jefe
 */
class PaymentProofPolicy
{
    // ------------------------------------------------------------------
    // Private helpers — match the enum values, not raw strings
    // ------------------------------------------------------------------

    private function isSuperadmin(Admin $user): bool
    {
        return $user->role === UserRole::Superadmin;
    }

    private function isAdminOrAbove(Admin $user): bool
    {
        return in_array($user->role, [
            UserRole::Superadmin,
            UserRole::Admin,
            UserRole::Jefe,
        ], strict: true);
    }

    private function isCoachOrAbove(Admin $user): bool
    {
        return in_array($user->role, [
            UserRole::Superadmin,
            UserRole::Admin,
            UserRole::Jefe,
            UserRole::Coach,
        ], strict: true);
    }

    // ------------------------------------------------------------------
    // Policy methods
    // ------------------------------------------------------------------

    /**
     * superadmin/admin/jefe see any record.
     * Coach sees only the proof they submitted (coach_id ownership).
     */
    public function view(Admin $user, PaymentProof $paymentProof): bool
    {
        if ($this->isAdminOrAbove($user)) {
            return true;
        }

        if ($user->role === UserRole::Coach) {
            return $paymentProof->coach_id === $user->id;
        }

        return false;
    }

    /**
     * Any Admin-portal user may reach the listing endpoint.
     * Coaches must additionally filter by coach_id — enforced in the controller,
     * not here, to keep the policy single-responsibility.
     */
    public function viewAny(Admin $user): bool
    {
        return $this->isCoachOrAbove($user);
    }

    /**
     * Coaches and above may submit payment proofs.
     * Active contract validation is handled by the EnsureCoachContractAccepted
     * middleware on the route, so the policy only checks role level.
     */
    public function create(Admin $user): bool
    {
        return $this->isCoachOrAbove($user);
    }

    /**
     * Payment proofs are immutable once uploaded.
     * No user role — including superadmin — may edit them.
     */
    public function update(Admin $user, PaymentProof $paymentProof): bool
    {
        return false;
    }

    /**
     * Permanent deletion: superadmin only.
     */
    public function delete(Admin $user, PaymentProof $paymentProof): bool
    {
        return $this->isSuperadmin($user);
    }

    /**
     * Approve a pending proof: superadmin, admin, jefe.
     */
    public function approve(Admin $user, PaymentProof $paymentProof): bool
    {
        return $this->isAdminOrAbove($user);
    }

    /**
     * Reject a pending proof: superadmin, admin, jefe.
     */
    public function reject(Admin $user, PaymentProof $paymentProof): bool
    {
        return $this->isAdminOrAbove($user);
    }
}
