import type {
  CurrencyTrend,
} from '../types';

export const getCurrencyTrend = (
  percentageChange: number,
): CurrencyTrend => {
  if (percentageChange > 0) {
    return 'up';
  }

  if (percentageChange < 0) {
    return 'down';
  }

  return 'neutral';
};