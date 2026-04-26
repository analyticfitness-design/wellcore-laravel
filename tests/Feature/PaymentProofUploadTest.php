<?php

/**
 * POST /api/v/coach/payment-proofs — upload a manual payment proof.
 *
 * Guard: auth:wellcore (Bearer token) + role:coach + coach.contract middleware
 * Rate limit: throttle:proof-upload → 10 per day per coach
 */

use App\Enums\PaymentProofStatus;
use App\Enums\PlanType;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\PaymentProof;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Create a Bearer token for an Admin and wire it into the test HTTP client.
 */
function actingAsCoachForUpload(Admin $coach): Tests\TestCase
{
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type'  => 'admin',
        'user_id'    => $coach->id,
        'token'      => $token,
        'expires_at' => now()->addDay(),
        'created_at' => now(),
    ]);

    return test()->withHeaders(['Authorization' => "Bearer {$token}"]);
}

/**
 * Disable the coach contract gate for the duration of a test so we can reach
 * the payment-proof endpoint without setting up a CoachContractAcceptance row.
 */
function withContractGateDisabled(): void
{
    config(['wellcore.coach_contract.enabled' => false]);
}

/**
 * Minimal valid multipart payload for POST /api/v/coach/payment-proofs.
 * Accepts overrides so individual tests can tweak single fields.
 */
function baseUploadPayload(array $override = []): array
{
    return array_merge([
        'client_name'    => 'Carlos Pérez',
        'client_email'   => fake()->unique()->safeEmail(),
        'plan'           => PlanType::Metodo->value,
        'amount'         => 339150,
        'payment_method' => 'transferencia',
    ], $override);
}

/**
 * Create a realistic fake PDF file (header bytes: %PDF-1.4).
 * finfo will detect this as application/pdf.
 */
function fakePdfFile(int $sizeKb = 50): UploadedFile
{
    // Write a minimal but real-enough PDF so finfo returns application/pdf
    $content = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\nxref\n0 0\n%%EOF";
    $tmpPath = tempnam(sys_get_temp_dir(), 'wc_test_') . '.pdf';
    file_put_contents($tmpPath, $content);

    return new UploadedFile(
        $tmpPath,
        'comprobante.pdf',
        'application/pdf',
        null,
        true   // test mode — skips PHP upload checks
    );
}

/**
 * Create a real minimal PNG file (valid PNG header bytes).
 */
function fakePngFile(): UploadedFile
{
    // Minimal 1×1 white PNG
    $pngData = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
    );
    $tmpPath = tempnam(sys_get_temp_dir(), 'wc_test_') . '.png';
    file_put_contents($tmpPath, $pngData);

    return new UploadedFile($tmpPath, 'comprobante.png', 'image/png', null, true);
}

/**
 * Create a fake text file renamed to .jpg so the MIME check will fail.
 * Simulates MIME spoofing (extension says image, content says text/plain).
 */
function fakeSpoofedJpgFile(): UploadedFile
{
    $tmpPath = tempnam(sys_get_temp_dir(), 'wc_test_') . '.jpg';
    file_put_contents($tmpPath, 'This is plain text disguised as a JPG.');

    return new UploadedFile($tmpPath, 'comprobante.jpg', 'image/jpeg', null, true);
}

// ---------------------------------------------------------------------------
// T1 — Authenticated coach can upload a valid PDF comprobante (201)
// ---------------------------------------------------------------------------

it('authenticated coach can upload a valid PDF proof and receives 201', function () {
    Mail::fake();
    Storage::fake('payment_proofs');

    withContractGateDisabled();

    $coach = Admin::factory()->coach()->create();

    $response = actingAsCoachForUpload($coach)
        ->postJson('/api/v/coach/payment-proofs', array_merge(
            baseUploadPayload(),
            ['file' => fakePdfFile()]
        ));

    $response->assertStatus(201)
             ->assertJsonStructure(['id', 'status', 'submittedAt', 'expiresAt', 'clientEmail', 'plan'])
             ->assertJsonPath('status', PaymentProofStatus::Pendiente->value);

    $this->assertDatabaseHas('payment_proofs', [
        'coach_id' => $coach->id,
        'status'   => PaymentProofStatus::Pendiente->value,
    ]);
});

