import { Box, Paper, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { Wallet } from '../types';
import {
  WALLET_TYPE_LABELS,
  WALLET_TYPE_ICONS,
  formatBalance,
  formatShortDate,
} from './walletHelpers';
import { WalletActionsMenu } from './WalletActionsMenu';

export interface WalletCardProps {
  readonly wallet: Wallet;
  readonly onViewDetails: () => void;
  readonly onEdit: () => void;
  readonly onDelete: () => void;
}

export const WalletCard = ({
  wallet,
  onViewDetails,
  onEdit,
  onDelete,
}: WalletCardProps) => {
  const Icon = WALLET_TYPE_ICONS[wallet.type];
  const typeLabel = WALLET_TYPE_LABELS[wallet.type];

  return (
    <Paper
      elevation={0}
      sx={{
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'space-between',
        p: 3,
        minHeight: 220,
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: '20px',
        backgroundColor: flowlyPalette.dashboard.surface,
        transition: 'border-color 200ms ease',
        '&:hover': {
          borderColor: '#A5B4FC',
        },
      }}
    >
      <Box>
        <Box
          sx={{
            display: 'flex',
            alignItems: 'flex-start',
            justifyContent: 'space-between',
            mb: 2,
          }}
        >
          <Box
            aria-hidden="true"
            sx={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              width: 48,
              height: 48,
              borderRadius: '14px',
              background: 'linear-gradient(135deg, #EEF2FF, #E0E7FF)',
              color: flowlyPalette.dashboard.indigoDark,
            }}
          >
            <Icon sx={{ fontSize: 22 }} />
          </Box>

          <WalletActionsMenu
            onDelete={onDelete}
            onEdit={onEdit}
            onViewDetails={onViewDetails}
          />
        </Box>

        <Typography
          sx={{
            color: flowlyPalette.dashboard.textPrimary,
            fontSize: '1rem',
            fontWeight: 600,
            lineHeight: 1.3,
            mb: 0.4,
          }}
        >
          {wallet.name}
        </Typography>

        <Typography
          sx={{
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.78rem',
          }}
        >
          {typeLabel}
        </Typography>

        <Typography
          sx={{
            mt: 1.5,
            color: flowlyPalette.dashboard.textPrimary,
            fontSize: '1.5rem',
            fontWeight: 700,
            fontVariantNumeric: 'tabular-nums',
            lineHeight: 1.2,
          }}
        >
          {formatBalance(wallet.balanceAmount, wallet.currency)}
        </Typography>
      </Box>

      <Box
        sx={{
          mt: 2,
          pt: 1.75,
          borderTop: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        }}
      >
        <Typography
          sx={{
            color: flowlyPalette.dashboard.textMuted,
            fontSize: '0.72rem',
          }}
        >
          Updated: {formatShortDate(wallet.updatedAt)}
        </Typography>
      </Box>
    </Paper>
  );
};
