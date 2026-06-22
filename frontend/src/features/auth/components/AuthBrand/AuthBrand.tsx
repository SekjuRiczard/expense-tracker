import {
  Stack,
} from '@mui/material';
import { LogoMark, } from '../../../../shared/ui/LogoMark';

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
      <LogoMark size={56} title="Flowly" />
    </Stack>
  );
};
