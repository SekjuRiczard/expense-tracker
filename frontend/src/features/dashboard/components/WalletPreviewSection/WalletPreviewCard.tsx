import {
  Box,
  Paper,
  Stack,
  Typography,
} from '@mui/material';
import { flowlyPalette, } from '../../../../app/theme';
import { formatCurrency, } from '../../../../shared/lib';
import type {
  Wallet,
  WalletType,
} from '../../../wallets';

const walletStyleMap: Record<WalletType, {
  readonly accent: string;
  readonly background: string;
}> = {
  bank_account: {
    accent: flowlyPalette.dashboard.indigo,
    background: `linear-gradient(135deg, ${flowlyPalette.dashboard.indigoSoft} 0%, #FFFFFF 100%)`,
  },
  savings_account: {
    accent: flowlyPalette.dashboard.emerald,
    background: `linear-gradient(135deg, ${flowlyPalette.dashboard.emeraldSoft} 0%, #FFFFFF 100%)`,
  },
  cash: {
    accent: flowlyPalette.dashboard.amber,
    background: `linear-gradient(135deg, ${flowlyPalette.dashboard.amberSoft} 0%, #FFFFFF 100%)`,
  },
  credit_card: {
    accent: flowlyPalette.dashboard.rose,
    background: `linear-gradient(135deg, ${flowlyPalette.dashboard.roseSoft} 0%, #FFFFFF 100%)`,
  },
};

export interface WalletPreviewCardProps {
  readonly wallet: Wallet;
}

export const WalletPreviewCard = ({
  wallet,
}: WalletPreviewCardProps) => {
  const style = walletStyleMap[wallet.type];

  return (
    <Paper
      elevation={0}
      sx={{
        p: 2.25,
        border: `1px solid ${flowlyPalette.dashboard.border}`,
        borderRadius: 3,
        background: style.background,
      }}
    >
      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'row',
          gap: 0.9,
        }}
      >
        <Box
          sx={{
            width: 10,
            height: 10,
            borderRadius: '50%',
            backgroundColor: style.accent,
          }}
        />

        <Typography
          sx={{
            color: flowlyPalette.dashboard.textPrimary,
            fontSize: '0.95rem',
            fontWeight: 850,
          }}
        >
          {wallet.name}
        </Typography>
      </Stack>

      <Stack
        sx={{
          flexDirection: 'row',
          gap: 3,
          mt: 2.25,
        }}
      >
        <Box>
          <Typography
            sx={{
              color: flowlyPalette.dashboard.textMuted,
              fontSize: '0.72rem',
              fontWeight: 700,
            }}
          >
            Balance
          </Typography>

          <Typography
            sx={{
              mt: 0.45,
              color: flowlyPalette.dashboard.textPrimary,
              fontSize: '1.05rem',
              fontWeight: 900,
              fontVariantNumeric: 'tabular-nums',
            }}
          >
            {formatCurrency(
              wallet.balanceAmount,
              wallet.currency,
            )}
          </Typography>
        </Box>

        <Box>
          <Typography
            sx={{
              color: flowlyPalette.dashboard.textMuted,
              fontSize: '0.72rem',
              fontWeight: 700,
            }}
          >
            Available
          </Typography>

          <Typography
            sx={{
              mt: 0.45,
              color: flowlyPalette.dashboard.textPrimary,
              fontSize: '1.05rem',
              fontWeight: 900,
              fontVariantNumeric: 'tabular-nums',
            }}
          >
            {formatCurrency(
              wallet.balanceAmount,
              wallet.currency,
            )}
          </Typography>
        </Box>
      </Stack>
    </Paper>
  );
};
