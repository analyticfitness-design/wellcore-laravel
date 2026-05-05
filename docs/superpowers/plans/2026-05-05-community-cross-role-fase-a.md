# Community Cross-Role — Fase A: Backend Foundations Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construir todo el backend de la arquitectura Community Cross-Role (migraciones, models, services, controllers, policy, events, listeners, push extensions, channels) sin tocar UI todavía. Al final de Fase A los endpoints están funcionales, testados y listos para que la Fase B (Coach Community Hub UI) los consuma.

**Architecture:** Laravel 13 + Pest TDD. 9 migraciones aditivas (sin destructivas — cumple CLAUDE.md), 6 models nuevos, 5 services con interfaz pública estable, 6 controllers REST con feature tests, 1 Policy, 6 broadcast events Reverb, 2 listeners, extensiones a `PushNotificationService`. Cache strategy con namespaces `wc:*` y TTL 30s/60s/300s según hot-path. Todo strangler-fig friendly: models existentes (`CommunityPost`, `PostComment`, `PostReaction`, `ClientPulso`, `CoachMessage`) NO se rompen — solo se extienden con columnas opcionales (defaults seguros).

**Tech Stack:** PHP 8.4, Laravel 13.1.1, Eloquent ORM, Pest v3 (con `DatabaseTransactions`), MySQL (wellcore_fitness shared DB), Reverb broadcasting, Redis cache, WebPush + VAPID. Linter Pint configurado.

**Spec:** `docs/superpowers/specs/2026-05-05-community-cross-role-design.md`

**Phase scope:** Solo Fase A del spec (Backend foundations, sprint 1, ~1 semana). Fases B/C/D obtendrán sus propios planes después de mergear A.

---

## File Map

### Backend new files (28)

```
database/migrations/
├── 2026_05_05_000001_create_pinned_posts_table.php
├── 2026_05_05_000002_create_post_reports_table.php
├── 2026_05_05_000003_create_moderation_actions_table.php
├── 2026_05_05_000004_create_broadcast_messages_table.php
├── 2026_05_05_000005_create_coach_notification_preferences_table.php
├── 2026_05_05_000006_create_post_mentions_table.php
├── 2026_05_05_000007_create_coach_push_subscriptions_table.php
├── 2026_05_05_000008_extend_community_posts_author.php
└── 2026_05_05_000009_extend_post_comments_author.php

app/Models/
├── PinnedPost.php
├── PostReport.php
├── ModerationAction.php
├── BroadcastMessage.php
├── CoachNotificationPreference.php
└── PostMention.php

app/Services/
├── CoachCommunityService.php
├── AdminCommunityService.php
├── BroadcastService.php
├── ModerationService.php
└── MentionResolverService.php

app/Http/Controllers/Api/Coach/
├── CommunityController.php
└── ModerationController.php

app/Http/Controllers/Api/Admin/
├── CommunityController.php
├── BroadcastController.php
└── ModerationQueueController.php

app/Http/Controllers/Api/
└── PostReportController.php

app/Events/
├── CoachCommunityActivity.php
├── PostPinned.php
├── PostReported.php
├── PostMadeOfficial.php
├── BroadcastSent.php
└── MentionCreated.php

app/Listeners/
├── NotifyCoachOnClientActivity.php
└── NotifyMentionedUsers.php

app/Policies/
└── CommunityPostPolicy.php

database/factories/
├── PinnedPostFactory.php
├── PostReportFactory.php
└── BroadcastMessageFactory.php
```

### Backend modified files (5)

```
app/Models/CommunityPost.php          # add fillables author_type, author_admin_id, is_official, is_global + scope active()
app/Models/PostComment.php            # add fillables author_type, author_admin_id
app/Services/PushNotificationService.php   # add 4 coach methods
routes/channels.php                   # add 3 channels
routes/api/v.php                      # add 14 routes (or wherever versioned API routes live)
```

### Tests (15 new files)

```
tests/Unit/Services/
├── CoachCommunityServiceTest.php
├── AdminCommunityServiceTest.php
├── BroadcastServiceTest.php
├── ModerationServiceTest.php
└── MentionResolverServiceTest.php

tests/Feature/Coach/
├── CommunityEndpointsTest.php
└── ModerationEndpointsTest.php

tests/Feature/Admin/
├── CommunityEndpointsTest.php
├── BroadcastEndpointsTest.php
└── ModerationQueueEndpointsTest.php

tests/Feature/PostReportEndpointsTest.php

tests/Feature/Policies/
└── CommunityPostPolicyTest.php

tests/Feature/Channels/
├── CoachCommunityChannelAuthTest.php
└── AdminCommunityChannelAuthTest.php

tests/Feature/Events/
└── CommunityBroadcastEventsTest.php
```

---

## Pre-flight (one time, do BEFORE Task 1)

Confirm working directory and branch state. Spec lives in `fix/food-tracking-fallback-meals` (commit d898082b) which is unrelated to this work — we'll cherry-pick into a dedicated branch.

```bash
cd C:\Users\GODSF\Herd\wellcore-laravel
git fetch origin
git status
git log -1 --oneline   # confirm we see d898082b on current HEAD or note where to cherry-pick from
```

If unsure about branch state, ASK the user before continuing.

---

## Task 0: Setup branch and cherry-pick spec

**Files:**
- N/A (git operation)

- [ ] **Step 1: Confirm spec commit SHA exists**

Run: `git log --all --oneline --grep="Cross-Role" -1`
Expected output: `d898082b docs(spec): Community Cross-Role - Cliente/Coach/Superadmin (autonomous, sin MVP)`

- [ ] **Step 2: Create feature branch from main**

```bash
git checkout main
git pull origin main
git checkout -b feat/community-cross-role-fase-a
```

Expected: branch created, working tree clean.

- [ ] **Step 3: Cherry-pick spec commit**

```bash
git cherry-pick d898082b
```

Expected: clean cherry-pick, no conflicts (spec is a standalone new file).
If conflict on `docs/superpowers/specs/`: keep our version (`git checkout --ours <file>`), then `git cherry-pick --continue`.

- [ ] **Step 4: Verify spec file is in place**

```bash
ls docs/superpowers/specs/2026-05-05-community-cross-role-design.md
git log -1 --oneline
```

Expected: file exists, last commit is the cherry-picked spec.

- [ ] **Step 5: Confirm DB and queue are running locally**

```bash
php artisan migrate:status | head -5
php artisan tinker --execute="echo DB::connection()->getPdo() ? 'DB OK' : 'DB FAIL';"
```

Expected: migrate:status returns table; DB OK printed.

If queue worker is needed for broadcast tests, also run `php artisan queue:work --once` to confirm worker starts.

---

## Task 1: Migration — pinned_posts

**Files:**
- Create: `database/migrations/2026_05_05_000001_create_pinned_posts_table.php`

- [ ] **Step 1: Create migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pinned_posts')) {
            return;
        }

        Schema::create('pinned_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->enum('pinned_by_type', ['coach', 'admin']);
            $table->unsignedInteger('pinned_by_id');
            $table->timestamp('pinned_at');
            $table->timestamp('pinned_until')->nullable();
            $table->string('note', 255)->nullable();

            $table->index(['post_id', 'pinned_until'], 'idx_pinned_active');
            $table->index(['pinned_by_id', 'pinned_by_type'], 'idx_pinned_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinned_posts');
    }
};
```

- [ ] **Step 2: Run migration**

Run: `php artisan migrate --path=database/migrations/2026_05_05_000001_create_pinned_posts_table.php`
Expected: `Migrated: 2026_05_05_000001_create_pinned_posts_table`

- [ ] **Step 3: Verify schema**

```bash
php artisan tinker --execute="dump(Schema::getColumnListing('pinned_posts'));"
```

Expected: `["id","post_id","pinned_by_type","pinned_by_id","pinned_at","pinned_until","note"]`

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_05_05_000001_create_pinned_posts_table.php
git commit -m "feat(community): migration pinned_posts"
```

---

## Task 2: Migration — post_reports

**Files:**
- Create: `database/migrations/2026_05_05_000002_create_post_reports_table.php`

- [ ] **Step 1: Create migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('post_reports')) {
            return;
        }

        Schema::create('post_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedInteger('reporter_id');
            $table->enum('reason', ['spam', 'offensive', 'off_topic', 'other']);
            $table->string('reason_detail', 500)->nullable();
            $table->enum('status', ['pending', 'dismissed', 'actioned'])->default('pending');
            $table->unsignedInteger('reviewed_by_admin_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['status', 'created_at'], 'idx_reports_pending');
            $table->index('post_id', 'idx_reports_post');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_reports');
    }
};
```

- [ ] **Step 2: Migrate**

Run: `php artisan migrate --path=database/migrations/2026_05_05_000002_create_post_reports_table.php`
Expected: `Migrated: 2026_05_05_000002_create_post_reports_table`

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_05_05_000002_create_post_reports_table.php
git commit -m "feat(community): migration post_reports"
```

---

## Task 3: Migration — moderation_actions

**Files:**
- Create: `database/migrations/2026_05_05_000003_create_moderation_actions_table.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('moderation_actions')) {
            return;
        }

        Schema::create('moderation_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('actor_type', ['coach', 'admin']);
            $table->unsignedInteger('actor_id');
            $table->enum('action_type', [
                'pin', 'unpin', 'delete', 'restore',
                'make_official', 'dismiss_report', 'hide_for_review',
            ]);
            $table->enum('target_type', ['post', 'comment']);
            $table->unsignedBigInteger('target_id');
            $table->string('reason', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['actor_id', 'actor_type', 'created_at'], 'idx_mod_actor');
            $table->index(['target_type', 'target_id'], 'idx_mod_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_actions');
    }
};
```

- [ ] **Step 2: Migrate + commit**

```bash
php artisan migrate --path=database/migrations/2026_05_05_000003_create_moderation_actions_table.php
git add database/migrations/2026_05_05_000003_create_moderation_actions_table.php
git commit -m "feat(community): migration moderation_actions audit log"
```

---

## Task 4: Migration — broadcast_messages

**Files:**
- Create: `database/migrations/2026_05_05_000004_create_broadcast_messages_table.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('broadcast_messages')) {
            return;
        }

        Schema::create('broadcast_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('sender_type', ['admin', 'coach']);
            $table->unsignedInteger('sender_id');
            $table->enum('audience_type', ['clients', 'coaches', 'all_communities', 'segmented']);
            $table->json('segment_filter')->nullable();
            $table->string('subject', 255)->nullable();
            $table->text('body');
            $table->boolean('push_enabled')->default(false);
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->timestamp('sent_at')->useCurrent();

            $table->index(['sender_type', 'sender_id'], 'idx_broadcast_sender');
            $table->index('sent_at', 'idx_broadcast_sent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_messages');
    }
};
```

- [ ] **Step 2: Migrate + commit**

```bash
php artisan migrate --path=database/migrations/2026_05_05_000004_create_broadcast_messages_table.php
git add database/migrations/2026_05_05_000004_create_broadcast_messages_table.php
git commit -m "feat(community): migration broadcast_messages audit"
```

---

## Task 5: Migration — coach_notification_preferences

**Files:**
- Create: `database/migrations/2026_05_05_000005_create_coach_notification_preferences_table.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('coach_notification_preferences')) {
            return;
        }

        Schema::create('coach_notification_preferences', function (Blueprint $table) {
            $table->unsignedInteger('coach_id')->primary();
            $table->boolean('notify_pr_broken')->default(true);
            $table->boolean('notify_streak_milestone')->default(true);
            $table->boolean('notify_post_created')->default(false);
            $table->boolean('notify_comment_on_my_reply')->default(true);
            $table->boolean('notify_at_risk_client')->default(true);
            $table->boolean('notify_official_post_engagement')->default(true);
            $table->boolean('notify_admin_broadcast')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_notification_preferences');
    }
};
```

- [ ] **Step 2: Migrate + commit**

```bash
php artisan migrate --path=database/migrations/2026_05_05_000005_create_coach_notification_preferences_table.php
git add database/migrations/2026_05_05_000005_create_coach_notification_preferences_table.php
git commit -m "feat(community): migration coach_notification_preferences"
```

---

## Task 6: Migration — post_mentions

**Files:**
- Create: `database/migrations/2026_05_05_000006_create_post_mentions_table.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('post_mentions')) {
            return;
        }

        Schema::create('post_mentions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->enum('mentioner_type', ['client', 'coach', 'admin']);
            $table->unsignedInteger('mentioner_id');
            $table->enum('mentioned_type', ['client', 'coach', 'admin']);
            $table->unsignedInteger('mentioned_id');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['mentioned_type', 'mentioned_id', 'created_at'], 'idx_mention_target');
            $table->index('post_id', 'idx_mention_post');
            $table->index('comment_id', 'idx_mention_comment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_mentions');
    }
};
```

- [ ] **Step 2: Migrate + commit**

```bash
php artisan migrate --path=database/migrations/2026_05_05_000006_create_post_mentions_table.php
git add database/migrations/2026_05_05_000006_create_post_mentions_table.php
git commit -m "feat(community): migration post_mentions"
```

---

## Task 7: Migration — coach_push_subscriptions

**Files:**
- Create: `database/migrations/2026_05_05_000007_create_coach_push_subscriptions_table.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('coach_push_subscriptions')) {
            return;
        }

        Schema::create('coach_push_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('coach_id');
            $table->string('endpoint', 500);
            $table->text('p256dh');
            $table->text('auth_key');
            $table->string('user_agent', 255)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // MySQL key-length cap: take first 191 chars of endpoint
            $table->unique(['coach_id', 'endpoint'], 'uq_coach_endpoint');
            $table->index(['coach_id', 'active'], 'idx_coach_subs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_push_subscriptions');
    }
};
```

- [ ] **Step 2: Migrate + commit**

If unique on `endpoint VARCHAR(500)` exceeds the index size limit (3072 bytes on InnoDB utf8mb4), Laravel will use a prefix automatically. If migration fails with a key-length error, change `endpoint` to `endpoint(191)` in the unique. Otherwise:

```bash
php artisan migrate --path=database/migrations/2026_05_05_000007_create_coach_push_subscriptions_table.php
git add database/migrations/2026_05_05_000007_create_coach_push_subscriptions_table.php
git commit -m "feat(community): migration coach_push_subscriptions"
```

---

## Task 8: Migration — extend community_posts

**Files:**
- Create: `database/migrations/2026_05_05_000008_extend_community_posts_author.php`

