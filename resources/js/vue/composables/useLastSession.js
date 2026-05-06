/**
 * useLastSession(exercise)
 *
 * Resuelve el objeto last_session del payload extendido del WorkoutPlayer v2.
 * Si exercise.last_session no existe (cliente con plan viejo o sin registros),
 * cae al patrón legacy last_weight / last_reps.
 *
 * Returns: { weight, reps, daysAgo, weightDelta, sessionId } | null
 */
export function useLastSession(exercise) {
    if (!exercise) return null;

    if (exercise.last_session) {
        const ls = exercise.last_session;
        return {
            weight: ls.weight ?? null,
            reps: ls.reps ?? 0,
            daysAgo: ls.days_ago ?? 0,
            weightDelta: ls.delta_kg ?? 0,
            sessionId: ls.session_id ?? null,
        };
    }

    if (exercise.last_weight !== undefined && exercise.last_weight !== null) {
        return {
            weight: exercise.last_weight,
            reps: exercise.last_reps ?? 0,
            daysAgo: 0,
            weightDelta: 0,
            sessionId: null,
        };
    }

    return null;
}