// ---------------------------------------------------------------------------
// T2 — Request without Bearer token returns 401
// ---------------------------------------------------------------------------

it('unauthenticated request returns 401', function () {
    $response = $this->postJson('/api/v/coach/payment-proofs', baseUploadPayload([
        'file' => fakePdfFile(),
    ]));

    $response->assertStatus(401);
});

// ---------------------------------------------------------------------------
// T3 — MIME spoofing: file renamed .jpg but content is text/plain → 422
// ---------------------------------------------------------------------------

it('rejects file with spoofed MIME type (text content disguised as JPG)', function () {
    Mail::fake();
    Storage::fake('payment_proofs');
    withContractGateDisabled();

    $coach = Admin::factory()->coach()->create();

    $response = actingAsCoachForUpload($coach)
        ->postJson('/api/v/coach/payment-proofs', array_merge(
            baseUploadPayload(),
            ['file' => fakeSpoofedJpgFile()]
        ));

    $response->assertStatus(422)
             ->assertJsonValidationErrorFor('file');
});

// ---------------------------------------------------------------------------
// T4 — File exceeds 10 MB limit → 422
// ---------------------------------------------------------------------------

it('rejects file larger than 10 MB', function () {
    withContractGateDisabled();

    $coach = Admin::factory()->coach()->create();

    // UploadedFile in test mode: use UPLOAD_ERR_OK but report a large size
    // We create a real 10.1 MB temp file so Laravel's max:10240 rule triggers
    $tmpPath = tempnam(sys_get_temp_dir(), 'wc_large_') . '.pdf';
    $handle  = fopen($tmpPath, 'wb');
    fwrite($handle, "%PDF-1.4\n");
    fwrite($handle, str_repeat('X', 10 * 1024 * 1024 + 1024)); // 10MB + 1KB
    fclose($handle);

    $bigFile = new UploadedFile($tmpPath, 'big.pdf', 'application/pdf', null, true);

    $response = actingAsCoachForUpload($coach)
        ->postJson('/api/v/coach/payment-proofs', array_merge(
            baseUploadPayload(),
            ['file' => $bigFile]
        ));

    $response->assertStatus(422)
             ->assertJsonValidationErrorFor('file');
});

// ---------------------------------------------------------------------------
// T5 — Duplicate pending proof for same (coach, client_email) → 409 DUPLICATE_PENDING
// ---------------------------------------------------------------------------

it('returns 409 DUPLICATE_PENDING when a pending proof already exists for that email', function () {
    Mail::fake();
    Storage::fake('payment_proofs');
    withContractGateDisabled();

    $coach = Admin::factory()->coach()->create();
    $email = fake()->safeEmail();

    // Pre-existing pending proof for this coach + email
    PaymentProof::factory()->pendiente()->forCoach($coach)->create([
        'client_email' => $email,
    ]);

    $response = actingAsCoachForUpload($coach)
        ->postJson('/api/v/coach/payment-proofs', array_merge(
            baseUploadPayload(['client_email' => $email]),
            ['file' => fakePdfFile()]
        ));

    $response->assertStatus(409)
             ->assertJsonPath('errorCode', 'DUPLICATE_PENDING');
});

// ---------------------------------------------------------------------------
// T6 — Same SHA-256 file hash already pending (from any coach) → 409 DUPLICATE_FILE
// ---------------------------------------------------------------------------