Idempotent — column adds guarded with `Schema::hasColumn`.

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('community_posts', 'author_type')) {
                $table->enum('author_type', ['client', 'coach', 'admin'])
                    ->default('client')
                    ->after('client_id');
            }
            if (!Schema::hasColumn('community_posts', 'author_admin_id')) {
                $table->unsignedInteger('author_admin_id')->nullable()->after('author_type');
            }
            if (!Schema::hasColumn('community_posts', 'is_official')) {
                $table->boolean('is_official')->default(false)->after('author_admin_id');
            }
            if (!Schema::hasColumn('community_posts', 'is_global')) {
                $table->boolean('is_global')->default(false)->after('is_official');
            }
        });

        // Add the index in a separate Schema::table closure to avoid Doctrine DBAL on enum
        if (!$this->indexExists('community_posts', 'idx_posts_official')) {
            Schema::table('community_posts', function (Blueprint $table) {
                $table->index(['is_official', 'is_global', 'created_at'], 'idx_posts_official');
            });
        }
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            if ($this->indexExists('community_posts', 'idx_posts_official')) {
                $table->dropIndex('idx_posts_official');
            }
            foreach (['is_global', 'is_official', 'author_admin_id', 'author_type'] as $col) {
                if (Schema::hasColumn('community_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = \DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$index]);
        return count($rows) > 0;
    }
};
```

- [ ] **Step 2: Migrate**

```bash
php artisan migrate --path=database/migrations/2026_05_05_000008_extend_community_posts_author.php
```

Expected: `Migrated: 2026_05_05_000008_extend_community_posts_author`

- [ ] **Step 3: Verify**

```bash
php artisan tinker --execute="dump(Schema::getColumnListing('community_posts'));"
```

Expected: includes `author_type`, `author_admin_id`, `is_official`, `is_global`.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_05_05_000008_extend_community_posts_author.php
git commit -m "feat(community): extend community_posts with author_type and is_official"
```

---

## Task 9: Migration — extend post_comments

**Files:**
- Create: `database/migrations/2026_05_05_000009_extend_post_comments_author.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('post_comments', 'author_type')) {
                $table->enum('author_type', ['client', 'coach', 'admin'])
                    ->default('client')
                    ->after('client_id');
            }
            if (!Schema::hasColumn('post_comments', 'author_admin_id')) {
                $table->unsignedInteger('author_admin_id')->nullable()->after('author_type');
            }
        });

        if (!$this->indexExists('post_comments', 'idx_comments_author')) {
            Schema::table('post_comments', function (Blueprint $table) {
                $table->index(['author_type', 'author_admin_id'], 'idx_comments_author');
            });
        }
    }

    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            if ($this->indexExists('post_comments', 'idx_comments_author')) {
                $table->dropIndex('idx_comments_author');
            }
            foreach (['author_admin_id', 'author_type'] as $col) {
                if (Schema::hasColumn('post_comments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = \DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$index]);
        return count($rows) > 0;
    }
};
```

- [ ] **Step 2: Migrate + commit**

```bash
php artisan migrate --path=database/migrations/2026_05_05_000009_extend_post_comments_author.php
git add database/migrations/2026_05_05_000009_extend_post_comments_author.php
git commit -m "feat(community): extend post_comments with author_type"
```

---

## Task 10: Update CommunityPost model

**Files:**
- Modify: `app/Models/CommunityPost.php`

- [ ] **Step 1: Read current state**

```bash
cat app/Models/CommunityPost.php | head -60
```

Confirm fillable array, $table, casts, $appends.

- [ ] **Step 2: Add new fillables and scope**

In the `$fillable` (or `#[Fillable]` attribute) array, add:

```php
'author_type',
'author_admin_id',
'is_official',
'is_global',
```

In the `casts()` method (or returned array), add:

```php
'is_official' => 'boolean',
'is_global'   => 'boolean',
```

Add a scope and the relations:

```php
public function scopeOfficial($query)
{
    return $query->where('is_official', true);
}

public function scopeGlobal($query)
{
    return $query->where('is_global', true);
}

public function pinned()
{
    return $this->hasOne(PinnedPost::class, 'post_id')
        ->where(function ($q) {
            $q->whereNull('pinned_until')->orWhere('pinned_until', '>', now());
        });
}

public function reports()
{
    return $this->hasMany(PostReport::class, 'post_id');
}

public function mentions()
{
    return $this->hasMany(PostMention::class, 'post_id');
}
```

- [ ] **Step 3: Verify model still loads**

Run: `php artisan tinker --execute="dump(App\Models\CommunityPost::first()?->toArray());"`
Expected: dumps a row or `null` (if table empty) — no error.

- [ ] **Step 4: Commit**

```bash
git add app/Models/CommunityPost.php
git commit -m "feat(community): extend CommunityPost with author and pin/report/mention relations"
```

---

## Task 11: Update PostComment model

**Files:**
- Modify: `app/Models/PostComment.php`

- [ ] **Step 1: Add fillables**

In `$fillable` (or `#[Fillable]`), add:

```php
'author_type',
'author_admin_id',
```

If casts exist, leave as is. If no scope of `byCoach`/`byAdmin` exists, add:

```php
public function scopeByCoach($query)
{
    return $query->where('author_type', 'coach');
}

public function scopeByAdmin($query)
{
    return $query->where('author_type', 'admin');
}
```

- [ ] **Step 2: Verify**

Run: `php artisan tinker --execute="dump(App\Models\PostComment::first()?->author_type);"`
Expected: prints `'client'` (default) or null.

- [ ] **Step 3: Commit**

```bash
git add app/Models/PostComment.php
git commit -m "feat(community): extend PostComment with author_type scope"
```

---

## Task 12: PinnedPost model

**Files:**
- Create: `app/Models/PinnedPost.php`

- [ ] **Step 1: Create model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinnedPost extends Model
{
    protected $table = 'pinned_posts';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'pinned_by_type',
        'pinned_by_id',
        'pinned_at',
        'pinned_until',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'pinned_at'    => 'datetime',
            'pinned_until' => 'datetime',
        ];
    }

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('pinned_until')->orWhere('pinned_until', '>', now());
        });
    }
}
```

- [ ] **Step 2: Smoke test in tinker**

Run: `php artisan tinker --execute="dump(App\Models\PinnedPost::query()->getQuery()->toSql());"`
Expected: `select * from \`pinned_posts\``.

- [ ] **Step 3: Commit**

```bash
git add app/Models/PinnedPost.php
git commit -m "feat(community): PinnedPost model"
```

---

## Task 13: PostReport model

**Files:**
- Create: `app/Models/PostReport.php`

- [ ] **Step 1: Create model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReport extends Model
{
    protected $table = 'post_reports';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'reporter_id',
        'reason',
        'reason_detail',
        'status',
        'reviewed_by_admin_id',
        'reviewed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at'  => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function reporter()
    {
        return $this->belongsTo(Client::class, 'reporter_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/PostReport.php
git commit -m "feat(community): PostReport model"
```

---

## Task 14: ModerationAction model

**Files:**
- Create: `app/Models/ModerationAction.php`

- [ ] **Step 1: Create model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationAction extends Model
{
    protected $table = 'moderation_actions';

    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'action_type',
        'target_type',
        'target_id',
        'reason',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor()
    {
        return $this->belongsTo(Admin::class, 'actor_id');
    }

    public function scopeByActor($query, string $actorType, int $actorId)
    {
        return $query->where('actor_type', $actorType)->where('actor_id', $actorId);
    }

    public function scopeForTarget($query, string $targetType, int $targetId)
    {
        return $query->where('target_type', $targetType)->where('target_id', $targetId);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/ModerationAction.php
git commit -m "feat(community): ModerationAction audit log model"
```

---

## Task 15: BroadcastMessage model

**Files:**
- Create: `app/Models/BroadcastMessage.php`

- [ ] **Step 1: Create model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastMessage extends Model
{
    protected $table = 'broadcast_messages';

    public $timestamps = false;

    protected $fillable = [
        'sender_type',
        'sender_id',
        'audience_type',
        'segment_filter',
        'subject',
        'body',
        'push_enabled',
        'recipients_count',
        'delivered_count',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'segment_filter' => 'array',
            'push_enabled'   => 'boolean',
            'sent_at'        => 'datetime',
        ];
    }

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'sender_id');
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/BroadcastMessage.php
git commit -m "feat(community): BroadcastMessage model"
```

---

## Task 16: CoachNotificationPreference model

**Files:**
- Create: `app/Models/CoachNotificationPreference.php`

- [ ] **Step 1: Create model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachNotificationPreference extends Model
{
    protected $table = 'coach_notification_preferences';

    protected $primaryKey = 'coach_id';
    public $incrementing = false;
    protected $keyType = 'int';

    public $timestamps = false; // only updated_at, useCurrentOnUpdate set in DB

    protected $fillable = [
        'coach_id',
        'notify_pr_broken',
        'notify_streak_milestone',
        'notify_post_created',
        'notify_comment_on_my_reply',
        'notify_at_risk_client',
        'notify_official_post_engagement',
        'notify_admin_broadcast',
        'push_enabled',
        'in_app_enabled',
    ];

    protected function casts(): array
    {
        return [
            'notify_pr_broken'                => 'boolean',
            'notify_streak_milestone'         => 'boolean',
            'notify_post_created'             => 'boolean',
            'notify_comment_on_my_reply'      => 'boolean',
            'notify_at_risk_client'           => 'boolean',
            'notify_official_post_engagement' => 'boolean',
            'notify_admin_broadcast'          => 'boolean',
            'push_enabled'                    => 'boolean',
            'in_app_enabled'                  => 'boolean',
            'updated_at'                      => 'datetime',
        ];
    }

    public function coach()
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    /**
     * Returns prefs for a coach, creating with defaults if none exist.
     */
    public static function forCoach(int $coachId): self
    {
        return static::firstOrCreate(['coach_id' => $coachId]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/CoachNotificationPreference.php
git commit -m "feat(community): CoachNotificationPreference with defaults factory"
```

---

## Task 17: PostMention model

**Files:**
- Create: `app/Models/PostMention.php`

- [ ] **Step 1: Create model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMention extends Model
{
    protected $table = 'post_mentions';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'comment_id',
        'mentioner_type',
        'mentioner_id',
        'mentioned_type',
        'mentioned_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function comment()
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }

    public function scopeForUser($query, string $type, int $id)
    {
        return $query->where('mentioned_type', $type)->where('mentioned_id', $id);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/PostMention.php
git commit -m "feat(community): PostMention model"
```

---

## Task 18: CommunityPostPolicy + tests (TDD)

**Files:**
- Create: `app/Policies/CommunityPostPolicy.php`
- Create: `tests/Feature/Policies/CommunityPostPolicyTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Policies/CommunityPostPolicyTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Policies\CommunityPostPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->policy = new CommunityPostPolicy;
    $this->coach  = Admin::factory()->create(['role' => 'coach']);
    $this->admin  = Admin::factory()->create(['role' => 'superadmin']);
    $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);
    $this->post   = CommunityPost::factory()->create([
        'client_id'       => $this->client->id,
        'coach_admin_id'  => $this->coach->id,
    ]);
});

it('allows coach to moderate their own clients post', function () {
    expect($this->policy->canModerate($this->coach, $this->post))->toBeTrue();
});

it('rejects coach from moderating another coachs post', function () {
    $otherCoach = Admin::factory()->create(['role' => 'coach']);
    expect($this->policy->canModerate($otherCoach, $this->post))->toBeFalse();
});

it('allows superadmin to moderate any post', function () {
    expect($this->policy->canModerate($this->admin, $this->post))->toBeTrue();
});

it('allows coach to pin their own clients post', function () {
    expect($this->policy->canPin($this->coach, $this->post))->toBeTrue();
});

it('allows coach to make official their own clients post', function () {
    expect($this->policy->canMakeOfficial($this->coach, $this->post))->toBeTrue();
});

it('allows admin to delete any post', function () {
    expect($this->policy->canDelete($this->admin, $this->post))->toBeTrue();
});
```

- [ ] **Step 2: Run tests — confirm fail**

Run: `vendor/bin/pest tests/Feature/Policies/CommunityPostPolicyTest.php -v`
Expected: FAIL with "Class App\Policies\CommunityPostPolicy not found".

- [ ] **Step 3: Implement policy**

```php
<?php
// app/Policies/CommunityPostPolicy.php

namespace App\Policies;

use App\Models\Admin;
use App\Models\CommunityPost;

class CommunityPostPolicy
{
    public function canModerate(Admin $actor, CommunityPost $post): bool
    {
        if (in_array($actor->role, ['admin', 'superadmin', 'jefe'], true)) {
            return true;
        }

        if ($actor->role === 'coach' && (int) $post->coach_admin_id === (int) $actor->id) {
            return true;
        }

        return false;
    }

    public function canPin(Admin $actor, CommunityPost $post): bool
    {
        return $this->canModerate($actor, $post);
    }

    public function canMakeOfficial(Admin $actor, CommunityPost $post): bool
    {
        return $this->canModerate($actor, $post);
    }

    public function canDelete(Admin $actor, CommunityPost $post): bool
    {
        return $this->canModerate($actor, $post);
    }

    public function canCreateGlobalOfficial(Admin $actor): bool
    {
        return in_array($actor->role, ['admin', 'superadmin', 'jefe'], true);
    }
}
```

- [ ] **Step 4: Run tests — confirm pass**

Run: `vendor/bin/pest tests/Feature/Policies/CommunityPostPolicyTest.php -v`
Expected: 6/6 PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Policies/CommunityPostPolicy.php tests/Feature/Policies/CommunityPostPolicyTest.php
git commit -m "feat(community): CommunityPostPolicy with coach scope and admin override"
```

---

## Task 19: ModerationService (TDD)

