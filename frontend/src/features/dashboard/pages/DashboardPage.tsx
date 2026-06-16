import {
  Box,
} from '@mui/material';
import {
  useState,
} from 'react';
import type {
  DashboardRange,
} from '../../../shared/lib';
import { DashboardHeroCard, } from '../components/DashboardHeroCard';
import { RecentTransactionsSection, } from '../components/RecentTransactionsSection';
import { WalletPreviewSection, } from '../components/WalletPreviewSection';
import { useDashboardPageData, } from '../hooks';

export const DashboardPage = () => {
  const [range, setRange,] = useState<DashboardRange>('yearly');

  const {
    analyticsQuery,
    previousAnalyticsQuery,
    walletsQuery,
    transactionsQuery,
    balanceChangePercentage,
  } = useDashboardPageData(range);

  return (
    <Box>
      <DashboardHeroCard
        balanceChangePercentage={balanceChangePercentage}
        data={analyticsQuery.data}
        error={analyticsQuery.isError}
        loading={
          analyticsQuery.isPending
          || previousAnalyticsQuery.isPending
        }
        onRangeChange={setRange}
        range={range}
      />

      <WalletPreviewSection
        data={walletsQuery.data}
        error={walletsQuery.isError}
        loading={walletsQuery.isPending}
      />

      <RecentTransactionsSection
        data={transactionsQuery.data}
        error={transactionsQuery.isError}
        loading={transactionsQuery.isPending}
      />
    </Box>
  );
};
