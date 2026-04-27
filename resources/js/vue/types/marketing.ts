import type { CoachDropV1 } from './coach-drop-v1.generated';

export type DropStatus =
  | 'pending' | 'generating' | 'in_review' | 'approved'
  | 'ready' | 'in_progress' | 'completed' | 'archived';

export type SpecialtyValue =
  | 'fuerza' | 'hipertrofia' | 'recomposicion'
  | 'perdida_grasa' | 'mujeres_postparto' | 'funcional' | 'otro';

export type AudienceAgeRange = '18-25' | '25-35' | '35-45' | '45+';
export type AudienceGender = 'mujeres' | 'hombres' | 'mixto';
export type AudienceOfferMain = 'esencial' | 'metodo' | 'elite' | 'presencial' | 'otro';

export interface VoiceSample {
  caption: string;
  source_url: string | null;
  note: string | null;
}

export interface ActiveOffer {
  name: string;
  price: number;
  currency: string;
  promo: string | null;
}

export interface TopWorkingPost {
  url: string;
  why_worked: string;
}

export interface MarketingProfile {
  id: number;
  brand_name: string;
  city: string | null;
  country_code: string | null;
  specialty_primary: SpecialtyValue | null;
  specialty_primary_other: string | null;
  specialty_secondary: SpecialtyValue | null;
  specialty_secondary_other: string | null;
  differentiator: string;
  audience_age_range: AudienceAgeRange | null;
  audience_gender: AudienceGender | null;
  audience_pain_main: string;
  audience_offer_main: AudienceOfferMain | null;
  preferred_methodologies: string[];
  preferred_methodologies_other: string[];
  content_topics: string[];
  content_topics_other: string[];
  voice_adjectives: string[];
  voice_samples: VoiceSample[];
  active_offers: ActiveOffer[];
  top_working_posts: TopWorkingPost[];
  completed_at: string | null;
  is_complete: boolean;
}

export interface PieceState {
  piece_type: 'reel' | 'story' | 'checklist_phase';
  piece_key: string;
  state: 'pending' | 'in_progress' | 'published' | 'skipped';
  published_url: string | null;
  state_changed_at: string | null;
}

export interface CoachDrop {
  id: number;
  iso_year: number;
  iso_week: number;
  week_starts_on: string;
  status: DropStatus;
  schema_version: 'coach_drop_v1';
  content: CoachDropV1;
  attribution: string;
  ready_at: string | null;
  completed_at: string | null;
  pieces: PieceState[];
}

export interface CoachDropSummary {
  id: number;
  iso_year: number;
  iso_week: number;
  week_starts_on: string;
  status: DropStatus;
  brief_title: string | null;
  pieces_completed: number;
  pieces_total: number;
}

export interface HistoryResponse {
  data: CoachDropSummary[];
  meta: { total: number; current_page: number; last_page?: number };
}

export type DayCode = 'LUN' | 'MAR' | 'MIE' | 'JUE' | 'VIE' | 'SAB' | 'DOM';

export const DAY_COLOR: Record<DayCode, string> = {
  LUN: 'var(--color-wc-day-lun)',
  MAR: 'var(--color-wc-day-mar)',
  MIE: 'var(--color-wc-day-mie)',
  JUE: 'var(--color-wc-day-jue)',
  VIE: 'var(--color-wc-day-vie)',
  SAB: 'var(--color-wc-day-sab)',
  DOM: 'var(--color-wc-day-dom)',
};