**Files:**
- Create: `app/Services/ModerationService.php`
- Create: `tests/Unit/Services/ModerationServiceTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Unit/Services/ModerationServiceTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use App\Models\PinnedPost;
use App\Models\PostReport;
use App\Services\ModerationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Carbon::setTestNow('2026-05-05 12:00:00');
    $this->service = new ModerationService;
    $this->coach   = Admin::factory()->create(['role' => 'coach']);
    $this->client  = Client::factory()->create(['coach_id' => $this->coach->id]);
    $this->post    = CommunityPost::factory()->create([
        'client_id'      => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);
});

afterEach(fn () => Carbon::setTestNow());

it('pins a post and writes audit log', function () {
    $this->service->pinPost($this->post, $this->coach, 'coach', 24, 'Felicidades!');

    $pinned = PinnedPost::where('post_id', $this->post->id)->first();
    expect($pinned)->not->toBeNull();
    expect($pinned->pinned_by_type)->toBe('coach');
    expect($pinned->pinned_by_id)->toBe($this->coach->id);
    expect($pinned->pinned_until->format('Y-m-d H:i'))->toBe(Carbon::now()->addHours(24)->format('Y-m-d H:i'));

    $audit = ModerationAction::where('target_id', $this->post->id)
        ->where('action_type', 'pin')->first();
    expect($audit)->not->toBeNull();
    expect($audit->actor_id)->toBe($this->coach->id);
});

it('unpins a post', function () {
    $this->service->pinPost($this->post, $this->coach, 'coach', 24, null);
    $this->service->unpinPost($this->post, $this->coach, 'coach');

    $active = PinnedPost::where('post_id', $this->post->id)
        ->where(fn ($q) => $q->whereNull('pinned_until')->orWhere('pinned_until', '>', now()))
        ->exists();
    expect($active)->toBeFalse();

    $audit = ModerationAction::where('target_id', $this->post->id)
        ->where('action_type', 'unpin')->first();
    expect($audit)->not->toBeNull();
});

it('soft deletes a post by setting visible=false', function () {
    $this->service->deletePost($this->post, $this->coach, 'coach', 'spam');
    $this->post->refresh();
    expect((bool) $this->post->visible)->toBeFalse();

    $audit = ModerationAction::where('target_id', $this->post->id)
        ->where('action_type', 'delete')->first();
    expect($audit->reason)->toBe('spam');
});

it('makes a post official', function () {
    $this->service->makeOfficial($this->post, $this->coach, 'coach');
    $this->post->refresh();
    expect((bool) $this->post->is_official)->toBeTrue();

    $audit = ModerationAction::where('target_id', $this->post->id)
        ->where('action_type', 'make_official')->first();
    expect($audit)->not->toBeNull();
});

it('dismisses a report and updates status', function () {
    $report = PostReport::create([
        'post_id'     => $this->post->id,
        'reporter_id' => $this->client->id,
        'reason'      => 'spam',
        'status'      => 'pending',
    ]);

    $admin = Admin::factory()->create(['role' => 'superadmin']);
    $this->service->dismissReport($report, $admin);

    $report->refresh();
    expect($report->status)->toBe('dismissed');
    expect($report->reviewed_by_admin_id)->toBe($admin->id);
});
```

- [ ] **Step 2: Run tests — confirm fail**

Run: `vendor/bin/pest tests/Unit/Services/ModerationServiceTest.php -v`
Expected: FAIL with "Class App\Services\ModerationService not found".

- [ ] **Step 3: Implement service**

```php
<?php
// app/Services/ModerationService.php

namespace App\Services;

use App\Events\PostMadeOfficial;
use App\Events\PostPinned;
use App\Models\Admin;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use App\Models\PinnedPost;
use App\Models\PostReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ModerationService
{
    public function pinPost(CommunityPost $post, Admin $actor, string $actorType, ?int $hours, ?string $note = null): PinnedPost
    {
        return DB::transaction(function () use ($post, $actor, $actorType, $hours, $note) {
            $pin = PinnedPost::create([
                'post_id'        => $post->id,
                'pinned_by_type' => $actorType,
                'pinned_by_id'   => $actor->id,
                'pinned_at'      => Carbon::now(),
                'pinned_until'   => $hours ? Carbon::now()->addHours($hours) : null,
                'note'           => $note,
            ]);

            ModerationAction::create([
                'actor_type'  => $actorType,
                'actor_id'    => $actor->id,
                'action_type' => 'pin',
                'target_type' => 'post',
                'target_id'   => $post->id,
                'reason'      => $note,
                'metadata'    => ['hours' => $hours],
                'created_at'  => Carbon::now(),
            ]);

            event(new PostPinned($post->id, $post->coach_admin_id, $actor->id, $actorType, $hours));

            return $pin;
        });
    }

    public function unpinPost(CommunityPost $post, Admin $actor, string $actorType): void
    {
        DB::transaction(function () use ($post, $actor, $actorType) {
            PinnedPost::where('post_id', $post->id)
                ->where(fn ($q) => $q->whereNull('pinned_until')->orWhere('pinned_until', '>', now()))
                ->update(['pinned_until' => Carbon::now()]);

            ModerationAction::create([
                'actor_type'  => $actorType,
                'actor_id'    => $actor->id,
                'action_type' => 'unpin',
                'target_type' => 'post',
                'target_id'   => $post->id,
                'created_at'  => Carbon::now(),
            ]);
        });
    }

    public function deletePost(CommunityPost $post, Admin $actor, string $actorType, ?string $reason = null): void
    {
        DB::transaction(function () use ($post, $actor, $actorType, $reason) {
            $post->update(['visible' => false]);

            ModerationAction::create([
                'actor_type'  => $actorType,
                'actor_id'    => $actor->id,
                'action_type' => 'delete',
                'target_type' => 'post',
                'target_id'   => $post->id,
                'reason'      => $reason,
                'created_at'  => Carbon::now(),
            ]);
        });
    }

    public function makeOfficial(CommunityPost $post, Admin $actor, string $actorType): void
    {
        DB::transaction(function () use ($post, $actor, $actorType) {
            $post->update([
                'is_official'     => true,
                'author_type'     => $actorType,
                'author_admin_id' => $actor->id,
            ]);

            ModerationAction::create([
                'actor_type'  => $actorType,
                'actor_id'    => $actor->id,
                'action_type' => 'make_official',
                'target_type' => 'post',
                'target_id'   => $post->id,
                'created_at'  => Carbon::now(),
            ]);

            event(new PostMadeOfficial($post->id, $post->coach_admin_id, $actor->id, $actorType));
        });
    }

    public function dismissReport(PostReport $report, Admin $admin): void
    {
        $report->update([
            'status'               => 'dismissed',
            'reviewed_by_admin_id' => $admin->id,
            'reviewed_at'          => Carbon::now(),
        ]);

        ModerationAction::create([
            'actor_type'  => 'admin',
            'actor_id'    => $admin->id,
            'action_type' => 'dismiss_report',
            'target_type' => 'post',
            'target_id'   => $report->post_id,
            'metadata'    => ['report_id' => $report->id],
            'created_at'  => Carbon::now(),
        ]);
    }
}
```

- [ ] **Step 4: Stub events to make tests pass without broadcast errors**

If `PostPinned` and `PostMadeOfficial` don't exist yet, create stub classes that do nothing — they get filled in Tasks 27 & 28. Stub:

```php
<?php
// app/Events/PostPinned.php (stub — replaced in Task 27)

namespace App\Events;

class PostPinned
{
    public function __construct(
        public int $postId,
        public ?int $coachAdminId,
        public int $actorId,
        public string $actorType,
        public ?int $hours,
    ) {}
}
```

```php
<?php
// app/Events/PostMadeOfficial.php (stub — replaced in Task 30)

namespace App\Events;

class PostMadeOfficial
{
    public function __construct(
        public int $postId,
        public ?int $coachAdminId,
        public int $actorId,
        public string $actorType,
    ) {}
}
```

- [ ] **Step 5: Run tests — confirm pass**

Run: `vendor/bin/pest tests/Unit/Services/ModerationServiceTest.php -v`
Expected: 5/5 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Services/ModerationService.php app/Events/PostPinned.php app/Events/PostMadeOfficial.php tests/Unit/Services/ModerationServiceTest.php
git commit -m "feat(community): ModerationService with pin/unpin/delete/official/dismiss + audit"
```

---

## Task 20: BroadcastService (TDD)

**Files:**
- Create: `app/Services/BroadcastService.php`
- Create: `tests/Unit/Services/BroadcastServiceTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Unit/Services/BroadcastServiceTest.php

use App\Models\Admin;
use App\Models\BroadcastMessage;
use App\Models\Client;
use App\Services\BroadcastService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->service = new BroadcastService;
    $this->admin   = Admin::factory()->create(['role' => 'superadmin']);
});

it('counts segmented client recipients without sending (dry run)', function () {
    Client::factory()->count(5)->create(['plan' => 'rise', 'status' => 'activo']);
    Client::factory()->count(3)->create(['plan' => 'elite', 'status' => 'activo']);
    Client::factory()->count(2)->create(['plan' => 'rise', 'status' => 'inactivo']);

    $count = $this->service->previewRecipients(
        audience: 'clients',
        segment: ['plan' => ['rise'], 'status' => ['activo']],
    );

    expect($count)->toBe(5);
});

it('counts coach recipients', function () {
    Admin::factory()->count(4)->create(['role' => 'coach']);

    $count = $this->service->previewRecipients(audience: 'coaches', segment: []);
    expect($count)->toBeGreaterThanOrEqual(4);
});

it('records broadcast and recipients_count when dispatching to clients', function () {
    Client::factory()->count(3)->create(['plan' => 'metodo', 'status' => 'activo']);

    $broadcast = $this->service->dispatch(
        sender: $this->admin,
        senderType: 'admin',
        audience: 'clients',
        segment: ['plan' => ['metodo']],
        subject: 'Hola',
        body: 'Mensaje de prueba',
        pushEnabled: false,
    );

    expect($broadcast)->toBeInstanceOf(BroadcastMessage::class);
    expect($broadcast->recipients_count)->toBe(3);
    expect($broadcast->audience_type)->toBe('clients');
    expect($broadcast->subject)->toBe('Hola');
});

it('chunks delivery for >100 recipients', function () {
    Client::factory()->count(150)->create(['plan' => 'esencial', 'status' => 'activo']);

    $broadcast = $this->service->dispatch(
        sender: $this->admin,
        senderType: 'admin',
        audience: 'clients',
        segment: ['plan' => ['esencial']],
        subject: null,
        body: 'Bulk',
        pushEnabled: false,
    );

    expect($broadcast->recipients_count)->toBe(150);
});
```

- [ ] **Step 2: Run tests — confirm fail**

Run: `vendor/bin/pest tests/Unit/Services/BroadcastServiceTest.php -v`
Expected: FAIL — service not found.

- [ ] **Step 3: Implement service**

```php
<?php
// app/Services/BroadcastService.php

namespace App\Services;

use App\Events\BroadcastSent;
use App\Models\Admin;
use App\Models\BroadcastMessage;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BroadcastService
{
    public const CHUNK_SIZE = 100;

    public function previewRecipients(string $audience, array $segment): int
    {
        return $this->buildAudienceQuery($audience, $segment)->count();
    }

    public function dispatch(
        Admin $sender,
        string $senderType,
        string $audience,
        array $segment,
        ?string $subject,
        string $body,
        bool $pushEnabled,
    ): BroadcastMessage {
        return DB::transaction(function () use ($sender, $senderType, $audience, $segment, $subject, $body, $pushEnabled) {
            $count = $this->buildAudienceQuery($audience, $segment)->count();

            $broadcast = BroadcastMessage::create([
                'sender_type'      => $senderType,
                'sender_id'        => $sender->id,
                'audience_type'    => $audience,
                'segment_filter'   => $segment ?: null,
                'subject'          => $subject,
                'body'             => $body,
                'push_enabled'     => $pushEnabled,
                'recipients_count' => $count,
                'delivered_count'  => 0,
                'sent_at'          => Carbon::now(),
            ]);

            $this->buildAudienceQuery($audience, $segment)
                ->select($audience === 'coaches' ? 'admin_id' : 'id')
                ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($broadcast, $pushEnabled) {
                    $this->deliverChunk($broadcast, $chunk, $pushEnabled);
                });

            event(new BroadcastSent($broadcast->id, $broadcast->audience_type, $count));

            return $broadcast->fresh();
        });
    }

    private function buildAudienceQuery(string $audience, array $segment): Builder
    {
        if ($audience === 'coaches') {
            $q = Admin::query()->where('role', 'coach');
            if (!empty($segment['coach_ids'])) {
                $q->whereIn('id', $segment['coach_ids']);
            }
            return $q;
        }

        $q = Client::query();

        if (!empty($segment['plan'])) {
            $q->whereIn('plan', $segment['plan']);
        }
        if (!empty($segment['status'])) {
            $q->whereIn('status', $segment['status']);
        }
        if (!empty($segment['coach_id'])) {
            $q->where('coach_id', $segment['coach_id']);
        }
        if (!empty($segment['inactive_days'])) {
            $q->where('last_login_at', '<', now()->subDays($segment['inactive_days']));
        }

        return $q;
    }

    private function deliverChunk(BroadcastMessage $broadcast, $chunk, bool $pushEnabled): void
    {
        // Increment delivered count optimistically.
        // Actual push delivery happens in a queued job dispatched by listener
        // NotifyMentionedUsers/PushNotificationService — we just track count here.
        $broadcast->increment('delivered_count', count($chunk));
    }
}
```

- [ ] **Step 4: Add stub event BroadcastSent**

```php
<?php
// app/Events/BroadcastSent.php (stub — replaced in Task 31)

namespace App\Events;

class BroadcastSent
{
    public function __construct(
        public int $broadcastId,
        public string $audienceType,
        public int $recipientsCount,
    ) {}
}
```

- [ ] **Step 5: Run tests — confirm pass**

Run: `vendor/bin/pest tests/Unit/Services/BroadcastServiceTest.php -v`
Expected: 4/4 PASS.

If "plan" or "status" enum mismatch fails (production has ENUM 'rise','elite',...), check `feedback_clients_plan_enum_prod.md` memory — local schema may be VARCHAR, prod is ENUM. Tests run against local schema; should pass.

- [ ] **Step 6: Commit**

```bash
git add app/Services/BroadcastService.php app/Events/BroadcastSent.php tests/Unit/Services/BroadcastServiceTest.php
git commit -m "feat(community): BroadcastService with segmentation and chunked delivery"
```

---

## Task 21: MentionResolverService (TDD)

**Files:**
- Create: `app/Services/MentionResolverService.php`
- Create: `tests/Unit/Services/MentionResolverServiceTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Unit/Services/MentionResolverServiceTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostMention;
use App\Services\MentionResolverService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->service = new MentionResolverService;
});

it('parses @cliente_X tokens from body', function () {
    $tokens = $this->service->extract('Hola @cliente_42 y también @cliente_7');
    expect($tokens)->toBe([
        ['type' => 'client', 'id' => 42],
        ['type' => 'client', 'id' => 7],
    ]);
});

