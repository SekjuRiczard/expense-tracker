import { Box, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';

export interface ProfileRowProps {
  readonly label: string;
  readonly value: string;
}

export const ProfileRow = ({ label, value }: ProfileRowProps) => {
  return (
    <Box
      component="div"
      sx={{
        display: 'flex',
        flexDirection: { xs: 'column', sm: 'row' },
        justifyContent: 'space-between',
        alignItems: { xs: 'flex-start', sm: 'center' },
        gap: { xs: 0.25, sm: 1 },
        py: 1.5,
        borderBottom: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        '&:last-child': {
          borderBottom: 'none',
          pb: 0,
        },
        '&:first-of-type': {
          pt: 0,
        },
      }}
    >
      <Typography
        component="dt"
        sx={{
          color: flowlyPalette.dashboard.textSecondary,
          fontSize: '0.82rem',
          fontWeight: 500,
          flexShrink: 0,
        }}
      >
        {label}
      </Typography>

      <Typography
        component="dd"
        sx={{
          m: 0,
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: '0.85rem',
          fontWeight: 600,
          textAlign: { xs: 'left', sm: 'right' },
        }}
      >
        {value}
      </Typography>
    </Box>
  );
};
