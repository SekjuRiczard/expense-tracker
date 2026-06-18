import {
  Stack,
  Typography,
} from '@mui/material';
import logoUrl from '../../../../assets/logo.png';
import { flowlyPalette, } from '../../../../app/theme';

export const AuthBrand = () => {
  return (
    <Stack
      sx={{
        alignItems: 'center',
        flexDirection: 'row',
        gap: 1.25,
        width: 'fit-content',
      }}
    >
      <img
        src={logoUrl}
        alt="Flowly logo"
        style={{
          display: 'block',
          width: 'clamp(56px, 7vw, 160px)',
          height: 'clamp(56px, 7vw, 160px)',
          objectFit: 'contain',
        }}
      />

      <Typography
        sx={{
          color: flowlyPalette.auth.textPrimary,
          fontSize: 'clamp(1.25rem, 2vw, 1.6rem)',
          fontWeight: 850,
          letterSpacing: '-0.045em',
        }}
      >
      </Typography>
    </Stack>
  );
};