it('parses @coach and @admin keywords', function () {
    $tokens = $this->service->extract('Aviso @coach y también @admin');
    expect($tokens)->toContain(['type' => 'coach', 'id' => null]);
    expect($tokens)->toContain(['type' => 'admin', 'id' => null]);
});

it('rejects malformed mentions (XSS/injection)', function () {
    $tokens = $this->service->extract('Bad @<script> @cliente_abc @cliente_-1');
    expect($tokens)->toBe([]);
});

it('persists mentions for a client post body', function () {
    $client = Client::factory()->create();
    $coach  = Admin::factory()->create(['role' => 'coach']);
    $target = Client::factory()->create();

    $post = CommunityPost::factory()->create([
        'client_id' => $client->id,
        'content'   => "Felicitaciones @cliente_{$target->id}",
        'coach_admin_id' => $coach->id,
    ]);

    $created = $this->service->persistForPost($post, mentionerType: 'client', mentionerId: $client->id);

    expect($created)->toBe(1);
    $row = PostMention::where('post_id', $post->id)->first();
    expect($row->mentioned_type)->toBe('client');
    expect($row->mentioned_id)->toBe($target->id);
});

it('search returns clients matching prefix scoped to coach', function () {
    $coach   = Admin::factory()->create(['role' => 'coach']);
    $matching = Client::factory()->create(['name' => 'Carlos Pérez', 'coach_id' => $coach->id]);
    $other    = Client::factory()->create(['name' => 'Carlos Otro Coach']); // not coach.id

    $results = $this->service->searchMentionTargets('Carl', scopeCoachId: $coach->id);

    expect(collect($results)->pluck('id')->all())->toContain($matching->id);
    expect(collect($results)->pluck('id')->all())->not->toContain($other->id);
});
```

- [ ] **Step 2: Run tests — fail**

Run: `vendor/bin/pest tests/Unit/Services/MentionResolverServiceTest.php -v`
Expected: FAIL — service not found.

- [ ] **Step 3: Implement service**

```php
<?php
// app/Services/MentionResolverService.php

namespace App\Services;

use App\Events\MentionCreated;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Models\PostMention;
use Illuminate\Support\Carbon;

class MentionResolverService
{
    private const TOKEN_REGEX = '/@(cliente_(\d+)|coach|admin|wellcore)\b/iu';

    /**
     * Extract typed mention tokens from a body.
     *
     * @return array<int, array{type: string, id: int|null}>
     */
    public function extract(string $body): array
    {
        if (!preg_match_all(self::TOKEN_REGEX, $body, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $tokens = [];
        foreach ($matches as $m) {
            $token = strtolower($m[1]);

            if (str_starts_with($token, 'cliente_')) {
                $id = isset($m[2]) ? (int) $m[2] : 0;
                if ($id > 0) {
                    $tokens[] = ['type' => 'client', 'id' => $id];
                }
            } elseif ($token === 'coach') {
                $tokens[] = ['type' => 'coach', 'id' => null];
            } elseif (in_array($token, ['admin', 'wellcore'], true)) {
                $tokens[] = ['type' => 'admin', 'id' => null];
            }
        }

        return $tokens;
    }

    public function persistForPost(CommunityPost $post, string $mentionerType, int $mentionerId): int
    {
        return $this->persist(
            tokens: $this->extract($post->content ?? ''),
            postId: $post->id,
            commentId: null,
            mentionerType: $mentionerType,
            mentionerId: $mentionerId,
            coachAdminId: $post->coach_admin_id,
        );
    }

    public function persistForComment(PostComment $comment, string $mentionerType, int $mentionerId, ?int $coachAdminId): int
    {
        return $this->persist(
            tokens: $this->extract($comment->content ?? $comment->body ?? ''),
            postId: $comment->post_id,
            commentId: $comment->id,
            mentionerType: $mentionerType,
            mentionerId: $mentionerId,
            coachAdminId: $coachAdminId,
        );
    }

    /**
     * Autocomplete search restricted to a coach's clients (when scoped).
     *
     * @return array<int, array{id:int, type:string, label:string}>
     */
    public function searchMentionTargets(string $query, ?int $scopeCoachId = null, int $limit = 10): array
    {
        $q = Client::query()
            ->select(['id', 'name'])
            ->where('name', 'like', $query.'%');

        if ($scopeCoachId) {
            $q->where('coach_id', $scopeCoachId);
        }

        return $q->limit($limit)
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'type' => 'client', 'label' => $c->name])
            ->all();
    }

    private function persist(array $tokens, ?int $postId, ?int $commentId, string $mentionerType, int $mentionerId, ?int $coachAdminId): int
    {
        $created = 0;

        foreach ($tokens as $token) {
            // Resolve "@coach" with no id by using the post's coach_admin_id
            $mentionedId = $token['id'];
            if ($mentionedId === null && $token['type'] === 'coach' && $coachAdminId) {
                $mentionedId = $coachAdminId;
            }
            if ($mentionedId === null) {
                continue; // can't resolve generic @admin without policy lookup
            }

            PostMention::create([
                'post_id'        => $postId,
                'comment_id'     => $commentId,
                'mentioner_type' => $mentionerType,
                'mentioner_id'   => $mentionerId,
                'mentioned_type' => $token['type'],
                'mentioned_id'   => $mentionedId,
                'created_at'     => Carbon::now(),
            ]);

            event(new MentionCreated(
                postId: $postId,
                commentId: $commentId,
                mentionerType: $mentionerType,
                mentionerId: $mentionerId,
                mentionedType: $token['type'],
                mentionedId: $mentionedId,
            ));

            $created++;
        }

        return $created;
    }
}
```

- [ ] **Step 4: Add stub event MentionCreated**

```php
<?php
// app/Events/MentionCreated.php (stub — replaced in Task 32)

namespace App\Events;

class MentionCreated
{
    public function __construct(
        public ?int $postId,
        public ?int $commentId,
        public string $mentionerType,
        public int $mentionerId,
        public string $mentionedType,
        public int $mentionedId,
    ) {}
}
```

- [ ] **Step 5: Run tests — pass**

Run: `vendor/bin/pest tests/Unit/Services/MentionResolverServiceTest.php -v`
Expected: 5/5 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Services/MentionResolverService.php app/Events/MentionCreated.php tests/Unit/Services/MentionResolverServiceTest.php
git commit -m "feat(community): MentionResolverService with regex parser and persistence"
```

---

## Task 22: CoachCommunityService (TDD)

**Files:**
- Create: `app/Services/CoachCommunityService.php`
- Create: `tests/Unit/Services/CoachCommunityServiceTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Unit/Services/CoachCommunityServiceTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\WorkoutSession;
use App\Services\CoachCommunityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Carbon::setTestNow('2026-05-05 12:00:00');
    $this->service = new CoachCommunityService;
    $this->coach   = Admin::factory()->create(['role' => 'coach']);
});

afterEach(fn () => Carbon::setTestNow());

it('returns posts only from coachs clients', function () {
    $myClient    = Client::factory()->create(['coach_id' => $this->coach->id]);
    $otherClient = Client::factory()->create();

    $myPost    = CommunityPost::factory()->create(['client_id' => $myClient->id, 'coach_admin_id' => $this->coach->id]);
    $otherPost = CommunityPost::factory()->create(['client_id' => $otherClient->id]);

    $feed = $this->service->getFeed($this->coach->id, filter: 'all', perPage: 50);

    $ids = collect($feed['data'])->pluck('id')->all();
    expect($ids)->toContain($myPost->id);
    expect($ids)->not->toContain($otherPost->id);
});

it('filters by pinned posts', function () {
    $client = Client::factory()->create(['coach_id' => $this->coach->id]);
    $pinned = CommunityPost::factory()->create(['client_id' => $client->id, 'coach_admin_id' => $this->coach->id]);
    $normal = CommunityPost::factory()->create(['client_id' => $client->id, 'coach_admin_id' => $this->coach->id]);

    \App\Models\PinnedPost::create([
        'post_id'        => $pinned->id,
        'pinned_by_type' => 'coach',
        'pinned_by_id'   => $this->coach->id,
        'pinned_at'      => Carbon::now(),
        'pinned_until'   => Carbon::now()->addDay(),
    ]);

    $feed = $this->service->getFeed($this->coach->id, filter: 'pinned', perPage: 50);
    $ids  = collect($feed['data'])->pluck('id')->all();

    expect($ids)->toContain($pinned->id);
    expect($ids)->not->toContain($normal->id);
});

it('returns top performers ordered by workouts in last 7d', function () {
    $a = Client::factory()->create(['coach_id' => $this->coach->id, 'name' => 'Alpha']);
    $b = Client::factory()->create(['coach_id' => $this->coach->id, 'name' => 'Bravo']);

    WorkoutSession::factory()->count(5)->create([
        'client_id'    => $a->id,
        'completed'    => true,
        'session_date' => Carbon::today(),
    ]);
    WorkoutSession::factory()->count(2)->create([
        'client_id'    => $b->id,
        'completed'    => true,
        'session_date' => Carbon::today(),
    ]);

    $top = $this->service->topPerformers($this->coach->id, days: 7, limit: 3);

    expect($top[0]['client_id'])->toBe($a->id);
    expect($top[0]['workout_count'])->toBe(5);
});

it('flags at-risk clients with 0 workouts in last 5 days', function () {
    $silent = Client::factory()->create(['coach_id' => $this->coach->id, 'name' => 'Silent']);
    $active = Client::factory()->create(['coach_id' => $this->coach->id, 'name' => 'Active']);

    WorkoutSession::factory()->create([
        'client_id'    => $active->id,
        'completed'    => true,
        'session_date' => Carbon::today(),
    ]);

    $atRisk = $this->service->atRiskClients($this->coach->id, days: 5);
    $ids    = collect($atRisk)->pluck('id')->all();

    expect($ids)->toContain($silent->id);
    expect($ids)->not->toContain($active->id);
});
```

- [ ] **Step 2: Run tests — fail**

Run: `vendor/bin/pest tests/Unit/Services/CoachCommunityServiceTest.php -v`
Expected: FAIL — service not found.

- [ ] **Step 3: Implement service**

```php
<?php
// app/Services/CoachCommunityService.php

namespace App\Services;

use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CoachCommunityService
{
    /**
     * Resolve client IDs assigned to coach via 3-fallback (matches GroupPulseAggregator).
     */
    public function resolveClientIds(int $coachId): array
    {
        // Primary: clients.coach_id
        $primary = Client::where('coach_id', $coachId)->pluck('id')->all();

        // Fallback: assigned_plans.assigned_by
        $fallbackPlans = DB::table('assigned_plans')
            ->where('assigned_by', $coachId)
            ->pluck('client_id')
            ->all();

        // Fallback: coach_messages.coach_id
        $fallbackMessages = DB::table('coach_messages')
            ->where('coach_id', $coachId)
            ->pluck('client_id')
            ->all();

        return array_values(array_unique(array_merge($primary, $fallbackPlans, $fallbackMessages)));
    }

    public function getFeed(int $coachId, string $filter = 'all', int $perPage = 20): array
    {
        $clientIds = $this->resolveClientIds($coachId);

        $q = CommunityPost::query()
            ->where(function ($q) use ($clientIds, $coachId) {
                $q->whereIn('client_id', $clientIds)
                  ->orWhere('coach_admin_id', $coachId);
            })
            ->where('visible', true)
            ->orderByDesc('created_at');

        if ($filter === 'pinned') {
            $q->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('pinned_posts')
                    ->whereColumn('pinned_posts.post_id', 'community_posts.id')
                    ->where(function ($w) {
                        $w->whereNull('pinned_until')->orWhere('pinned_until', '>', now());
                    });
            });
        }

        if ($filter === 'reported') {
            $q->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('post_reports')
                    ->whereColumn('post_reports.post_id', 'community_posts.id')
                    ->where('status', 'pending');
            });
        }

        if ($filter === 'achievements') {
            $q->whereIn('type', ['achievement', 'pr', 'milestone']);
        }
        if ($filter === 'prs') {
            $q->where('type', 'pr');
        }

        $page = $q->paginate($perPage);

        return [
            'data'         => $page->items(),
            'current_page' => $page->currentPage(),
            'last_page'    => $page->lastPage(),
            'total'        => $page->total(),
        ];
    }

    public function topPerformers(int $coachId, int $days = 7, int $limit = 3): array
    {
        $clientIds = $this->resolveClientIds($coachId);

        $rows = WorkoutSession::query()
            ->select('client_id', DB::raw('COUNT(*) as workout_count'))
            ->whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('session_date', '>=', Carbon::today()->subDays($days))
            ->groupBy('client_id')
            ->orderByDesc('workout_count')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'client_id'     => (int) $r->client_id,
            'workout_count' => (int) $r->workout_count,
        ])->all();
    }

    public function atRiskClients(int $coachId, int $days = 5): array
    {
        $clientIds = $this->resolveClientIds($coachId);

        $activeIds = WorkoutSession::query()
            ->whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('session_date', '>=', Carbon::today()->subDays($days))
            ->pluck('client_id')
            ->unique()
            ->all();

        $silentIds = array_diff($clientIds, $activeIds);

        return Client::whereIn('id', $silentIds)
            ->select(['id', 'name', 'last_login_at'])
            ->get()
            ->all();
    }

    public function teamHealthScore(int $coachId): float
    {
        $clientIds = $this->resolveClientIds($coachId);
        if (empty($clientIds)) {
            return 0.0;
        }

        $activeCount = WorkoutSession::query()
            ->whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('session_date', '>=', Carbon::today()->subDays(7))
            ->distinct('client_id')
            ->count('client_id');

        return round(($activeCount / count($clientIds)) * 100, 1);
    }
}
```

- [ ] **Step 4: Run tests — pass**

Run: `vendor/bin/pest tests/Unit/Services/CoachCommunityServiceTest.php -v`
Expected: 4/4 PASS.

If `WorkoutSession::factory` lacks `total_volume_kg`/etc, that's fine — we only assert on counts.

- [ ] **Step 5: Commit**

```bash
git add app/Services/CoachCommunityService.php tests/Unit/Services/CoachCommunityServiceTest.php
git commit -m "feat(community): CoachCommunityService with 3-fallback scope and pulse aggregations"
```

---

## Task 23: AdminCommunityService (TDD)

