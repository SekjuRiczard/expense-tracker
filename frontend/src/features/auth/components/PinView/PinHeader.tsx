import {
  CheckRounded,
  ShieldOutlined,
} from '@mui/icons-material';
import {
  Box,
  Stack,
  Typography,
} from '@mui/material';
import {
  motion,
} from 'motion/react';
import { flowlyPalette, } from '../../../../app/theme';

const MotionBox = motion.create(Box);

export type PinHeaderVariant =
  | 'shield'
  | 'check';

export interface PinHeaderProps {
  readonly variant: PinHeaderVariant;
  readonly title: string;
  readonly description: string;
}

export const PinHeader = ({
  variant,
  title,
  description,
}: PinHeaderProps) => {
  return (
  <Box
    sx={{
      display: 'flex',
      width: '100%',
      justifyContent: 'center',
    }}
  >
    <Stack
      alignItems="center"
      spacing={1.3}
      sx={{
        width: {
          xs: 280,
          sm: 352,
        },
      }}
    >
      <MotionBox
        key={variant}
        initial={{
          opacity: 0,
          scale: 0.45,
        }}
        animate={{
          opacity: 1,
          scale: 1,
        }}
        transition={{
          type: 'spring',
          stiffness: 320,
          damping: 18,
        }}
        sx={{
    display: 'grid',
    width: 76,
    height: 76,
    placeItems: 'center',
    alignSelf: 'center',
    mx: 'auto',
    borderRadius: '50%',
    backgroundColor: '#EEF3FF',
    color: flowlyPalette.auth.focus,
  }}
      >
        {variant === 'check'
          ? (
            <CheckRounded
              sx={{
                fontSize: 40,
              }}
            />
          )
          : (
            <ShieldOutlined
              sx={{
                fontSize: 40,
              }}
            />
          )}
      </MotionBox>

      <Stack
        alignItems="center"
        spacing={0.65}
        sx={{
          width: '100%',
        }}
      >
        <Typography
          component="h1"
          sx={{
            width: '100%',
            color: flowlyPalette.auth.textPrimary,
            fontSize: 'clamp(1.9rem, 4vw, 2.3rem)',
            fontWeight: 850,
            letterSpacing: '-0.052em',
            lineHeight: 1.15,
            textAlign: 'center',
          }}
        >
          {title}
        </Typography>

        <Typography
          sx={{
            width: '100%',
            color: flowlyPalette.auth.textSecondary,
            fontSize: '0.975rem',
            lineHeight: 1.65,
            textAlign: 'center',
          }}
        >
          {description}
        </Typography>
      </Stack>
    </Stack>
  </Box>
);
};