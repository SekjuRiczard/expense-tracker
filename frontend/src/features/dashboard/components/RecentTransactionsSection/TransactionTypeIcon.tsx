import {
  CallMadeRounded,
  CallReceivedRounded,
} from '@mui/icons-material';
import {
  Box,
} from '@mui/material';
import { flowlyPalette, } from '../../../../app/theme';
import type {
  TransactionType,
} from '../../../transactions';

export interface TransactionTypeIconProps {
  readonly type: TransactionType;
}

export const TransactionTypeIcon = ({
  type,
}: TransactionTypeIconProps) => {
  const isIncome = type === 'income';
  const Icon = isIncome
    ? CallReceivedRounded
    : CallMadeRounded;

  return (
    <Box
      sx={{
        display: 'grid',
        width: 34,
        height: 34,
        flexShrink: 0,
        placeItems: 'center',
        borderRadius: '50%',
        backgroundColor: isIncome
          ? flowlyPalette.dashboard.emeraldSoft
          : flowlyPalette.dashboard.roseSoft,
        color: isIncome
          ? flowlyPalette.dashboard.emerald
          : flowlyPalette.dashboard.rose,
      }}
    >
      <Icon
        sx={{
          fontSize: 18,
        }}
      />
    </Box>
  );
};
