import { types } from '../../api_types';

/**
 * Represents a single cell in the calendar grid
 */
export interface CalendarCell {
  date: Date;
  day: number;
  isCurrentMonth: boolean;
  isToday: boolean;
  contests: types.ContestListItem[];
}

/**
 * Generate an array of week day headers based on locale
 * @param weekStartsOnMonday - If true, week starts on Monday; otherwise Sunday
 * @returns Array of abbreviated day names
 */
export function getWeekDaysHeader(
  weekStartsOnMonday: boolean = true,
): string[] {
  const mondayStart = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  const sundayStart = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  return weekStartsOnMonday ? mondayStart : sundayStart;
}

/**
 * Check if two dates are the same day (ignoring time)
 */
export function isSameDay(date1: Date, date2: Date): boolean {
  return (
    date1.getFullYear() === date2.getFullYear() &&
    date1.getMonth() === date2.getMonth() &&
    date1.getDate() === date2.getDate()
  );
}

/**
 * Check if a contest is active on a specific date
 * A contest is active on a date if the date falls between start_time and finish_time
 */
export function isContestOnDate(
  contest: types.ContestListItem,
  date: Date,
): boolean {
  const startOfDay = new Date(date);
  startOfDay.setHours(0, 0, 0, 0);

  const endOfDay = new Date(date);
  endOfDay.setHours(23, 59, 59, 999);

  const contestStart = new Date(contest.start_time);
  const contestEnd = new Date(contest.finish_time);

  // Contest overlaps with the day if:
  // - Contest starts before end of day AND
  // - Contest ends after start of day
  return contestStart <= endOfDay && contestEnd >= startOfDay;
}

/**
 * Filter contests for a specific date
 */
export function getContestsForDate(
  date: Date,
  contests: types.ContestListItem[],
): types.ContestListItem[] {
  return contests.filter((contest) => isContestOnDate(contest, date));
}

/**
 * Get all contests from all categories (current, future, past)
 */
export function getAllContests(
  contestList: types.ContestList,
): types.ContestListItem[] {
  return [
    ...(contestList.current || []),
    ...(contestList.future || []),
    ...(contestList.past || []),
  ];
}

/**
 * Generate calendar grid cells for a given month
 * Returns 42 cells (6 weeks x 7 days) to ensure consistent grid layout
 */
export function generateCalendarCells(
  year: number,
  month: number,
  contests: types.ContestListItem[],
  weekStartsOnMonday: boolean = true,
): CalendarCell[] {
  const cells: CalendarCell[] = [];
  const today = new Date();

  // First day of the current month
  const firstDayOfMonth = new Date(year, month, 1);
  // Last day of the current month
  const lastDayOfMonth = new Date(year, month + 1, 0);

  // Get the day of week for the first day (0 = Sunday, 1 = Monday, etc.)
  let firstDayOfWeek = firstDayOfMonth.getDay();

  // Adjust for Monday start
  if (weekStartsOnMonday) {
    firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;
  }

  // Add days from previous month to fill the first row
  const prevMonth = new Date(year, month, 0); // Last day of previous month
  const prevMonthDays = prevMonth.getDate();

  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    const day = prevMonthDays - i;
    const date = new Date(year, month - 1, day);
    cells.push({
      date,
      day,
      isCurrentMonth: false,
      isToday: isSameDay(date, today),
      contests: getContestsForDate(date, contests),
    });
  }

  // Add days from current month
  for (let day = 1; day <= lastDayOfMonth.getDate(); day++) {
    const date = new Date(year, month, day);
    cells.push({
      date,
      day,
      isCurrentMonth: true,
      isToday: isSameDay(date, today),
      contests: getContestsForDate(date, contests),
    });
  }

  // Add days from next month to complete the grid (42 cells = 6 rows)
  const remainingCells = 42 - cells.length;
  for (let day = 1; day <= remainingCells; day++) {
    const date = new Date(year, month + 1, day);
    cells.push({
      date,
      day,
      isCurrentMonth: false,
      isToday: isSameDay(date, today),
      contests: getContestsForDate(date, contests),
    });
  }

  return cells;
}

/**
 * Format month and year for display
 */
export function formatMonthYear(year: number, month: number): string {
  const date = new Date(year, month, 1);
  return date.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
}

/**
 * Get the start and end dates for a week containing the given date
 */
export function getWeekRange(
  date: Date,
  weekStartsOnMonday: boolean = true,
): { start: Date; end: Date } {
  const start = new Date(date);
  const dayOfWeek = start.getDay();

  // Calculate offset to week start
  let offset: number;
  if (weekStartsOnMonday) {
    offset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
  } else {
    offset = -dayOfWeek;
  }

  start.setDate(start.getDate() + offset);
  start.setHours(0, 0, 0, 0);

  const end = new Date(start);
  end.setDate(end.getDate() + 6);
  end.setHours(23, 59, 59, 999);

  return { start, end };
}

/**
 * Generate week view cells for a specific week
 */
export function generateWeekCells(
  centerDate: Date,
  contests: types.ContestListItem[],
  weekStartsOnMonday: boolean = true,
): CalendarCell[] {
  const { start } = getWeekRange(centerDate, weekStartsOnMonday);
  const today = new Date();
  const cells: CalendarCell[] = [];

  for (let i = 0; i < 7; i++) {
    const date = new Date(start);
    date.setDate(date.getDate() + i);
    cells.push({
      date,
      day: date.getDate(),
      isCurrentMonth: date.getMonth() === centerDate.getMonth(),
      isToday: isSameDay(date, today),
      contests: getContestsForDate(date, contests),
    });
  }

  return cells;
}
