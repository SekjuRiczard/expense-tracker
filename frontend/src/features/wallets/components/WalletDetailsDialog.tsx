import {
  Box,
  Button,
  Dialog,
  DialogContent,
  DialogTitle,
  Typography,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { Wallet } from '../types';
import {
  WALLET_TYPE_ICONS,
  WALLET_TYPE_LABELS,
  formatBalance,
  formatDateTime,
} from './walletHelpers';

export interface WalletDetailsDialogProps {
  readonly open: boolean;
  readonly wallet: Wallet | null;
  readonly onClose: () => void;
}

const DetailRow = ({
  label,
  value,
}: {
  readonly label: string;
  readonly value: string;
}) => (
  <Box
    sx={{
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      py: 1.25,
      borderBottom: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
      '&:last-child': { borderBottom: 'none', pb: 0 },
      '&:first-of-type': { pt: 0 },
    }}
  >
    <Typography
      sx={{
        color: flowlyPalette.dashboard.textSecondary,
        fontSize: '0.82rem',
        fontWeight: 500,
      }}
    >
      {label}
    </Typography>

    <Typography
      sx={{
        color: flowlyPalette.dashboard.textPrimary,
        fontSize: '0.85rem',
        fontWeight: 600,
        textAlign: 'right',
      }}
    >
      {value}
    </Typography>
  </Box>
);

export const WalletDetailsDialog = ({
  open,
  wallet,
  onClose,
}: WalletDetailsDialogProps) => {
  if (!wallet) return null;

  const Icon = WALLET_TYPE_ICONS[wallet.type];

  return (
    <Dialog
      maxWidth="xs"
      onClose={onClose}
      open={open}
      slotProps={{
        paper: {
          sx: { borderRadius: '20px', width: '100%' },
        },
      }}
    >
      <DialogTitle
        sx={{
          pb: 0,
          pt: 3,
          px: 3,
          display: 'flex',
          alignItems: 'center',
          gap: 1.5,
        }}
      >
        <Box
          aria-hidden="true"
          sx={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            width: 40,
            height: 40,
            borderRadius: '12px',
            background: 'linear-gradient(135deg, #EEF2FF, #E0E7FF)',
            color: flowlyPalette.dashboard.indigoDark,
            flexShrink: 0,
          }}
        >
          <Icon sx={{ fontSize: 20 }} />
        </Box>

        <Typography
          component="span"
          sx={{
            color: flowlyPalette.dashboard.textPrimary,
            fontSize: '1.1rem',
            fontWeight: 800,
            letterSpacing: '-0.02em',
          }}
        >
          {wallet.name}
        </Typography>
      </DialogTitle>

      <DialogContent sx={{ px: 3, pt: '20px !important', pb: 3 }}>
        <Box component="dl" sx={{ m: 0 }}>
          <DetailRow label="Type" value={WALLET_TYPE_LABELS[wallet.type]} />
          <DetailRow label="Currency" value={wallet.currency} />
          <DetailRow
            label="Balance"
            value={formatBalance(wallet.balanceAmount, wallet.currency)}
          />
          <DetailRow label="Created" value={formatDateTime(wallet.createdAt)} />
          <DetailRow
            label="Last updated"
            value={formatDateTime(wallet.updatedAt)}
          />
        </Box>

        <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 2.5 }}>
          <Button
            onClick={onClose}
            variant="outlined"
            sx={{
              borderRadius: '12px',
              borderColor: flowlyPalette.dashboard.border,
              color: flowlyPalette.dashboard.textSecondary,
              fontWeight: 600,
              textTransform: 'none',
              '&:hover': {
                borderColor: flowlyPalette.dashboard.textMuted,
                backgroundColor: flowlyPalette.dashboard.background,
              },
            }}
          >
            Close
          </Button>
        </Box>
      </DialogContent>
    </Dialog>
  );
};
