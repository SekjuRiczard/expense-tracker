import { Box, Stack, Typography } from '@mui/material';
import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';
import { flowlyPalette } from '../../../app/theme';
import type { CategoryBreakdownItem } from '../types';
import { CATEGORY_COLORS, formatMoney } from './analyticsHelpers';

export interface ExpensesByCategoryChartProps {
  readonly data: readonly CategoryBreakdownItem[];
  readonly currency: string;
}

interface TooltipPayloadEntry {
  readonly name?: string;
  readonly value?: number;
}

const ChartTooltip = ({
  active,
  payload,
  currency,
}: {
  readonly active?: boolean;
  readonly payload?: readonly TooltipPayloadEntry[];
  readonly currency: string;
}) => {
  if (!active || !payload || payload.length === 0) {
    return null;
  }

  const entry = payload[0];

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
      <Typography sx={{ fontSize: '0.72rem', opacity: 0.8 }}>
        {entry.name}
      </Typography>
      <Typography sx={{ fontSize: '0.82rem', fontWeight: 700 }}>
        {formatMoney(entry.value ?? 0, currency)}
      </Typography>
    </Box>
  );
};

export const ExpensesByCategoryChart = ({
  data,
  currency,
}: ExpensesByCategoryChartProps) => {
  const chartData = data.map((item) => ({
    name: item.categoryName,
    value: item.amount,
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
        Expenses by category
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
          No expense categories in this period.
        </Typography>
      ) : (
        <>
          <Box sx={{ width: '100%', height: 200 }}>
            <ResponsiveContainer height="100%" width="100%">
              <PieChart>
                <Pie
                  cx="50%"
                  cy="50%"
                  data={chartData}
                  dataKey="value"
                  innerRadius={55}
                  nameKey="name"
                  outerRadius={85}
                  paddingAngle={2}
                  stroke="none"
                >
                  {chartData.map((_entry, index) => (
                    <Cell
                      fill={CATEGORY_COLORS[index % CATEGORY_COLORS.length]}
                      key={index}
                    />
                  ))}
                </Pie>
                <Tooltip
                  content={<ChartTooltip currency={currency} />}
                />
              </PieChart>
            </ResponsiveContainer>
          </Box>

          <Stack component="ul" sx={{ gap: 1, mt: 2, p: 0, listStyle: 'none' }}>
            {data.map((item, index) => (
              <Stack
                component="li"
                key={item.categoryId}
                sx={{
                  alignItems: 'center',
                  flexDirection: 'row',
                  justifyContent: 'space-between',
                  gap: 1,
                }}
              >
                <Stack
                  sx={{
                    alignItems: 'center',
                    flexDirection: 'row',
                    gap: 1,
                    minWidth: 0,
                  }}
                >
                  <Box
                    aria-hidden="true"
                    sx={{
                      width: 10,
                      height: 10,
                      flexShrink: 0,
                      borderRadius: '999px',
                      backgroundColor:
                        CATEGORY_COLORS[index % CATEGORY_COLORS.length],
                    }}
                  />
                  <Typography
                    sx={{
                      color: flowlyPalette.dashboard.textSecondary,
                      fontSize: '0.75rem',
                      overflow: 'hidden',
                      textOverflow: 'ellipsis',
                      whiteSpace: 'nowrap',
                    }}
                  >
                    {item.categoryName}
                  </Typography>
                </Stack>

                <Stack
                  sx={{
                    alignItems: 'center',
                    flexDirection: 'row',
                    flexShrink: 0,
                    gap: 1,
                  }}
                >
                  <Typography
                    sx={{
                      color: flowlyPalette.dashboard.textMuted,
                      fontSize: '0.72rem',
                    }}
                  >
                    {item.percentage.toFixed(1)}%
                  </Typography>
                  <Typography
                    sx={{
                      color: flowlyPalette.dashboard.textPrimary,
                      fontSize: '0.72rem',
                      fontWeight: 600,
                    }}
                  >
                    {formatMoney(item.amount, currency)}
                  </Typography>
                </Stack>
              </Stack>
            ))}
          </Stack>
        </>
      )}
    </Box>
  );
};
