import { AddRounded } from '@mui/icons-material';
import { Box, ButtonBase, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';

export interface CreateWalletCardProps {
  readonly onClick: () => void;
}

export const CreateWalletCard = ({ onClick }: CreateWalletCardProps) => {
  return (
    <ButtonBase
      focusRipple
      onClick={onClick}
      sx={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 1.5,
        p: 3,
        minHeight: 220,
        width: '100%',
        border: `2px dashed ${flowlyPalette.dashboard.border}`,
        borderRadius: '20px',
        backgroundColor: 'transparent',
        color: flowlyPalette.dashboard.textSecondary,
        cursor: 'pointer',
        transition: 'border-color 200ms ease, color 200ms ease',
        '&:hover': {
          borderColor: flowlyPalette.dashboard.indigo,
          color: flowlyPalette.dashboard.indigoDark,
        },
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
          borderRadius: '999px',
          backgroundColor: '#F1F5F9',
        }}
      >
        <AddRounded sx={{ fontSize: 22 }} />
      </Box>

      <Typography
        sx={{
          fontSize: '0.88rem',
          fontWeight: 600,
          color: 'inherit',
        }}
      >
        Create new wallet
      </Typography>
    </ButtonBase>
  );
};
