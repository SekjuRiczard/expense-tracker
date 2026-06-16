import { Box } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { TransactionType } from '../types';
import { TRANSACTION_TYPE_LABELS } from './transactionHelpers';

export interface TransactionTypePillProps {
  readonly type: TransactionType;
}

export const TransactionTypePill = ({ type }: TransactionTypePillProps) => {
  const isIncome = type === 'income';

  return (
    <Box
      component="span"
      sx={{
        display: 'inline-flex',
        alignItems: 'center',
        px: 1,
        py: 0.4,
        borderRadius: '999px',
        fontSize: '0.65rem',
        fontWeight: 700,
        lineHeight: 1,
        backgroundColor: isIncome
          ? flowlyPalette.dashboard.emeraldSoft
          : flowlyPalette.dashboard.roseSoft,
        color: isIncome ? '#047857' : '#BE123C',
      }}
    >
      {TRANSACTION_TYPE_LABELS[type]}
    </Box>
  );
};