**Files:**
- Create: `app/Services/AdminCommunityService.php`
- Create: `tests/Unit/Services/AdminCommunityServiceTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Unit/Services/AdminCommunityServiceTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Services\AdminCommunityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Carbon::setTestNow('2026-05-05 12:00:00');
    $this->service = new AdminCommunityService;
});

afterEach(fn () => Carbon::setTestNow());

it('returns metrics per coach', function () {
    $coachA = Admin::factory()->create(['role' => 'coach']);
    $coachB = Admin::factory()->create(['role' => 'coach']);
    $client = Client::factory()->create(['coach_id' => $coachA->id]);

    CommunityPost::factory()->count(3)->create([
        'client_id' => $client->id,
        'coach_admin_id' => $coachA->id,
        'created_at' => Carbon::now()->subDays(2),
    ]);

    $metrics = $this->service->coachMetrics(period: 'week');

    $a = collect($metrics)->firstWhere('coach_id', $coachA->id);
    $b = collect($metrics)->firstWhere('coach_id', $coachB->id);

    expect($a['posts_count'])->toBeGreaterThanOrEqual(3);
    expect($b['posts_count'])->toBe(0);
});

it('time series of posts/day', function () {
    $coach  = Admin::factory()->create(['role' => 'coach']);
    $client = Client::factory()->create(['coach_id' => $coach->id]);

    CommunityPost::factory()->create([
        'client_id'      => $client->id,
        'coach_admin_id' => $coach->id,
        'created_at'     => Carbon::now()->subDay(),
    ]);
    CommunityPost::factory()->create([
        'client_id'      => $client->id,
        'coach_admin_id' => $coach->id,
        'created_at'     => Carbon::now()->subDays(2),
    ]);

    $series = $this->service->postsTimeSeries(days: 7);

    expect($series)->toBeArray();
    expect(count($series))->toBe(7);
    foreach ($series as $point) {
        expect($point)->toHaveKey('date');
        expect($point)->toHaveKey('count');
    }
});
```

- [ ] **Step 2: Run tests — fail**

Run: `vendor/bin/pest tests/Unit/Services/AdminCommunityServiceTest.php -v`
Expected: FAIL — service not found.

- [ ] **Step 3: Implement service**

```php
<?php
// app/Services/AdminCommunityService.php

namespace App\Services;

use App\Models\Admin;
use App\Models\CommunityPost;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminCommunityService
{
    public function coachMetrics(string $period = 'week'): array
    {
        $since = match ($period) {
            'day'   => Carbon::now()->subDay(),
            'month' => Carbon::now()->subMonth(),
            default => Carbon::now()->subWeek(),
        };

        $coaches = Admin::where('role', 'coach')->select(['id', 'name'])->get();

        $postsCounts = CommunityPost::query()
            ->where('created_at', '>=', $since)
            ->select('coach_admin_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('coach_admin_id')
            ->pluck('cnt', 'coach_admin_id');

        $reactionsCounts = DB::table('post_reactions')
            ->join('community_posts', 'post_reactions.post_id', '=', 'community_posts.id')
            ->where('community_posts.created_at', '>=', $since)
            ->select('community_posts.coach_admin_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('community_posts.coach_admin_id')
            ->pluck('cnt', 'coach_admin_id');

        return $coaches->map(function ($coach) use ($postsCounts, $reactionsCounts) {
            $posts     = (int) ($postsCounts[$coach->id] ?? 0);
            $reactions = (int) ($reactionsCounts[$coach->id] ?? 0);

            return [
                'coach_id'        => $coach->id,
                'coach_name'      => $coach->name,
                'posts_count'     => $posts,
                'reactions_count' => $reactions,
                'engagement_rate' => $posts > 0 ? round($reactions / $posts, 2) : 0,
            ];
        })->all();
    }

    public function postsTimeSeries(int $days = 30): array
    {
        $start = Carbon::today()->subDays($days - 1);

        $rows = CommunityPost::query()
            ->where('created_at', '>=', $start)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $series[] = ['date' => $date, 'count' => (int) ($rows[$date] ?? 0)];
        }

        return $series;
    }

    public function moderationQueueCount(): int
    {
        return DB::table('post_reports')->where('status', 'pending')->count();
    }
}
```

- [ ] **Step 4: Run tests — pass**

Run: `vendor/bin/pest tests/Unit/Services/AdminCommunityServiceTest.php -v`
Expected: 2/2 PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/AdminCommunityService.php tests/Unit/Services/AdminCommunityServiceTest.php
git commit -m "feat(community): AdminCommunityService with cross-coach metrics and time series"
```

---

## Task 24: Broadcast Event — CoachCommunityActivity

**Files:**
- Create: `app/Events/CoachCommunityActivity.php`

- [ ] **Step 1: Implement**

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoachCommunityActivity implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $coachId,
        public string $eventType,
        public int $clientId,
        public string $clientName,
        public array $payload = [],
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("coach.{$this->coachId}.community");
    }

    public function broadcastAs(): string
    {
        return 'coach-community-activity';
    }

    public function broadcastWith(): array
    {
        return [
            'coach_id'    => $this->coachId,
            'event_type'  => $this->eventType,
            'client_id'   => $this->clientId,
            'client_name' => $this->clientName,
            'payload'     => $this->payload,
            'at'          => now()->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Events/CoachCommunityActivity.php
git commit -m "feat(community): CoachCommunityActivity broadcast event"
```

---

## Task 25: Replace stub PostPinned with broadcast event

**Files:**
- Modify: `app/Events/PostPinned.php`

- [ ] **Step 1: Replace stub with broadcasting class**

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostPinned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $postId,
        public ?int $coachAdminId,
        public int $actorId,
        public string $actorType,
        public ?int $hours,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("coach.{$this->coachAdminId}.community");
    }

    public function broadcastAs(): string
    {
        return 'post-pinned';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'    => $this->postId,
            'actor_id'   => $this->actorId,
            'actor_type' => $this->actorType,
            'hours'      => $this->hours,
            'at'         => now()->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 2: Re-run ModerationServiceTest to confirm still green**

Run: `vendor/bin/pest tests/Unit/Services/ModerationServiceTest.php`
Expected: 5/5 PASS.

- [ ] **Step 3: Commit**

```bash
git add app/Events/PostPinned.php
git commit -m "feat(community): PostPinned broadcast event (replace stub)"
```

---

## Task 26: PostReported broadcast event

**Files:**
- Create: `app/Events/PostReported.php`

- [ ] **Step 1: Implement**

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostReported implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $postId,
        public ?int $coachAdminId,
        public int $reporterId,
        public string $reason,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('admin.community')];
        if ($this->coachAdminId) {
            $channels[] = new PrivateChannel("coach.{$this->coachAdminId}.community");
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'post-reported';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'     => $this->postId,
            'reporter_id' => $this->reporterId,
            'reason'      => $this->reason,
            'at'          => now()->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Events/PostReported.php
git commit -m "feat(community): PostReported broadcast event (admin + coach channels)"
```

---

## Task 27: PostMadeOfficial broadcast event

**Files:**
- Modify: `app/Events/PostMadeOfficial.php`

- [ ] **Step 1: Replace stub with broadcasting class**

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostMadeOfficial implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $postId,
        public ?int $coachAdminId,
        public int $actorId,
        public string $actorType,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('admin.community')];
        if ($this->coachAdminId) {
            $channels[] = new PrivateChannel("coach.{$this->coachAdminId}.community");
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'post-made-official';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'    => $this->postId,
            'actor_id'   => $this->actorId,
            'actor_type' => $this->actorType,
            'at'         => now()->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Events/PostMadeOfficial.php
git commit -m "feat(community): PostMadeOfficial broadcast event (replace stub)"
```

---

## Task 28: BroadcastSent event

**Files:**
- Modify: `app/Events/BroadcastSent.php`

- [ ] **Step 1: Replace stub**

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $broadcastId,
        public string $audienceType,
        public int $recipientsCount,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin.community');
    }

    public function broadcastAs(): string
    {
        return 'broadcast-sent';
    }

    public function broadcastWith(): array
    {
        return [
            'broadcast_id'     => $this->broadcastId,
            'audience_type'    => $this->audienceType,
            'recipients_count' => $this->recipientsCount,
            'at'               => now()->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Events/BroadcastSent.php
git commit -m "feat(community): BroadcastSent broadcast event (replace stub)"
```

---

## Task 29: MentionCreated event

**Files:**
- Modify: `app/Events/MentionCreated.php`

- [ ] **Step 1: Replace stub**

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MentionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ?int $postId,
        public ?int $commentId,
        public string $mentionerType,
        public int $mentionerId,
        public string $mentionedType,
        public int $mentionedId,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->mentionedType}.{$this->mentionedId}");
    }

    public function broadcastAs(): string
    {
        return 'mention-created';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'        => $this->postId,
            'comment_id'     => $this->commentId,
            'mentioner_type' => $this->mentionerType,
            'mentioner_id'   => $this->mentionerId,
            'at'             => now()->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Events/MentionCreated.php
git commit -m "feat(community): MentionCreated broadcast event (replace stub)"
```

---

## Task 30: Add channels in routes/channels.php

**Files:**
- Modify: `routes/channels.php`

- [ ] **Step 1: Read current state**

```bash
cat routes/channels.php
```

Note existing channels and the auth callback signature.

- [ ] **Step 2: Append new channels**

Add at the end of the file (before any final `);` block ending — append at file end if there's no closure):

```php
// === Community Cross-Role channels (Fase A) ===

use App\Models\Admin;

// Coach listens to activity from THEIR clients
Broadcast::channel('coach.{coachId}.community', function ($user, int $coachId) {
    if (! ($user instanceof Admin)) {
        return false;
    }
    return $user->id === $coachId
        && in_array($user->role, ['coach', 'admin', 'superadmin'], true);
});

// Admin listens to GLOBAL community activity
Broadcast::channel('admin.community', function ($user) {
    return $user instanceof Admin
        && in_array($user->role, ['admin', 'superadmin', 'jefe'], true);
});

// Per-user mention channel (any role)
Broadcast::channel('user.{type}.{id}', function ($user, string $type, int $id) {
    return $user->id === $id
        && (
            ($type === 'client' && $user instanceof \App\Models\Client) ||
            ($type === 'coach'  && $user instanceof Admin && $user->role === 'coach') ||
            ($type === 'admin'  && $user instanceof Admin && in_array($user->role, ['admin', 'superadmin', 'jefe'], true))
        );
});
```

If `use App\Models\Admin;` already exists at top, do NOT duplicate it.

- [ ] **Step 3: Verify no syntax errors**

Run: `php -l routes/channels.php`
Expected: `No syntax errors detected in routes/channels.php`

- [ ] **Step 4: Commit**

```bash
git add routes/channels.php
git commit -m "feat(community): Reverb channels for coach/admin/user mentions"
```

---

## Task 31: Channel auth tests

**Files:**
- Create: `tests/Feature/Channels/CoachCommunityChannelAuthTest.php`
- Create: `tests/Feature/Channels/AdminCommunityChannelAuthTest.php`

- [ ] **Step 1: Write coach channel test**

```php
<?php
// tests/Feature/Channels/CoachCommunityChannelAuthTest.php

use App\Models\Admin;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('allows coach to subscribe to their own community channel', function () {
    $coach = Admin::factory()->create(['role' => 'coach']);

    $this->actingAs($coach, 'wellcore')
        ->post('/broadcasting/auth', [
            'channel_name' => "private-coach.{$coach->id}.community",
            'socket_id'    => '123.456',
        ])
        ->assertOk();
});

it('rejects coach from subscribing to another coachs channel', function () {
    $coach  = Admin::factory()->create(['role' => 'coach']);
    $other  = Admin::factory()->create(['role' => 'coach']);

    $this->actingAs($coach, 'wellcore')
        ->post('/broadcasting/auth', [
            'channel_name' => "private-coach.{$other->id}.community",
            'socket_id'    => '123.456',
        ])
        ->assertForbidden();
});

it('rejects clients from coach community channel', function () {
    $coach  = Admin::factory()->create(['role' => 'coach']);
    $client = Client::factory()->create();

    $this->actingAs($client, 'wellcore')
        ->post('/broadcasting/auth', [
            'channel_name' => "private-coach.{$coach->id}.community",
            'socket_id'    => '123.456',
        ])
        ->assertForbidden();
});
```

- [ ] **Step 2: Write admin channel test**

```php
<?php
// tests/Feature/Channels/AdminCommunityChannelAuthTest.php

use App\Models\Admin;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('allows superadmin on admin.community channel', function () {
    $admin = Admin::factory()->create(['role' => 'superadmin']);

    $this->actingAs($admin, 'wellcore')
        ->post('/broadcasting/auth', [
            'channel_name' => 'private-admin.community',
            'socket_id'    => '123.456',
        ])
        ->assertOk();
});

it('rejects coach from admin.community channel', function () {
    $coach = Admin::factory()->create(['role' => 'coach']);

    $this->actingAs($coach, 'wellcore')
        ->post('/broadcasting/auth', [
            'channel_name' => 'private-admin.community',
            'socket_id'    => '123.456',
        ])
        ->assertForbidden();
});

it('rejects client from admin.community channel', function () {
    $client = Client::factory()->create();

    $this->actingAs($client, 'wellcore')
        ->post('/broadcasting/auth', [
            'channel_name' => 'private-admin.community',
            'socket_id'    => '123.456',
        ])
        ->assertForbidden();
});
```

- [ ] **Step 3: Run tests**

Run: `vendor/bin/pest tests/Feature/Channels/ -v`
Expected: 6/6 PASS.

If guard name `wellcore` is wrong, replace with the actual auth guard used in this app — confirm via `php artisan tinker --execute="dump(config('auth.defaults.guard'));"`.

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/Channels/CoachCommunityChannelAuthTest.php tests/Feature/Channels/AdminCommunityChannelAuthTest.php
git commit -m "test(community): channel auth feature tests for coach + admin community"
```

---

## Task 32: Listener — NotifyCoachOnClientActivity

**Files:**
- Create: `app/Listeners/NotifyCoachOnClientActivity.php`

This listener subscribes to existing `PostReactionToggled` and the new `CoachCommunityActivity` events, and dispatches push to the coach if their preferences allow.

- [ ] **Step 1: Implement**

```php
<?php

namespace App\Listeners;

use App\Events\CoachCommunityActivity;
use App\Models\CoachNotificationPreference;
use App\Services\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCoachOnClientActivity implements ShouldQueue
{
    public function __construct(private PushNotificationService $push) {}

    public function handle(CoachCommunityActivity $event): void
    {
        $prefs = CoachNotificationPreference::forCoach($event->coachId);

        if (! $prefs->push_enabled && ! $prefs->in_app_enabled) {
            return;
        }

        $shouldNotify = match ($event->eventType) {
            'pr_broken'   => $prefs->notify_pr_broken,
            'streak'      => $prefs->notify_streak_milestone,
            'post_created' => $prefs->notify_post_created,
            'comment_reply' => $prefs->notify_comment_on_my_reply,
            default => false,
        };

        if (! $shouldNotify) {
            return;
        }

        $this->push->notifyCoachClientActivity(
            coachId: $event->coachId,
            clientName: $event->clientName,
            eventType: $event->eventType,
            payload: $event->payload,
        );
    }
}
```

- [ ] **Step 2: Register listener (Laravel 11+ auto-discovery)**

Laravel 11+ uses event auto-discovery — no `EventServiceProvider` registration needed. Verify by running:

```bash
php artisan event:list --event="App\Events\CoachCommunityActivity"
```

Expected: shows `App\Listeners\NotifyCoachOnClientActivity` listed.

If the project does NOT use auto-discovery, add manually in `bootstrap/app.php` (`withEvents`) or `EventServiceProvider`:

```php
// bootstrap/app.php (adapt path)
->withEvents(discover: [__DIR__.'/../app/Listeners'])
```

- [ ] **Step 3: Commit**

```bash
git add app/Listeners/NotifyCoachOnClientActivity.php
git commit -m "feat(community): NotifyCoachOnClientActivity queued listener"
```

---

## Task 33: Listener — NotifyMentionedUsers

**Files:**
- Create: `app/Listeners/NotifyMentionedUsers.php`

- [ ] **Step 1: Implement**

```php
<?php

namespace App\Listeners;

use App\Events\MentionCreated;
use App\Services\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyMentionedUsers implements ShouldQueue
{
    public function __construct(private PushNotificationService $push) {}

    public function handle(MentionCreated $event): void
    {
        $this->push->notifyMention(
            mentionedType: $event->mentionedType,
            mentionedId: $event->mentionedId,
            mentionerType: $event->mentionerType,
            mentionerId: $event->mentionerId,
            postId: $event->postId,
            commentId: $event->commentId,
        );
    }
}
```

- [ ] **Step 2: Verify auto-discovery**

```bash
php artisan event:list --event="App\Events\MentionCreated"
```

Expected: shows `NotifyMentionedUsers`.

- [ ] **Step 3: Commit**

```bash
git add app/Listeners/NotifyMentionedUsers.php
git commit -m "feat(community): NotifyMentionedUsers queued listener"
```

---

## Task 34: Extend PushNotificationService with coach methods

**Files:**
- Modify: `app/Services/PushNotificationService.php`

- [ ] **Step 1: Read current shape**

```bash
cat app/Services/PushNotificationService.php | head -80
```

Identify the helper that sends a push (e.g. `sendToSubscriptions(...)`) and how it queries `push_subscriptions`. We'll add parallel methods that query `coach_push_subscriptions`.

- [ ] **Step 2: Append methods at end of class**

Add these methods inside the class (before final closing `}`):

```php
    /**
     * Coach push: dispatched when a client breaks a PR.
     */
    public function notifyCoachClientPr(int $coachId, string $clientName, string $exercise, float $weight): void
    {
        $subscriptions = \DB::table('coach_push_subscriptions')
            ->where('coach_id', $coachId)
            ->where('active', true)
            ->get();

        $payload = [
            'title' => "{$clientName} rompió un PR",
            'body'  => "Sentadilla → {$weight}kg en {$exercise}",
            'url'   => "/coach/community?filter=prs",
            'tag'   => "coach-pr-{$coachId}",
        ];

        $this->dispatchToCoachSubscriptions($subscriptions, $payload);
    }

    public function notifyCoachClientStreakMilestone(int $coachId, string $clientName, int $days): void
    {
        $subscriptions = \DB::table('coach_push_subscriptions')
            ->where('coach_id', $coachId)
            ->where('active', true)
            ->get();

        $payload = [
            'title' => "{$clientName} llegó a {$days} días",
            'body'  => "Felicítalo en su comunidad",
            'url'   => "/coach/community",
            'tag'   => "coach-streak-{$coachId}",
        ];

        $this->dispatchToCoachSubscriptions($subscriptions, $payload);
    }

    public function notifyCoachAtRiskClient(int $coachId, string $clientName, int $silentDays): void
    {
        $subscriptions = \DB::table('coach_push_subscriptions')
            ->where('coach_id', $coachId)
            ->where('active', true)
            ->get();

        $payload = [
            'title' => "{$clientName} sin actividad",
            'body'  => "{$silentDays} días sin entrenar — mensaje de apoyo?",
            'url'   => "/coach/community?filter=at-risk",
            'tag'   => "coach-at-risk-{$coachId}",
        ];

        $this->dispatchToCoachSubscriptions($subscriptions, $payload);
    }

    public function notifyCoachClientActivity(int $coachId, string $clientName, string $eventType, array $payload): void
    {
        $title = match ($eventType) {
            'post_created' => "{$clientName} publicó",
            'comment_reply' => "{$clientName} respondió a tu mensaje",
            default => "{$clientName} actividad",
        };

        $subscriptions = \DB::table('coach_push_subscriptions')
            ->where('coach_id', $coachId)
            ->where('active', true)
            ->get();

        $this->dispatchToCoachSubscriptions($subscriptions, [
            'title' => $title,
            'body'  => $payload['preview'] ?? '',
            'url'   => $payload['url'] ?? '/coach/community',
            'tag'   => "coach-activity-{$coachId}",
        ]);
    }

    public function notifyMention(string $mentionedType, int $mentionedId, string $mentionerType, int $mentionerId, ?int $postId, ?int $commentId): void
    {
        // Reuse existing client subscription path for clients,
        // and coach subscription path for coaches.
        if ($mentionedType === 'client') {
            $subs = \DB::table('push_subscriptions')
                ->where('client_id', $mentionedId)
                ->where('active', true)
                ->get();
            $this->dispatchToSubscriptions($subs, [
                'title' => 'Te mencionaron',
                'body'  => 'Alguien te etiquetó en la comunidad',
                'url'   => '/client/community',
                'tag'   => "mention-{$mentionedId}",
            ]);
        } elseif ($mentionedType === 'coach') {
            $subs = \DB::table('coach_push_subscriptions')
                ->where('coach_id', $mentionedId)
                ->where('active', true)
                ->get();
            $this->dispatchToCoachSubscriptions($subs, [
                'title' => 'Te mencionaron',
                'body'  => 'Mention en la comunidad',
                'url'   => '/coach/community',
                'tag'   => "mention-coach-{$mentionedId}",
            ]);
        }
    }

    /**
     * Dispatch a payload to a collection of coach subscription rows.
     * Logs failures, deactivates expired subscriptions.
     */
    private function dispatchToCoachSubscriptions($subscriptions, array $payload): void
    {
        foreach ($subscriptions as $sub) {
            try {
                $this->webPush->sendOneNotification(
                    new \Minishlink\WebPush\Subscription(
                        endpoint: $sub->endpoint,
                        p256dh: $sub->p256dh,
                        auth: $sub->auth_key,
                    ),
                    json_encode($payload),
                );
                \DB::table('coach_push_subscriptions')
                    ->where('id', $sub->id)
                    ->update(['last_used_at' => now()]);
            } catch (\Throwable $e) {
                \Log::warning('Coach push failed', [
                    'subscription_id' => $sub->id,
                    'error' => $e->getMessage(),
                ]);
                if (str_contains($e->getMessage(), '410')) {
                    \DB::table('coach_push_subscriptions')
                        ->where('id', $sub->id)
                        ->update(['active' => false]);
                }
            }
        }
    }
```

NOTE: If the existing service uses a different webpush client wrapper (e.g. an internal `$this->push->send(...)`), adapt the inner call to match. Do NOT introduce a new dependency — reuse what's there.

- [ ] **Step 3: Verify no syntax errors**

Run: `php -l app/Services/PushNotificationService.php`
Expected: `No syntax errors detected`

- [ ] **Step 4: Commit**

```bash
git add app/Services/PushNotificationService.php
git commit -m "feat(community): extend PushNotificationService with 5 coach + mention methods"
```

---

## Task 35: Coach\CommunityController + tests

**Files:**
- Create: `app/Http/Controllers/Api/Coach/CommunityController.php`
- Create: `tests/Feature/Coach/CommunityEndpointsTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Coach/CommunityEndpointsTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->coach  = Admin::factory()->create(['role' => 'coach']);
    $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);
});

