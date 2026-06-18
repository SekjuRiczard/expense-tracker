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

export const LiveUpdateBadge = () => {
  return (
    <Stack
      sx={{
        alignItems: 'center',
        flexDirection: 'row',
        justifyContent: 'center',
        gap: 0.9,
        width: 'fit-content',
        minHeight: 34,
        px: 1.45,
        py: 0.7,
        border: `1px solid ${flowlyPalette.auth.glassBorder}`,
        borderRadius: 10,
        backgroundColor: flowlyPalette.auth.glassBackground,
        backdropFilter: 'blur(12px)',
      }}
    >
      <Box
        sx={{
          display: 'grid',
          width: 10,
          height: 10,
          flexShrink: 0,
          placeItems: 'center',
        }}
      >
        <MotionBox
          animate={{
            opacity: [1, 0.45, 1,],
            scale: [1, 1.22, 1,],
          }}
          transition={{
            duration: 1.7,
            ease: 'easeInOut',
            repeat: Number.POSITIVE_INFINITY,
          }}
          sx={{
            width: 8,
            height: 8,
            borderRadius: '50%',
            backgroundColor: flowlyPalette.auth.success,
            boxShadow: `0 0 12px ${flowlyPalette.auth.success}`,
          }}
        />
      </Box>

      <Typography
        sx={{
          color: flowlyPalette.auth.panelTextSecondary,
          fontSize: '0.73rem',
          fontWeight: 700,
          lineHeight: 1,
          whiteSpace: 'nowrap',
        }}
      >
        NBP rates · checked every minute
      </Typography>
    </Stack>
  );
};