it('returns 409 DUPLICATE_FILE when the same file is already pending from another coach', function () {
    Mail::fake();
    Storage::fake('payment_proofs');
    withContractGateDisabled();

    $coach1 = Admin::factory()->coach()->create();
    $coach2 = Admin::factory()->coach()->create();

    // We need the file hash to collide, so we compute it from the same bytes
    $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\nxref\n0 0\n%%EOF-UNIQUE-" . uniqid();
    $tmpPath    = tempnam(sys_get_temp_dir(), 'wc_dup_') . '.pdf';
    file_put_contents($tmpPath, $pdfContent);
    $knownHash = hash_file('sha256', $tmpPath);

    // Store existing proof from coach1 with the known hash
    PaymentProof::factory()->pendiente()->forCoach($coach1)->create([
        'file_hash' => $knownHash,
    ]);

    // coach2 tries to upload the exact same file
    $dupFile = new UploadedFile($tmpPath, 'comprobante.pdf', 'application/pdf', null, true);

    $response = actingAsCoachForUpload($coach2)
        ->postJson('/api/v/coach/payment-proofs', array_merge(
            baseUploadPayload(),
            ['file' => $dupFile]
        ));

    $response->assertStatus(409)
             ->assertJsonPath('errorCode', 'DUPLICATE_FILE');
});

// ---------------------------------------------------------------------------
// T7 — Rate limit: 11th upload in the same day returns 429
// ---------------------------------------------------------------------------

it('returns 429 when coach exceeds daily proof-upload limit of 10', function () {
    Mail::fake();
    Storage::fake('payment_proofs');
    withContractGateDisabled();

    $coach = Admin::factory()->coach()->create();

    // Create a token once and reuse it for all 11 requests
    $rawToken = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type'  => 'admin',
        'user_id'    => $coach->id,
        'token'      => $rawToken,
        'expires_at' => now()->addDay(),
        'created_at' => now(),
    ]);

    $headers = ['Authorization' => "Bearer {$rawToken}"];

    // Make 10 successful uploads to exhaust the daily limit.
    // Each needs a unique file (different hash) and unique email to avoid 409.
    for ($i = 1; $i <= 10; $i++) {
        $pdfContent = "%PDF-1.4 unique-" . uniqid('', true) . '-' . $i;
        $tmpPath = tempnam(sys_get_temp_dir(), 'wc_rate_') . '.pdf';
        file_put_contents($tmpPath, $pdfContent);
        $file = new UploadedFile($tmpPath, "proof{$i}.pdf", 'application/pdf', null, true);

        test()->withHeaders($headers)->postJson('/api/v/coach/payment-proofs', array_merge(
            baseUploadPayload(['client_email' => "ratelimit-{$i}-" . uniqid() . '@test.com']),
            ['file' => $file]
        ))->assertStatus(201);
    }

    // The 11th request should be rejected by the rate limiter
    $pdfContent = "%PDF-1.4 overflow-" . uniqid('', true);
    $tmpPath = tempnam(sys_get_temp_dir(), 'wc_over_') . '.pdf';
    file_put_contents($tmpPath, $pdfContent);
    $overFile = new UploadedFile($tmpPath, 'overflow.pdf', 'application/pdf', null, true);

    $response = test()->withHeaders($headers)->postJson(
        '/api/v/coach/payment-proofs',
        array_merge(
            baseUploadPayload(['client_email' => 'overflow-' . uniqid() . '@test.com']),
            ['file' => $overFile]
        )
    );

    $response->assertStatus(429);
});

// ---------------------------------------------------------------------------
// T8 — Missing required fields return 422 with validation errors
// ---------------------------------------------------------------------------

it('returns 422 when required fields are missing', function () {
    withContractGateDisabled();

    $coach = Admin::factory()->coach()->create();

    $response = actingAsCoachForUpload($coach)
        ->postJson('/api/v/coach/payment-proofs', [
            // No file, no client_name, no client_email, no plan
            'amount' => 100,
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrorFor('file')
             ->assertJsonValidationErrorFor('client_name')
             ->assertJsonValidationErrorFor('client_email')
             ->assertJsonValidationErrorFor('plan');
});

// ---------------------------------------------------------------------------
// T9 — Trial plan is rejected (excluded by Rule::enum except)
// ---------------------------------------------------------------------------

it('rejects trial plan as invalid enum value', function () {
    withContractGateDisabled();

    $coach = Admin::factory()->coach()->create();

    $response = actingAsCoachForUpload($coach)
        ->postJson('/api/v/coach/payment-proofs', array_merge(
            baseUploadPayload(['plan' => PlanType::Trial->value]),
            ['file' => fakePdfFile()]
        ));

    $response->assertStatus(422)
             ->assertJsonValidationErrorFor('plan');
});
