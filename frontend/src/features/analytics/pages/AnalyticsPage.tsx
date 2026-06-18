import { Alert, Box, Button } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import type { AppLayoutOutletContext } from '../../../app/layouts/AppLayout';
import {
  AnalyticsFilters,
  AnalyticsSkeleton,
  AnalyticsStatsGrid,
  CashFlowChart,
  CashFlowTable,
  ExpensesByCategoryChart,
} from '../components';
import { useAnalyticsDashboard } from '../hooks';
import type { AnalyticsCurrency } from '../types';

const DEFAULT_FROM = '2026-01-01';
const DEFAULT_TO = '2026-12-31';
const DEFAULT_CURRENCY: AnalyticsCurrency = 'PLN';

export const AnalyticsPage = () => {
  const { setHeaderOverride } = useOutletContext<AppLayoutOutletContext>();

  const [from, setFrom] = useState(DEFAULT_FROM);
  const [to, setTo] = useState(DEFAULT_TO);
  const [currency, setCurrency] = useState<AnalyticsCurrency>(DEFAULT_CURRENCY);

  const invalidRange = Boolean(from) && Boolean(to) && from > to;

  const params = useMemo(
    () => ({ from, to, currency }),
    [from, to, currency],
  );

  const dashboardQuery = useAnalyticsDashboard(params, {
    enabled: !invalidRange,
  });

  const data = dashboardQuery.data;

  useEffect(() => {
    setHeaderOverride({
      subtitle: 'Full picture of your finances',
      action: <></>,
    });

    return () => {
      setHeaderOverride(null);
    };
  }, [setHeaderOverride]);

  const filters = (
    <AnalyticsFilters
      currency={currency}
      from={from}
      invalidRange={invalidRange}
      onCurrencyChange={setCurrency}
      onFromChange={setFrom}
      onToChange={setTo}
      to={to}
    />
  );

  if (invalidRange) {
    return (
      <Box>
        {filters}
        <Alert severity="warning" sx={{ borderRadius: '12px' }}>
          Please select a valid date range to view analytics.
        </Alert>
      </Box>
    );
  }

  if (dashboardQuery.isError) {
    return (
      <Box>
        {filters}
        <Alert
          action={
            <Button
              color="inherit"
              onClick={() => { void dashboardQuery.refetch(); }}
              size="small"
            >
              Retry
            </Button>
          }
          severity="error"
          sx={{ borderRadius: '12px' }}
        >
          Failed to load analytics.
        </Alert>
      </Box>
    );
  }

  if (dashboardQuery.isPending || !data) {
    return <AnalyticsSkeleton />;
  }

  const displayCurrency = data.summary.currency || currency;

  return (
    <Box>
      {filters}

      <AnalyticsStatsGrid currency={displayCurrency} summary={data.summary} />

      <Box
        sx={{
          display: 'grid',
          gridTemplateColumns: { xs: '1fr', lg: 'repeat(3, minmax(0, 1fr))' },
          gap: 3,
          mb: 3,
        }}
      >
        <Box sx={{ gridColumn: { lg: 'span 1' } }}>
          <ExpensesByCategoryChart
            currency={displayCurrency}
            data={data.categoryBreakdown}
          />
        </Box>

        <Box sx={{ gridColumn: { lg: 'span 2' } }}>
          <CashFlowChart currency={displayCurrency} data={data.cashFlow} />
        </Box>
      </Box>

      <CashFlowTable currency={displayCurrency} data={data.cashFlow} />
    </Box>
  );
};
