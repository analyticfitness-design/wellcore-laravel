-- Snapshot pre-cardio-module 2026-05-16 20:54 UTC
-- Schema completo de workout_logs ANTES de F0 (ALTER TABLE).
-- Origen: SHOW CREATE TABLE workout_logs en MySQL producción (container EasyPanel).
-- Tamaño: 1463 bytes.
-- Backup persistente en container: /tmp/backup-cardio-2026-05-16/schema-workout_logs.sql

CREATE TABLE `workout_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_id` bigint unsigned NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `exercise_name` varchar(255) NOT NULL,
  `block_type` varchar(50) DEFAULT 'normal',
  `block_order` int DEFAULT 0,
  `set_number` int unsigned NOT NULL,
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `reps` int unsigned DEFAULT NULL,
  `target_reps` varchar(50) DEFAULT NULL,
  `target_weight` decimal(6,2) DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `is_pr` tinyint(1) NOT NULL DEFAULT '0',
  `is_cardio` tinyint(1) NOT NULL DEFAULT '0',
  `is_isometric` tinyint(1) NOT NULL DEFAULT '0',
  `duration_minutes` smallint unsigned DEFAULT NULL,
  `duration_seconds` int unsigned DEFAULT NULL,
  `speed_kmh` decimal(5,2) DEFAULT NULL,
  `incline_percent` tinyint unsigned DEFAULT NULL,
  `heart_rate_avg` smallint unsigned DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_workout_log_set` (`session_id`,`exercise_name`,`set_number`,`block_order`),
  KEY `idx_session` (`session_id`),
  KEY `idx_client_exercise` (`client_id`,`exercise_name`),
  KEY `idx_client_date` (`client_id`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3513 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- NOTAS:
-- - is_cardio + duration_minutes + speed_kmh + incline_percent + heart_rate_avg + duration_seconds ya existen
-- - NO existe: cardio_type, rounds_planned, rounds_completed, rpe, cardio_metadata
-- - UPDATED_AT no existe (schema vanilla PHP heredado)
-- - UNIQUE KEY uq_workout_log_set previene duplicates
