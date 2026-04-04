/**
 * useExerciseMedia — helpers for YouTube URLs and exercise media.
 */

/**
 * Extracts the 11-character YouTube video ID from any supported YouTube URL format:
 * - https://www.youtube.com/watch?v=XXXXXXXXXXX
 * - https://youtu.be/XXXXXXXXXXX
 * - https://www.youtube.com/embed/XXXXXXXXXXX
 */
export function extractYouTubeId(url: string | null): string | null {
  if (!url) return null;
  const m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|\/embed\/)([a-zA-Z0-9_-]{11})/);
  return m ? m[1] : null;
}

/**
 * Returns a YouTube embed URL suitable for an iframe src.
 * autoplay=0 so it does not auto-play; rel=0 hides related videos;
 * modestbranding=1 reduces YouTube branding.
 */
export function getEmbedUrl(videoUrl: string | null): string | null {
  const id = extractYouTubeId(videoUrl);
  // youtube-nocookie.com reduces tracking and has fewer embedding restrictions
  return id ? `https://www.youtube-nocookie.com/embed/${id}?autoplay=0&rel=0&modestbranding=1` : null;
}

export function getWatchUrl(videoUrl: string | null): string | null {
  const id = extractYouTubeId(videoUrl);
  return id ? `https://www.youtube.com/watch?v=${id}` : null;
}
