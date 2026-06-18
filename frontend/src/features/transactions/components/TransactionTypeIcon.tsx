import { CallMadeRounded, CallReceivedRounded } from '@mui/icons-material';
import { Box } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { TransactionType } from '../types';

export interface TransactionTypeIconProps {
  readonly type: TransactionType;
}

export const TransactionTypeIcon = ({ type }: TransactionTypeIconProps) => {
  const isIncome = type === 'income';
  const Icon = isIncome ? CallReceivedRounded : CallMadeRounded;

  return (
    <Box
      aria-hidden="true"
      sx={{
        display: 'grid',
        width: 32,
        height: 32,
        flexShrink: 0,
        placeItems: 'center',
        borderRadius: '999px',
        backgroundColor: isIncome
          ? flowlyPalette.dashboard.emeraldSoft
          : flowlyPalette.dashboard.roseSoft,
        color: isIncome
          ? flowlyPalette.dashboard.emerald
          : flowlyPalette.dashboard.rose,
      }}
    >
      <Icon sx={{ fontSize: 16 }} />
    </Box>
  );
};
