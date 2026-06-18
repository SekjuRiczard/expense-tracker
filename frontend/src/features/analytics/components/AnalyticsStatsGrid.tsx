import { Box } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { AnalyticsSummary } from '../types';
import { AnalyticsStatCard } from './AnalyticsStatCard';
import { formatMoney } from './analyticsHelpers';

export interface AnalyticsStatsGridProps {
  readonly summary: AnalyticsSummary;
  readonly currency: string;
}

export const AnalyticsStatsGrid = ({
  summary,
  currency,
}: AnalyticsStatsGridProps) => {
  return (
    <Box
      sx={{
        display: 'grid',
        gridTemplateColumns: {
          xs: 'repeat(2, minmax(0, 1fr))',
          lg: 'repeat(4, minmax(0, 1fr))',
        },
        gap: 2,
        mb: 3,
      }}
    >
      <AnalyticsStatCard
        label="Income"
        value={formatMoney(summary.income, currency)}
        valueColor={flowlyPalette.dashboard.emerald}
      />
      <AnalyticsStatCard
        label="Expenses"
        value={formatMoney(summary.expense, currency)}
        valueColor={flowlyPalette.dashboard.rose}
      />
      <AnalyticsStatCard
        label="Balance"
        value={formatMoney(summary.balance, currency)}
      />
      <AnalyticsStatCard
        label="Transaction count"
        value={String(summary.transactionCount)}
      />
    </Box>
  );
};
