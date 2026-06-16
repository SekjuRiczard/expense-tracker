import { Box, Typography } from '@mui/material';
import {
  Bar,
  BarChart,
  CartesianGrid,
  Legend,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { flowlyPalette } from '../../../app/theme';
import { formatCompactAmount } from '../../../shared/lib';
import type { CashFlowPoint } from '../types';
import { formatMoney, formatPeriodShort } from './analyticsHelpers';

export interface CashFlowChartProps {
  readonly data: readonly CashFlowPoint[];
  readonly currency: string;
}

interface TooltipPayloadEntry {
  readonly name?: string;
  readonly value?: number;
  readonly color?: string;
}

const ChartTooltip = ({
  active,
  label,
  payload,
  currency,
}: {
  readonly active?: boolean;
  readonly label?: string;
  readonly payload?: readonly TooltipPayloadEntry[];
  readonly currency: string;
}) => {
  if (!active || !payload || payload.length === 0) {
    return null;
  }

  return (
    <Box
      sx={{
        px: 1.5,
        py: 1,
        borderRadius: '12px',
        backgroundColor: '#0F172A',
        color: '#FFFFFF',
      }}
    >
      <Typography sx={{ mb: 0.5, fontSize: '0.72rem', opacity: 0.8 }}>
        {label}
      </Typography>
      {payload.map((entry) => (
        <Typography key={entry.name} sx={{ fontSize: '0.78rem', fontWeight: 600 }}>
          {entry.name}: {formatMoney(entry.value ?? 0, currency)}
        </Typography>
      ))}
    </Box>
  );
};

export const CashFlowChart = ({ data, currency }: CashFlowChartProps) => {
  const chartData = data.map((point) => ({
    period: formatPeriodShort(point.period),
    Income: point.income,
    Expenses: point.expense,
  }));

  return (
    <Box
      sx={{
        p: 3,
        height: '100%',
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: '20px',
        backgroundColor: flowlyPalette.dashboard.surface,
      }}
    >
      <Typography
        component="h2"
        sx={{
          mb: 3,
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: '1.05rem',
          fontWeight: 700,
        }}
      >
        Cash flow
      </Typography>

      {data.length === 0 ? (
        <Typography
          sx={{
            py: 4,
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.82rem',
            textAlign: 'center',
          }}
        >
          No cash flow data in this period.
        </Typography>
      ) : (
        <Box sx={{ width: '100%', height: 280 }}>
          <ResponsiveContainer height="100%" width="100%">
            <BarChart data={chartData}>
              <CartesianGrid
                stroke="#f1f5f9"
                strokeDasharray="3 3"
                vertical={false}
              />
              <XAxis
                axisLine={false}
                dataKey="period"
                tick={{ fill: '#94a3b8', fontSize: 12 }}
                tickLine={false}
              />
              <YAxis
                axisLine={false}
                tick={{ fill: '#94a3b8', fontSize: 12 }}
                tickFormatter={formatCompactAmount}
                tickLine={false}
              />
              <Tooltip
                content={<ChartTooltip currency={currency} />}
                cursor={{ fill: 'rgba(148, 163, 184, 0.1)' }}
              />
              <Legend
                wrapperStyle={{ fontSize: 12 }}
              />
              <Bar dataKey="Income" fill="#6366f1" radius={[6, 6, 0, 0]} />
              <Bar dataKey="Expenses" fill="#f43f5e" radius={[6, 6, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </Box>
      )}
    </Box>
  );
};
