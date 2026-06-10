import {
  Box,
  Stack,
  Typography,
} from '@mui/material';
import { flowlyPalette, } from '../../../../app/theme';
import { formatCurrency, } from '../../../../shared/lib';

interface TooltipPayloadItem {
  readonly dataKey?: string;
  readonly value?: number;
}

export interface DashboardChartTooltipProps {
  readonly active?: boolean;
  readonly label?: string;
  readonly payload?: readonly TooltipPayloadItem[];
  readonly currency: string;
}

export const DashboardChartTooltip = ({
  active,
  label,
  payload,
  currency,
}: DashboardChartTooltipProps) => {
  if (!active || !payload?.length) {
    return null;
  }

  const income = payload.find(
    (item) => item.dataKey === 'income',
  )?.value ?? 0;

  const balance = payload.find(
    (item) => item.dataKey === 'balance',
  )?.value ?? 0;

  return (
    <Box
      sx={{
        minWidth: 180,
        p: 1.5,
        borderRadius: 2,
        backgroundColor: flowlyPalette.dashboard.textPrimary,
        boxShadow: '0 12px 24px rgba(15, 23, 42, 0.18)',
      }}
    >
      <Typography
        sx={{
          color: flowlyPalette.dashboard.surface,
          fontSize: '0.8rem',
          fontWeight: 800,
        }}
      >
        {label}
      </Typography>

      <Stack
        sx={{
          gap: 0.5,
          mt: 1,
        }}
      >
        <Typography
          sx={{
            color: flowlyPalette.dashboard.surface,
            fontSize: '0.75rem',
          }}
        >
          Income: {formatCurrency(income, currency)}
        </Typography>

        <Typography
          sx={{
            color: flowlyPalette.dashboard.surface,
            fontSize: '0.75rem',
          }}
        >
          Balance: {formatCurrency(balance, currency)}
        </Typography>
      </Stack>
    </Box>
  );
};