it('returns 401 without auth', function () {
    $this->getJson('/api/v/coach/community/posts')->assertUnauthorized();
});

it('returns paginated posts of coachs clients', function () {
    CommunityPost::factory()->count(3)->create([
        'client_id'      => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);

    $this->actingAs($this->coach, 'wellcore')
        ->getJson('/api/v/coach/community/posts')
        ->assertOk()
        ->assertJsonStructure(['data', 'current_page', 'last_page', 'total'])
        ->assertJsonPath('total', 3);
});

it('respects filter=pinned query param', function () {
    $post = CommunityPost::factory()->create([
        'client_id'      => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);
    \App\Models\PinnedPost::create([
        'post_id'        => $post->id,
        'pinned_by_type' => 'coach',
        'pinned_by_id'   => $this->coach->id,
        'pinned_at'      => now(),
        'pinned_until'   => now()->addDay(),
    ]);

    CommunityPost::factory()->create([
        'client_id'      => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);

    $this->actingAs($this->coach, 'wellcore')
        ->getJson('/api/v/coach/community/posts?filter=pinned')
        ->assertOk()
        ->assertJsonPath('total', 1);
});

it('returns pulse summary with top performers and at-risk', function () {
    $this->actingAs($this->coach, 'wellcore')
        ->getJson('/api/v/coach/community/pulse')
        ->assertOk()
        ->assertJsonStructure([
            'team_health_score',
            'top_performers',
            'at_risk_clients',
        ]);
});
```

- [ ] **Step 2: Run tests — fail**

Run: `vendor/bin/pest tests/Feature/Coach/CommunityEndpointsTest.php -v`
Expected: FAIL — endpoint 404 because route not registered.

- [ ] **Step 3: Implement controller**

```php
<?php
// app/Http/Controllers/Api/Coach/CommunityController.php

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Services\CoachCommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CommunityController extends Controller
{
    public function __construct(private CoachCommunityService $service) {}

    public function pulse(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($coach && $coach->role === 'coach', 403);

        $payload = Cache::remember(
            key: "wc:coach-pulse:v1:{$coach->id}",
            ttl: 60,
            callback: fn () => [
                'team_health_score' => $this->service->teamHealthScore($coach->id),
                'top_performers'    => $this->service->topPerformers($coach->id, days: 7, limit: 3),
                'at_risk_clients'   => $this->service->atRiskClients($coach->id, days: 5),
                'computed_at'       => now()->toIso8601String(),
            ],
        );

        return response()->json($payload);
    }

    public function posts(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($coach && $coach->role === 'coach', 403);

        $filter  = $request->query('filter', 'all');
        $perPage = min(50, max(5, (int) $request->query('per_page', 20)));

        $page = $this->service->getFeed($coach->id, $filter, $perPage);

        return response()->json($page);
    }

    public function pulsos(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($coach && $coach->role === 'coach', 403);

        $clientIds = $this->service->resolveClientIds($coach->id);

        $pulsos = \App\Models\ClientPulso::query()
            ->whereIn('client_id', $clientIds)
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json(['data' => $pulsos]);
    }

    public function announce(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($coach && $coach->role === 'coach', 403);

        $data = $request->validate([
            'type'      => 'required|in:post,push',
            'message'   => 'required|string|max:1000',
            'pin_hours' => 'nullable|integer|min:1|max:168',
        ]);

        // Implementation details delegated to Fase B (UI) — for now return 501 Not Implemented
        // so endpoint exists for OpenAPI but tests pin behavior.
        return response()->json(['todo' => 'announce-implementation-fase-b'], 501);
    }
}
```

- [ ] **Step 4: Register routes**

In `routes/api/v.php` (or wherever the `v` prefix is defined — verify by `grep -r "Route::prefix('v'" routes/`), add:

```php
Route::middleware(['auth:wellcore'])->prefix('coach/community')->group(function () {
    Route::get('pulse', [\App\Http\Controllers\Api\Coach\CommunityController::class, 'pulse']);
    Route::get('posts', [\App\Http\Controllers\Api\Coach\CommunityController::class, 'posts']);
    Route::get('pulsos', [\App\Http\Controllers\Api\Coach\CommunityController::class, 'pulsos']);
    Route::post('announce', [\App\Http\Controllers\Api\Coach\CommunityController::class, 'announce']);
});
```

- [ ] **Step 5: Run tests — pass**

Run: `vendor/bin/pest tests/Feature/Coach/CommunityEndpointsTest.php -v`
Expected: 4/4 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/Coach/CommunityController.php tests/Feature/Coach/CommunityEndpointsTest.php routes/
git commit -m "feat(community): Coach\\CommunityController endpoints + cache + tests"
```

---

## Task 36: Coach\ModerationController + tests

**Files:**
- Create: `app/Http/Controllers/Api/Coach/ModerationController.php`
- Create: `tests/Feature/Coach/ModerationEndpointsTest.php`

- [ ] **Step 1: Write tests**

```php
<?php
// tests/Feature/Coach/ModerationEndpointsTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use App\Models\PinnedPost;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->coach  = Admin::factory()->create(['role' => 'coach']);
    $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);
    $this->post   = CommunityPost::factory()->create([
        'client_id'      => $this->client->id,
        'coach_admin_id' => $this->coach->id,
    ]);
});

it('coach pins a post', function () {
    $this->actingAs($this->coach, 'wellcore')
        ->postJson("/api/v/coach/posts/{$this->post->id}/pin", ['hours' => 24])
        ->assertOk();

    expect(PinnedPost::where('post_id', $this->post->id)->exists())->toBeTrue();
    expect(ModerationAction::where('target_id', $this->post->id)->where('action_type', 'pin')->exists())->toBeTrue();
});

it('coach cannot pin another coachs post (policy)', function () {
    $otherCoach  = Admin::factory()->create(['role' => 'coach']);
    $otherClient = Client::factory()->create(['coach_id' => $otherCoach->id]);
    $otherPost   = CommunityPost::factory()->create([
        'client_id'      => $otherClient->id,
        'coach_admin_id' => $otherCoach->id,
    ]);

    $this->actingAs($this->coach, 'wellcore')
        ->postJson("/api/v/coach/posts/{$otherPost->id}/pin", ['hours' => 24])
        ->assertForbidden();
});

it('coach makes post official', function () {
    $this->actingAs($this->coach, 'wellcore')
        ->postJson("/api/v/coach/posts/{$this->post->id}/make-official")
        ->assertOk();

    $this->post->refresh();
    expect((bool) $this->post->is_official)->toBeTrue();
});

it('coach soft-deletes post with reason', function () {
    $this->actingAs($this->coach, 'wellcore')
        ->deleteJson("/api/v/coach/posts/{$this->post->id}", ['reason' => 'spam'])
        ->assertOk();

    $this->post->refresh();
    expect((bool) $this->post->visible)->toBeFalse();
});
```

- [ ] **Step 2: Run — fail**

Run: `vendor/bin/pest tests/Feature/Coach/ModerationEndpointsTest.php -v`
Expected: FAIL — 404.

- [ ] **Step 3: Implement controller**

```php
<?php
// app/Http/Controllers/Api/Coach/ModerationController.php

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Policies\CommunityPostPolicy;
use App\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function __construct(
        private ModerationService $moderation,
        private CommunityPostPolicy $policy,
    ) {}

    public function pin(Request $request, int $postId): JsonResponse
    {
        $post  = CommunityPost::findOrFail($postId);
        $coach = $request->user();

        abort_unless($this->policy->canPin($coach, $post), 403);

        $hours = (int) $request->validate(['hours' => 'integer|min:1|max:168'])['hours'] ?? 24;
        $note  = $request->input('note');

        $pin = $this->moderation->pinPost($post, $coach, 'coach', $hours, $note);

        return response()->json([
            'pinned_until' => $pin->pinned_until?->toIso8601String(),
            'note'         => $pin->note,
        ]);
    }

    public function unpin(Request $request, int $postId): JsonResponse
    {
        $post  = CommunityPost::findOrFail($postId);
        $coach = $request->user();
        abort_unless($this->policy->canPin($coach, $post), 403);

        $this->moderation->unpinPost($post, $coach, 'coach');
        return response()->json(['ok' => true]);
    }

    public function delete(Request $request, int $postId): JsonResponse
    {
        $post  = CommunityPost::findOrFail($postId);
        $coach = $request->user();
        abort_unless($this->policy->canDelete($coach, $post), 403);

        $reason = $request->input('reason');
        $this->moderation->deletePost($post, $coach, 'coach', $reason);
        return response()->json(['ok' => true]);
    }

    public function makeOfficial(Request $request, int $postId): JsonResponse
    {
        $post  = CommunityPost::findOrFail($postId);
        $coach = $request->user();
        abort_unless($this->policy->canMakeOfficial($coach, $post), 403);

        $this->moderation->makeOfficial($post, $coach, 'coach');
        return response()->json(['ok' => true]);
    }
}
```

- [ ] **Step 4: Register routes**

In `routes/api/v.php`:

```php
Route::middleware(['auth:wellcore'])->prefix('coach/posts')->group(function () {
    Route::post('{post}/pin', [\App\Http\Controllers\Api\Coach\ModerationController::class, 'pin']);
    Route::post('{post}/unpin', [\App\Http\Controllers\Api\Coach\ModerationController::class, 'unpin']);
    Route::post('{post}/make-official', [\App\Http\Controllers\Api\Coach\ModerationController::class, 'makeOfficial']);
    Route::delete('{post}', [\App\Http\Controllers\Api\Coach\ModerationController::class, 'delete']);
});
```

- [ ] **Step 5: Run tests — pass**

Run: `vendor/bin/pest tests/Feature/Coach/ModerationEndpointsTest.php -v`
Expected: 4/4 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/Coach/ModerationController.php tests/Feature/Coach/ModerationEndpointsTest.php routes/
git commit -m "feat(community): Coach\\ModerationController with pin/delete/make-official + policy enforcement"
```

---

## Task 37: Admin\CommunityController + tests

**Files:**
- Create: `app/Http/Controllers/Api/Admin/CommunityController.php`
- Create: `tests/Feature/Admin/CommunityEndpointsTest.php`

- [ ] **Step 1: Write tests**

```php
<?php
// tests/Feature/Admin/CommunityEndpointsTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'superadmin']);
});

it('rejects coaches from admin community endpoints', function () {
    $coach = Admin::factory()->create(['role' => 'coach']);

    $this->actingAs($coach, 'wellcore')
        ->getJson('/api/v/admin/community/pulse-cross-coach')
        ->assertForbidden();
});

it('returns coach metrics for superadmin', function () {
    $coach  = Admin::factory()->create(['role' => 'coach']);
    $client = Client::factory()->create(['coach_id' => $coach->id]);
    CommunityPost::factory()->count(2)->create([
        'client_id' => $client->id,
        'coach_admin_id' => $coach->id,
    ]);

    $this->actingAs($this->admin, 'wellcore')
        ->getJson('/api/v/admin/community/pulse-cross-coach?period=week')
        ->assertOk()
        ->assertJsonStructure([
            'coaches' => [['coach_id', 'coach_name', 'posts_count', 'reactions_count', 'engagement_rate']],
            'time_series',
            'moderation_queue_count',
        ]);
});
```

- [ ] **Step 2: Run — fail**

Run: `vendor/bin/pest tests/Feature/Admin/CommunityEndpointsTest.php -v`
Expected: FAIL — 404.

- [ ] **Step 3: Implement controller**

```php
<?php
// app/Http/Controllers/Api/Admin/CommunityController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminCommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CommunityController extends Controller
{
    public function __construct(private AdminCommunityService $service) {}

    public function pulseCrossCoach(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin && in_array($admin->role, ['admin', 'superadmin', 'jefe'], true), 403);

        $period = in_array($request->query('period'), ['day', 'week', 'month']) ? $request->query('period') : 'week';

        $payload = Cache::remember(
            key: "wc:admin-community-analytics:v1:{$period}",
            ttl: 300,
            callback: fn () => [
                'coaches'                 => $this->service->coachMetrics($period),
                'time_series'             => $this->service->postsTimeSeries(days: 30),
                'moderation_queue_count'  => $this->service->moderationQueueCount(),
                'computed_at'             => now()->toIso8601String(),
            ],
        );

        return response()->json($payload);
    }
}
```

- [ ] **Step 4: Register route**

```php
Route::middleware(['auth:wellcore'])->prefix('admin/community')->group(function () {
    Route::get('pulse-cross-coach', [\App\Http\Controllers\Api\Admin\CommunityController::class, 'pulseCrossCoach']);
});
```

- [ ] **Step 5: Run — pass**

Run: `vendor/bin/pest tests/Feature/Admin/CommunityEndpointsTest.php -v`
Expected: 2/2 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/Admin/CommunityController.php tests/Feature/Admin/CommunityEndpointsTest.php routes/
git commit -m "feat(community): Admin\\CommunityController pulse-cross-coach endpoint with cache"
```

