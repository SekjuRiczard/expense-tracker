import { formatCurrency } from '../../../shared/lib';

export const CATEGORY_COLORS = [
  '#6366f1',
  '#10b981',
  '#f59e0b',
  '#8b5cf6',
  '#ec4899',
  '#06b6d4',
] as const;

export const formatMoney = (amount: number, currency: string): string => {
  return formatCurrency(amount, currency);
};

const MONTH_LABELS = [
  'Jan',
  'Feb',
  'Mar',
  'Apr',
  'May',
  'Jun',
  'Jul',
  'Aug',
  'Sep',
  'Oct',
  'Nov',
  'Dec',
] as const;

const parsePeriod = (period: string): { month: number; year: string } | null => {
  const match = /^(\d{4})-(\d{2})$/.exec(period);
  if (!match) return null;
  const year = match[1];
  const month = Number(match[2]) - 1;
  if (month < 0 || month > 11) return null;
  return { month, year };
};

export const formatPeriodShort = (period: string): string => {
  const parsed = parsePeriod(period);
  return parsed ? MONTH_LABELS[parsed.month] : period;
};

export const formatPeriodLong = (period: string): string => {
  const parsed = parsePeriod(period);
  return parsed ? `${MONTH_LABELS[parsed.month]} ${parsed.year}` : period;
};
