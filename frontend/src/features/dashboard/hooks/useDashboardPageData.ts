import {
  useMemo,
} from 'react';
import {
  useAnalyticsDashboard,
} from '../../analytics';
import {
  useTransactions,
} from '../../transactions';
import {
  useWallets,
} from '../../wallets';
import {
  getDashboardPeriod,
  getPreviousDashboardPeriod,
  type DashboardRange,
} from '../../../shared/lib';

const calculatePercentageChange = (
  currentValue: number,
  previousValue: number,
): number | null => {
  if (previousValue === 0) {
    return null;
  }

  return (
    (currentValue - previousValue)
    / Math.abs(previousValue)
  ) * 100;
};

export const useDashboardPageData = (
  range: DashboardRange,
) => {
  const currentPeriod = useMemo(
    () => getDashboardPeriod(range),
    [range],
  );

  const previousPeriod = useMemo(
    () => getPreviousDashboardPeriod(range),
    [range],
  );

  const analyticsQuery = useAnalyticsDashboard(
    currentPeriod,
  );

  const previousAnalyticsQuery = useAnalyticsDashboard(
    previousPeriod,
  );

  const walletsQuery = useWallets();

  const transactionsQuery = useTransactions({
    page: 1,
    limit: 5,
  });

  const balanceChangePercentage = useMemo(() => {
    if (
      !analyticsQuery.data
      || !previousAnalyticsQuery.data
    ) {
      return null;
    }

    return calculatePercentageChange(
      analyticsQuery.data.summary.balance,
      previousAnalyticsQuery.data.summary.balance,
    );
  }, [
    analyticsQuery.data,
    previousAnalyticsQuery.data,
  ]);

  return {
    analyticsQuery,
    previousAnalyticsQuery,
    walletsQuery,
    transactionsQuery,
    balanceChangePercentage,
  };
};
