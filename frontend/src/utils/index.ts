export function formatSecondsToMMSS(seconds: number): string {
  const mm = String(Math.floor(seconds / 60)).padStart(2, '0');
  const ss = String(seconds % 60).padStart(2, '0');
  return `${mm}:${ss}`;
}

export function parseDuration(input: string): number {
  if (!input) return 0;

  const match = input.match(/^(\d+):([0-5]\d)$/);
  if (match) {
    const minutes = parseInt(match[1], 10);
    const seconds = parseInt(match[2], 10);
    return minutes * 60 + seconds;
  }

  const parsed = parseInt(input, 10);
  return isNaN(parsed) ? 0 : parsed;
}