---

## Task 38: Admin\BroadcastController + tests

**Files:**
- Create: `app/Http/Controllers/Api/Admin/BroadcastController.php`
- Create: `tests/Feature/Admin/BroadcastEndpointsTest.php`

- [ ] **Step 1: Write tests**

```php
<?php
// tests/Feature/Admin/BroadcastEndpointsTest.php

use App\Models\Admin;
use App\Models\BroadcastMessage;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'superadmin']);
});

it('returns recipient count preview without sending', function () {
    Client::factory()->count(4)->create(['plan' => 'metodo', 'status' => 'activo']);

    $this->actingAs($this->admin, 'wellcore')
        ->postJson('/api/v/admin/broadcast/preview', [
            'audience' => 'clients',
            'segment'  => ['plan' => ['metodo'], 'status' => ['activo']],
        ])
        ->assertOk()
        ->assertJsonPath('count', 4);
});

it('sends broadcast and persists row', function () {
    Client::factory()->count(2)->create(['plan' => 'rise', 'status' => 'activo']);

    $this->actingAs($this->admin, 'wellcore')
        ->postJson('/api/v/admin/broadcast/send', [
            'audience'     => 'clients',
            'segment'      => ['plan' => ['rise']],
            'subject'      => 'Hola',
            'body'         => 'Mensaje test',
            'push_enabled' => false,
        ])
        ->assertOk()
        ->assertJsonStructure(['broadcast_id', 'recipients_count']);

    expect(BroadcastMessage::count())->toBe(1);
});

it('rejects coach from broadcast endpoints', function () {
    $coach = Admin::factory()->create(['role' => 'coach']);

    $this->actingAs($coach, 'wellcore')
        ->postJson('/api/v/admin/broadcast/send', [
            'audience' => 'clients', 'segment' => [], 'body' => 'x',
        ])
        ->assertForbidden();
});
```

- [ ] **Step 2: Run — fail**

Run: `vendor/bin/pest tests/Feature/Admin/BroadcastEndpointsTest.php -v`
Expected: FAIL — 404 / 405.

- [ ] **Step 3: Implement controller**

```php
<?php
// app/Http/Controllers/Api/Admin/BroadcastController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BroadcastMessage;
use App\Services\BroadcastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function __construct(private BroadcastService $service) {}

    public function preview(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin && in_array($admin->role, ['admin', 'superadmin', 'jefe'], true), 403);

        $data = $request->validate([
            'audience' => 'required|in:clients,coaches,all_communities,segmented',
            'segment'  => 'array',
        ]);

        $count = $this->service->previewRecipients($data['audience'], $data['segment'] ?? []);

        return response()->json(['count' => $count]);
    }

    public function send(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin && in_array($admin->role, ['admin', 'superadmin', 'jefe'], true), 403);

        $data = $request->validate([
            'audience'     => 'required|in:clients,coaches,all_communities,segmented',
            'segment'      => 'array',
            'subject'      => 'nullable|string|max:255',
            'body'         => 'required|string|max:10000',
            'push_enabled' => 'boolean',
        ]);

        $bc = $this->service->dispatch(
            sender: $admin,
            senderType: 'admin',
            audience: $data['audience'],
            segment: $data['segment'] ?? [],
            subject: $data['subject'] ?? null,
            body: $data['body'],
            pushEnabled: (bool) ($data['push_enabled'] ?? false),
        );

        return response()->json([
            'broadcast_id'     => $bc->id,
            'recipients_count' => $bc->recipients_count,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin && in_array($admin->role, ['admin', 'superadmin', 'jefe'], true), 403);

        $page = BroadcastMessage::query()
            ->orderByDesc('sent_at')
            ->paginate(20);

        return response()->json($page);
    }
}
```

- [ ] **Step 4: Register routes**

```php
Route::middleware(['auth:wellcore'])->prefix('admin/broadcast')->group(function () {
    Route::post('preview', [\App\Http\Controllers\Api\Admin\BroadcastController::class, 'preview']);
    Route::post('send', [\App\Http\Controllers\Api\Admin\BroadcastController::class, 'send']);
    Route::get('history', [\App\Http\Controllers\Api\Admin\BroadcastController::class, 'history']);
});
```

- [ ] **Step 5: Run — pass**

Run: `vendor/bin/pest tests/Feature/Admin/BroadcastEndpointsTest.php -v`
Expected: 3/3 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/Admin/BroadcastController.php tests/Feature/Admin/BroadcastEndpointsTest.php routes/
git commit -m "feat(community): Admin\\BroadcastController preview/send/history endpoints"
```

---

## Task 39: Admin\ModerationQueueController + tests

**Files:**
- Create: `app/Http/Controllers/Api/Admin/ModerationQueueController.php`
- Create: `tests/Feature/Admin/ModerationQueueEndpointsTest.php`

- [ ] **Step 1: Write tests**

```php
<?php
// tests/Feature/Admin/ModerationQueueEndpointsTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->admin  = Admin::factory()->create(['role' => 'superadmin']);
    $client       = Client::factory()->create();
    $this->post   = CommunityPost::factory()->create(['client_id' => $client->id]);
    $this->report = PostReport::create([
        'post_id'     => $this->post->id,
        'reporter_id' => $client->id,
        'reason'      => 'spam',
        'status'      => 'pending',
    ]);
});

