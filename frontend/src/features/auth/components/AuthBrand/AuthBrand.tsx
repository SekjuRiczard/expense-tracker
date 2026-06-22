import {
  Stack,
} from '@mui/material';
import logoUrl from '../../../../assets/logo.png';

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
        width={160}
        height={56}
        style={{
          display: 'block',
          width: 'clamp(56px, 7vw, 160px)',
          height: 'auto',
          objectFit: 'contain',
        }}
      />
    </Stack>
  );
};
