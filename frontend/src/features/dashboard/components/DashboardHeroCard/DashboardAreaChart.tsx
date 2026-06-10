import {
  Box,
} from '@mui/material';
import {
  Area,
  AreaChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { flowlyPalette, } from '../../../../app/theme';
import {
  formatCompactAmount,
} from '../../../../shared/lib';
import type {
  CashFlowPoint,
} from '../../../analytics';
import { DashboardChartTooltip, } from './DashboardChartTooltip';

export interface DashboardAreaChartProps {
  readonly points: readonly CashFlowPoint[];
  readonly currency: string;
}

export const DashboardAreaChart = ({
  points,
  currency,
}: DashboardAreaChartProps) => {
  return (
    <Box
      aria-label="Income and balance chart"
      role="img"
      sx={{
        width: '100%',
        height: {
          xs: 220,
          md: 280,
        },
      }}
    >
      <ResponsiveContainer
        height="100%"
        width="100%"
      >
        <AreaChart
          data={points}
          margin={{
            top: 8,
            right: 8,
            bottom: 0,
            left: -16,
          }}
        >
          <defs>
            <linearGradient
              id="incomeGradient"
              x1="0"
              x2="0"
              y1="0"
              y2="1"
            >
              <stop
                offset="5%"
                stopColor={flowlyPalette.dashboard.indigo}
                stopOpacity={0.35}
              />
              <stop
                offset="95%"
                stopColor={flowlyPalette.dashboard.indigo}
                stopOpacity={0}
              />
            </linearGradient>

            <linearGradient
              id="balanceGradient"
              x1="0"
              x2="0"
              y1="0"
              y2="1"
            >
              <stop
                offset="5%"
                stopColor={flowlyPalette.dashboard.emerald}
                stopOpacity={0.25}
              />
              <stop
                offset="95%"
                stopColor={flowlyPalette.dashboard.emerald}
                stopOpacity={0}
              />
            </linearGradient>
          </defs>

          <CartesianGrid
            stroke={flowlyPalette.dashboard.borderSoft}
            strokeDasharray="4 4"
            vertical={false}
          />

          <XAxis
            axisLine={false}
            dataKey="period"
            tick={{
              fill: flowlyPalette.dashboard.textMuted,
              fontSize: 12,
            }}
            tickLine={false}
          />

          <YAxis
            axisLine={false}
            tick={{
              fill: flowlyPalette.dashboard.textMuted,
              fontSize: 12,
            }}
            tickFormatter={formatCompactAmount}
            tickLine={false}
          />

          <Tooltip
            content={(
              <DashboardChartTooltip
                currency={currency}
              />
            )}
          />

          <Area
            dataKey="income"
            fill="url(#incomeGradient)"
            stroke={flowlyPalette.dashboard.indigo}
            strokeWidth={2.5}
            type="monotone"
          />

          <Area
            dataKey="balance"
            fill="url(#balanceGradient)"
            stroke={flowlyPalette.dashboard.emerald}
            strokeWidth={2.5}
            type="monotone"
          />
        </AreaChart>
      </ResponsiveContainer>
    </Box>
  );
};