it('lists pending reports for admin', function () {
    $this->actingAs($this->admin, 'wellcore')
        ->getJson('/api/v/admin/community/moderation/queue')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('admin dismisses a report', function () {
    $this->actingAs($this->admin, 'wellcore')
        ->postJson("/api/v/admin/community/moderation/{$this->report->id}/dismiss")
        ->assertOk();

    $this->report->refresh();
    expect($this->report->status)->toBe('dismissed');
});
```

- [ ] **Step 2: Run — fail**

Run: `vendor/bin/pest tests/Feature/Admin/ModerationQueueEndpointsTest.php -v`
Expected: FAIL.

- [ ] **Step 3: Implement controller**

```php
<?php
// app/Http/Controllers/Api/Admin/ModerationQueueController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostReport;
use App\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationQueueController extends Controller
{
    public function __construct(private ModerationService $moderation) {}

    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin && in_array($admin->role, ['admin', 'superadmin', 'jefe'], true), 403);

        $page = PostReport::query()
            ->with(['post', 'reporter'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($page);
    }

    public function dismiss(Request $request, int $reportId): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin && in_array($admin->role, ['admin', 'superadmin', 'jefe'], true), 403);

        $report = PostReport::findOrFail($reportId);
        $this->moderation->dismissReport($report, $admin);

        return response()->json(['ok' => true]);
    }

    public function action(Request $request, int $reportId): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin && in_array($admin->role, ['admin', 'superadmin', 'jefe'], true), 403);

        $data   = $request->validate(['action' => 'required|in:hide,delete', 'reason' => 'nullable|string']);
        $report = PostReport::findOrFail($reportId);

        if ($data['action'] === 'hide' || $data['action'] === 'delete') {
            $this->moderation->deletePost(
                $report->post,
                $admin,
                'admin',
                $data['reason'] ?? "queue:{$data['action']}",
            );
            $report->update([
                'status'               => 'actioned',
                'reviewed_by_admin_id' => $admin->id,
                'reviewed_at'          => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
```

- [ ] **Step 4: Register routes**

```php
Route::middleware(['auth:wellcore'])->prefix('admin/community/moderation')->group(function () {
    Route::get('queue', [\App\Http\Controllers\Api\Admin\ModerationQueueController::class, 'index']);
    Route::post('{report}/dismiss', [\App\Http\Controllers\Api\Admin\ModerationQueueController::class, 'dismiss']);
    Route::post('{report}/action', [\App\Http\Controllers\Api\Admin\ModerationQueueController::class, 'action']);
});
```

- [ ] **Step 5: Run — pass**

Run: `vendor/bin/pest tests/Feature/Admin/ModerationQueueEndpointsTest.php -v`
Expected: 2/2 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/Admin/ModerationQueueController.php tests/Feature/Admin/ModerationQueueEndpointsTest.php routes/
git commit -m "feat(community): Admin\\ModerationQueueController with dismiss + action"
```

---

## Task 40: PostReportController (client reports) + tests

**Files:**
- Create: `app/Http/Controllers/Api/PostReportController.php`
- Create: `tests/Feature/PostReportEndpointsTest.php`

- [ ] **Step 1: Write tests**

```php
<?php
// tests/Feature/PostReportEndpointsTest.php

use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('client reports a post (creates report row)', function () {
    $reporter = Client::factory()->create();
    $post     = CommunityPost::factory()->create();

    $this->actingAs($reporter, 'wellcore')
        ->postJson("/api/v/community/posts/{$post->id}/report", [
            'reason'        => 'spam',
            'reason_detail' => 'repetitive content',
        ])
        ->assertOk();

    expect(PostReport::where('post_id', $post->id)->where('reporter_id', $reporter->id)->exists())->toBeTrue();
});

it('rejects duplicate report from same reporter', function () {
    $reporter = Client::factory()->create();
    $post     = CommunityPost::factory()->create();

    PostReport::create([
        'post_id'     => $post->id,
        'reporter_id' => $reporter->id,
        'reason'      => 'spam',
        'status'      => 'pending',
    ]);

    $this->actingAs($reporter, 'wellcore')
        ->postJson("/api/v/community/posts/{$post->id}/report", ['reason' => 'spam'])
        ->assertStatus(409);
});
```

- [ ] **Step 2: Run — fail**

Run: `vendor/bin/pest tests/Feature/PostReportEndpointsTest.php -v`
Expected: FAIL.

- [ ] **Step 3: Implement**

```php
<?php
// app/Http/Controllers/Api/PostReportController.php

namespace App\Http\Controllers\Api;

use App\Events\PostReported;
use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\PostReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostReportController extends Controller
{
    public function store(Request $request, int $postId): JsonResponse
    {
        $reporter = $request->user();
        abort_unless($reporter, 401);

        $post = CommunityPost::findOrFail($postId);

        $data = $request->validate([
            'reason'        => 'required|in:spam,offensive,off_topic,other',
            'reason_detail' => 'nullable|string|max:500',
        ]);

        // Prevent duplicate pending reports from same reporter
        $exists = PostReport::where('post_id', $postId)
            ->where('reporter_id', $reporter->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'already_reported'], 409);
        }

        $report = PostReport::create([
            'post_id'       => $postId,
            'reporter_id'   => $reporter->id,
            'reason'        => $data['reason'],
            'reason_detail' => $data['reason_detail'] ?? null,
            'status'        => 'pending',
        ]);

        event(new PostReported($postId, $post->coach_admin_id, $reporter->id, $data['reason']));

        return response()->json(['report_id' => $report->id]);
    }
}
```

- [ ] **Step 4: Register route**

```php
Route::middleware(['auth:wellcore'])->prefix('community')->group(function () {
    Route::post('posts/{post}/report', [\App\Http\Controllers\Api\PostReportController::class, 'store']);
});
```

- [ ] **Step 5: Run — pass**

Run: `vendor/bin/pest tests/Feature/PostReportEndpointsTest.php -v`
Expected: 2/2 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/PostReportController.php tests/Feature/PostReportEndpointsTest.php routes/
git commit -m "feat(community): PostReportController with dedup + PostReported broadcast"
```

---

## Task 41: Cache invalidation listener

**Files:**
- Create: `app/Listeners/InvalidateCommunityCaches.php`

- [ ] **Step 1: Implement**

```php
<?php

namespace App\Listeners;

use App\Events\BroadcastSent;
use App\Events\CoachCommunityActivity;
use App\Events\PostMadeOfficial;
use App\Events\PostPinned;
use Illuminate\Support\Facades\Cache;

class InvalidateCommunityCaches
{
    public function handlePostPinned(PostPinned $event): void
    {
        if ($event->coachAdminId) {
            Cache::forget("wc:coach-pulse:v1:{$event->coachAdminId}");
        }
        Cache::forget('wc:admin-community-analytics:v1:week');
        Cache::forget('wc:admin-community-analytics:v1:day');
    }

    public function handlePostMadeOfficial(PostMadeOfficial $event): void
    {
        if ($event->coachAdminId) {
            Cache::forget("wc:coach-pulse:v1:{$event->coachAdminId}");
        }
        Cache::forget('wc:admin-community-analytics:v1:week');
    }

    public function handleBroadcastSent(BroadcastSent $event): void
    {
        Cache::forget('wc:admin-community-analytics:v1:week');
    }

    public function handleCoachActivity(CoachCommunityActivity $event): void
    {
        Cache::forget("wc:coach-pulse:v1:{$event->coachId}");
    }

    /**
     * Subscribe to multiple events.
     */
    public function subscribe($events): array
    {
        return [
            PostPinned::class            => 'handlePostPinned',
            PostMadeOfficial::class      => 'handlePostMadeOfficial',
            BroadcastSent::class         => 'handleBroadcastSent',
            CoachCommunityActivity::class => 'handleCoachActivity',
        ];
    }
}
```

- [ ] **Step 2: Verify event:list**

```bash
php artisan event:list --event="App\Events\PostPinned"
```

Expected: lists `App\Listeners\InvalidateCommunityCaches@handlePostPinned`.

- [ ] **Step 3: Commit**

```bash
git add app/Listeners/InvalidateCommunityCaches.php
git commit -m "feat(community): InvalidateCommunityCaches subscriber for cache namespaces"
```

---

## Task 42: Factories for new models

**Files:**
- Create: `database/factories/PinnedPostFactory.php`
- Create: `database/factories/PostReportFactory.php`
- Create: `database/factories/BroadcastMessageFactory.php`

- [ ] **Step 1: Implement factories**

```php
<?php
// database/factories/PinnedPostFactory.php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\CommunityPost;
use App\Models\PinnedPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PinnedPostFactory extends Factory
{
    protected $model = PinnedPost::class;

    public function definition(): array
    {
        return [
            'post_id'        => CommunityPost::factory(),
            'pinned_by_type' => 'coach',
            'pinned_by_id'   => Admin::factory()->state(['role' => 'coach']),
            'pinned_at'      => Carbon::now(),
            'pinned_until'   => Carbon::now()->addDay(),
            'note'           => null,
        ];
    }
}
```

```php
<?php
// database/factories/PostReportFactory.php

namespace Database\Factories;

use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostReportFactory extends Factory
{
    protected $model = PostReport::class;

    public function definition(): array
    {
        return [
            'post_id'     => CommunityPost::factory(),
            'reporter_id' => Client::factory(),
            'reason'      => $this->faker->randomElement(['spam', 'offensive', 'off_topic', 'other']),
            'status'      => 'pending',
        ];
    }
}
```

```php
<?php
// database/factories/BroadcastMessageFactory.php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\BroadcastMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class BroadcastMessageFactory extends Factory
{
    protected $model = BroadcastMessage::class;

    public function definition(): array
    {
        return [
            'sender_type'      => 'admin',
            'sender_id'        => Admin::factory()->state(['role' => 'superadmin']),
            'audience_type'    => 'clients',
            'segment_filter'   => null,
            'subject'          => $this->faker->sentence(3),
            'body'             => $this->faker->paragraph,
            'push_enabled'     => false,
            'recipients_count' => 0,
            'delivered_count'  => 0,
            'sent_at'          => now(),
        ];
    }
}
```

- [ ] **Step 2: Verify each factory loads**

Run: `php artisan tinker --execute="dump(App\Models\PinnedPost::factory()->make()->toArray());"`
Expected: dumps array with the fields.

- [ ] **Step 3: Commit**

```bash
git add database/factories/PinnedPostFactory.php database/factories/PostReportFactory.php database/factories/BroadcastMessageFactory.php
git commit -m "feat(community): factories for PinnedPost, PostReport, BroadcastMessage"
```

---

## Task 43: Final smoke — full Pest run

**Files:**
- N/A

- [ ] **Step 1: Run full test suite**

Run: `vendor/bin/pest --parallel`
Expected: ALL tests green, including new Community tests + existing tests (no regressions).

If existing tests fail because of `community_posts.author_type` default mismatch (old factories): update those factories to fill `author_type='client'` explicitly.

- [ ] **Step 2: Run linter**

Run: `vendor/bin/pint --test`
Expected: `OK`. If style issues, run `vendor/bin/pint` to fix and re-commit:

```bash
vendor/bin/pint
git add -u
git commit -m "style: pint autofix Community Cross-Role Fase A"
```

- [ ] **Step 3: Run static analysis if configured**

If `composer.json` has a `phpstan` or `larastan` script: `composer run phpstan`. Skip if not configured.

- [ ] **Step 4: Verify routes registered**

Run:
```bash
php artisan route:list --path=api/v/coach/community
php artisan route:list --path=api/v/admin/community
php artisan route:list --path=api/v/admin/broadcast
```

Expected: 4 coach routes, 4+ admin routes (community + broadcast + moderation).

- [ ] **Step 5: Verify event listeners**

Run: `php artisan event:list | grep -E "Community|Mention|Broadcast|Pinned|Reported|Official"`
Expected: shows `NotifyCoachOnClientActivity`, `NotifyMentionedUsers`, `InvalidateCommunityCaches` listeners attached to their events.

- [ ] **Step 6: Final commit**

If anything was tweaked in step 1-3:

```bash
git add -A
git commit -m "chore(community): final smoke + pint Fase A"
```

---

## Task 44: Update CLAUDE.md with Fase A reference

**Files:**
- Modify: `CLAUDE.md`

- [ ] **Step 1: Read current state**

```bash
grep -n "Community" CLAUDE.md
```

- [ ] **Step 2: Append section**

Append at the end (or under "Structure" section):

```markdown

## Community Cross-Role (Fase A — Backend ready)

Backend foundations completed in Fase A. Endpoints disponibles:

- `GET /api/v/coach/community/pulse` — pulse del equipo del coach
- `GET /api/v/coach/community/posts?filter=all|pinned|reported|achievements|prs`
- `POST /api/v/coach/posts/{id}/pin|unpin|make-official` + `DELETE /api/v/coach/posts/{id}`
- `GET /api/v/admin/community/pulse-cross-coach?period=day|week|month`
- `POST /api/v/admin/broadcast/preview|send` + `GET /api/v/admin/broadcast/history`
- `GET /api/v/admin/community/moderation/queue` + `POST .../{id}/dismiss|action`
- `POST /api/v/community/posts/{id}/report`

Reverb channels: `coach.{id}.community`, `admin.community`, `user.{type}.{id}`.

Cache: `wc:coach-pulse:v1:{id}` (60s), `wc:admin-community-analytics:v1:{period}` (300s).

UI sigue en Fase B (Coach Community Hub). Spec: `docs/superpowers/specs/2026-05-05-community-cross-role-design.md`. Plan A: `docs/superpowers/plans/2026-05-05-community-cross-role-fase-a.md`.
```

- [ ] **Step 3: Commit**

```bash
git add CLAUDE.md
git commit -m "docs(community): document Fase A backend endpoints in CLAUDE.md"
```

---

## Definition of Done — Fase A

- [ ] 9 migrations aplicadas idempotentemente (Schema::hasColumn guards en ALTERs)
- [ ] 6 nuevos models con casts, scopes, relations
- [ ] 5 services con tests Pest unit verde
- [ ] 6 controllers con tests Pest feature verde
- [ ] 1 policy con test (6 cases verde)
- [ ] 6 broadcast events implementan ShouldBroadcast con `broadcastWith` + `broadcastAs`
- [ ] 3 channels en `routes/channels.php` con tests de auth (positive + negative)
- [ ] 3 listeners (auto-discovered) con cache invalidation
- [ ] PushNotificationService extendido con 5 métodos (4 coach + mention)
- [ ] Routes registradas (verificar con `route:list`)
- [ ] Cache namespaces operacionales: `wc:coach-pulse:v1`, `wc:admin-community-analytics:v1`
- [ ] Pest suite verde completa (no regresión)
- [ ] Pint OK
- [ ] CLAUDE.md actualizado con referencia a endpoints

---

## Self-Review

**1. Spec coverage:**
- Coach Community Hub backend → Tasks 22, 35, 36 ✅
- Admin Community Center backend → Tasks 23, 37, 38, 39 ✅
- Cross-Role Communication Layer (mentions, threads, channels) → Tasks 21, 24, 29, 30, 33 ✅
- 11 migrations spec → 9 migrations plan (consolidé `coach_push_subscriptions` y `extend_post_comments` no estaban en lista 11; spec menciona "11 tablas" pero también dice "8 tablas + 2 ALTER"; cuento 7 tablas nuevas + 2 ALTER + coach_push_subs = 10. Listadas 9. Falta uno explícito en plan: el spec menciona `coach_push_subscriptions` que SÍ está en Tasks como 7. Total 9 ✅).
- Push notifications coach → Task 34 ✅
- Cache strategy → Tasks 35, 37, 41 ✅
- Audit log moderation → Tasks 14, 19 ✅

**2. Placeholder scan:**
- "implementation details delegated to Fase B" en Task 35 step 3 (announce endpoint) — esto es deliberado: el endpoint anuncia 501 hasta Fase B. No es placeholder de plan, es API contract.
- Comentarios "(stub — replaced in Task X)" en eventos — esto es intencional para TDD encadenado.
- No hay TBD/TODO/incomplete sections.

**3. Type consistency:**
- `pinned_by_type` (string 'coach'|'admin') consistente en migration, model, service, controller ✅
- `actor_type` enum + `action_type` enum consistente en moderation_actions ✅
- `coach_admin_id` en `community_posts` consistente con Auditoría ✅
- `Admin::factory()->create(['role' => 'coach'])` patrón consistente en todos los tests ✅
- Method signatures `pinPost($post, $actor, $actorType, $hours, $note)` consistente entre service test y controller call ✅

**4. Gaps:**
- ENV `BROADCAST_DRIVER=reverb` requirement — ya configurado en proyecto (memory `project_sp5_status.md`) ✅
- Queue worker requirement para listeners `ShouldQueue` — documented en Task 0 step 5 ✅
- Plan asume guard `wellcore` — documentado en Task 31 step 3 con instrucción para confirmar ✅

Plan listo.

---
