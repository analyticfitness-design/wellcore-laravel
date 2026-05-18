-- ============================================================================
-- WELLCORE KB — Schema inicial Sprint 0 Motor v2
-- Fecha: 2026-05-17
-- ============================================================================
--
-- SAFETY GUARANTEES:
--   1. Crea base de datos NUEVA `wellcore_kb` (NO toca `wellcore_fitness`).
--   2. Solo CREATE IF NOT EXISTS (idempotente, no destruye nada).
--   3. NO contiene DROP, ALTER, TRUNCATE, DELETE.
--   4. Ejecutar en HERD MySQL LOCAL (127.0.0.1:3306) — NUNCA en producción.
--
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `wellcore_kb`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `wellcore_kb`;

-- ----------------------------------------------------------------------------
-- methodologies — catálogo central de metodologías de entrenamiento
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `methodologies` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL COMMENT 'Identificador único kebab-case ej. body-part-split-5d',
  `name` VARCHAR(200) NOT NULL COMMENT 'Nombre humano de la metodología',
  `type` VARCHAR(40) NOT NULL DEFAULT 'entrenamiento' COMMENT 'entrenamiento | nutricion | suplementacion',
  `source` VARCHAR(60) NULL COMMENT 'literatura_clasica | literatura_cientifica | wellcore_adaptado',
  `evidence_level` VARCHAR(20) NULL COMMENT 'alta | moderada | baja | anecdotica',
  `is_split_agnostic` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=es periodización/sistema que se monta sobre split base',
  `short_description` TEXT NULL,
  `applicable_tiers` JSON NULL COMMENT 'Array: esencial, metodo, elite, rise, entreno_solo, etc.',
  `applicable_levels` JSON NULL COMMENT 'Array: principiante, intermedio, avanzado',
  `applicable_objectives` JSON NULL,
  `applicable_gender` JSON NULL,
  `applicable_days_range` JSON NULL COMMENT 'Array de días disponibles del cliente, ej [4,5,6]',
  `applicable_locations` JSON NULL,
  `raw_data` JSON NOT NULL COMMENT 'Entry completo del JSON seed para no perder estructura',
  `version` INT NOT NULL DEFAULT 1,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_methodologies_slug` (`slug`),
  KEY `idx_methodologies_type` (`type`),
  KEY `idx_methodologies_active` (`active`),
  KEY `idx_methodologies_split_agnostic` (`is_split_agnostic`),
  KEY `idx_methodologies_evidence` (`evidence_level`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de metodologías de entrenamiento curado para motor v2';

-- ----------------------------------------------------------------------------
-- methodologies_seed_runs — audit trail de ejecuciones del seed
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `methodologies_seed_runs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `json_version` VARCHAR(20) NULL COMMENT 'methodologies-seed.json version field',
  `json_generated_at` VARCHAR(40) NULL,
  `json_source_path` VARCHAR(255) NULL,
  `seed_started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `seed_finished_at` TIMESTAMP NULL DEFAULT NULL,
  `rows_inserted` INT NOT NULL DEFAULT 0,
  `rows_updated` INT NOT NULL DEFAULT 0,
  `rows_skipped` INT NOT NULL DEFAULT 0,
  `rows_total` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'started' COMMENT 'started | completed | failed',
  `notes` TEXT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_seed_runs_status` (`status`),
  KEY `idx_seed_runs_started` (`seed_started_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Audit trail de ejecuciones del seed de methodologies';
