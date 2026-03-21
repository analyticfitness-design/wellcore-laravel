<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->handleRequest(\Illuminate\Http\Request::capture());

// But we only need the DB, so let's use it directly
use Illuminate\Support\Facades\DB;

$notifications = [
    [
        'user_type' => 'client',
        'user_id'   => 1,
        'type'      => 'coach',
        'title'     => 'Tu coach revisó tu check-in semanal',
        'body'      => 'Tu coach dejó comentarios sobre tu progreso de esta semana.',
        'link'      => '/client/checkin',
        'read_at'   => null,
        'created_at'=> now()->subMinutes(12)->format('Y-m-d H:i:s'),
    ],
    [
        'user_type' => 'client',
        'user_id'   => 1,
        'type'      => 'challenge',
        'title'     => 'Nuevo reto disponible: 30 días sin azúcar',
        'body'      => 'Un nuevo reto acaba de abrirse. ¿Te animas a participar?',
        'link'      => '/client/challenges',
        'read_at'   => null,
        'created_at'=> now()->subHours(2)->format('Y-m-d H:i:s'),
    ],
    [
        'user_type' => 'client',
        'user_id'   => 1,
        'type'      => 'achievement',
        'title'     => '¡Felicidades! Racha de 7 días desbloqueada',
        'body'      => 'Completaste 7 días consecutivos de entrenamiento.',
        'link'      => null,
        'read_at'   => null,
        'created_at'=> now()->subHours(6)->format('Y-m-d H:i:s'),
    ],
    [
        'user_type' => 'client',
        'user_id'   => 1,
        'type'      => 'plan',
        'title'     => 'Tu plan fue actualizado para esta semana',
        'body'      => 'Tu coach ajustó tu plan de entrenamiento. Revisa los cambios.',
        'link'      => '/client/plan',
        'read_at'   => null,
        'created_at'=> now()->subDay()->format('Y-m-d H:i:s'),
    ],
    [
        'user_type' => 'client',
        'user_id'   => 1,
        'type'      => 'reminder',
        'title'     => 'Recordatorio: check-in pendiente',
        'body'      => 'No olvides completar tu check-in semanal antes del domingo.',
        'link'      => '/client/checkin',
        'read_at'   => null,
        'created_at'=> now()->subDays(2)->format('Y-m-d H:i:s'),
    ],
];

DB::table('notifications')->insert($notifications);

echo "Inserted " . count($notifications) . " test notifications for client_id=1\n";
