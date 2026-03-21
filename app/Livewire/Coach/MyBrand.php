<?php

namespace App\Livewire\Coach;

use App\Models\CoachProfile;
use App\Models\CoachPwaConfig;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Mi Marca'])]
class MyBrand extends Component
{
    public string $activeTab = 'brand';

    // Coach info
    public int $coachId = 0;
    public string $coachName = '';
    public int $profileId = 0;

    // Brand fields (from coach_profiles)
    public string $slug = '';
    public string $bio = '';
    public string $color_primary = '#E31E24';
    public string $logo_url = '';
    public string $photo_url = '';
    public string $whatsapp = '';
    public string $instagram = '';
    public bool $public_visible = true;

    // PWA fields (from coach_pwa_config)
    public int $pwaConfigId = 0;
    public string $pwa_app_name = 'Mi App Fitness';
    public string $pwa_icon_url = '';
    public string $pwa_color = '#E31E24';
    public string $pwa_subdomain = '';

    // Generated manifest JSON
    public string $manifestJson = '';

    // Flash states
    public bool $brandSaved = false;
    public bool $pwaSaved = false;

    public function mount(): void
    {
        $coach = auth('wellcore')->user();
        $this->coachId = $coach->id;
        $this->coachName = $coach->name ?? 'Coach';

        $this->loadProfile();
        $this->loadPwaConfig();
        $this->generateManifest();
    }

    protected function loadProfile(): void
    {
        $profile = CoachProfile::where('admin_id', $this->coachId)->first();

        if (! $profile) {
            $profile = CoachProfile::create([
                'admin_id' => $this->coachId,
                'slug' => Str::slug($this->coachName) . '-' . Str::random(4),
                'color_primary' => '#E31E24',
                'public_visible' => true,
            ]);
        }

        $this->profileId = $profile->id;
        $this->slug = $profile->slug ?? '';
        $this->bio = $profile->bio ?? '';
        $this->color_primary = $profile->color_primary ?? '#E31E24';
        $this->logo_url = $profile->logo_url ?? '';
        $this->photo_url = $profile->photo_url ?? '';
        $this->whatsapp = $profile->whatsapp ?? '';
        $this->instagram = $profile->instagram ?? '';
        $this->public_visible = (bool) $profile->public_visible;
    }

    protected function loadPwaConfig(): void
    {
        $pwa = CoachPwaConfig::where('coach_id', $this->coachId)->first();

        if ($pwa) {
            $this->pwaConfigId = $pwa->id;
            $this->pwa_app_name = $pwa->app_name ?? 'Mi App Fitness';
            $this->pwa_icon_url = $pwa->icon_url ?? '';
            $this->pwa_color = $pwa->color ?? '#E31E24';
            $this->pwa_subdomain = $pwa->subdomain ?? '';
        }
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->brandSaved = false;
        $this->pwaSaved = false;

        if ($tab === 'pwa' || $tab === 'preview') {
            $this->generateManifest();
        }
    }

    public function saveBrand(): void
    {
        CoachProfile::where('id', $this->profileId)->update([
            'slug' => $this->slug,
            'bio' => $this->bio,
            'color_primary' => $this->color_primary,
            'logo_url' => $this->logo_url,
            'photo_url' => $this->photo_url,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
            'public_visible' => $this->public_visible,
        ]);

        $this->brandSaved = true;
    }

    public function savePwa(): void
    {
        if ($this->pwaConfigId) {
            CoachPwaConfig::where('id', $this->pwaConfigId)->update([
                'app_name' => $this->pwa_app_name,
                'icon_url' => $this->pwa_icon_url,
                'color' => $this->pwa_color,
                'subdomain' => $this->pwa_subdomain,
            ]);
        } else {
            $pwa = CoachPwaConfig::create([
                'coach_id' => $this->coachId,
                'app_name' => $this->pwa_app_name,
                'icon_url' => $this->pwa_icon_url,
                'color' => $this->pwa_color,
                'subdomain' => $this->pwa_subdomain,
            ]);
            $this->pwaConfigId = $pwa->id;
        }

        $this->generateManifest();
        $this->pwaSaved = true;
    }

    public function generateManifest(): void
    {
        $manifest = [
            'name' => $this->pwa_app_name ?: 'Mi App Fitness',
            'short_name' => Str::limit($this->pwa_app_name ?: 'Fitness', 12, ''),
            'description' => $this->bio ?: 'Tu app de fitness personalizada',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#0A0A0A',
            'theme_color' => $this->pwa_color ?: '#E31E24',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => $this->pwa_icon_url ?: '/icons/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => $this->pwa_icon_url ?: '/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ];

        $this->manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function updatedPwaAppName(): void
    {
        $this->generateManifest();
    }

    public function updatedPwaColor(): void
    {
        $this->generateManifest();
    }

    public function updatedPwaIconUrl(): void
    {
        $this->generateManifest();
    }

    public function updatedBio(): void
    {
        if ($this->activeTab === 'preview') {
            $this->generateManifest();
        }
    }

    public function render()
    {
        return view('livewire.coach.my-brand');
    }
}
