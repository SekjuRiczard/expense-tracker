import {
  Alert,
  Box,
  Button,
  Skeleton,
  Stack,
  Typography,
} from '@mui/material';
import {
  motion,
} from 'motion/react';
import { flowlyPalette, } from '../../../../app/theme';
import {
  useNbpCurrencyRates,
} from '../../../currency';
import { CurrencyCard, } from './CurrencyCard';
import { LiveUpdateBadge, } from './LiveUpdateBadge';

const MotionBox = motion.create(Box);

const CurrencyCardSkeleton = () => {
  return (
    <Skeleton
      animation="wave"
      variant="rounded"
      sx={{
        minHeight: {
          md: 182,
          lg: 206,
          xl: 230,
        },
        borderRadius: '20px',
        backgroundColor: 'rgba(255, 255, 255, 0.08)',
      }}
    />
  );
};

export const CurrencyPanel = () => {
  const {
    data,
    isError,
    isPending,
    refetch,
  } = useNbpCurrencyRates();

  return (
    <Box
      sx={{
        display: 'flex',
        width: '100%',
        maxWidth: 790,
        minHeight: 'calc(100dvh - 80px)',
        flexDirection: 'column',
      }}
    >
      <Stack
        justifyContent="center"
        spacing={{
          md: 1.4,
          lg: 1.8,
        }}
        sx={{
          flex: 1,
        }}
      >
        {isPending && (
          <>
            <CurrencyCardSkeleton />
            <CurrencyCardSkeleton />
            <CurrencyCardSkeleton />
          </>
        )}

        {isError && (
          <Stack spacing={1.5}>
            <Alert severity="error">
              NBP exchange rates could not be loaded.
            </Alert>

            <Button
              onClick={() => {
                void refetch();
              }}
              variant="outlined"
              sx={{
                width: 'fit-content',
                borderColor: flowlyPalette.auth.glassBorder,
                color: flowlyPalette.auth.panelTextPrimary,
                textTransform: 'none',

                '&:hover': {
                  borderColor: flowlyPalette.auth.panelTextSecondary,
                },
              }}
            >
              Try again
            </Button>
          </Stack>
        )}

        {!isPending && !isError && data.map((
          currencyRate,
          index,
        ) => (
          <MotionBox
            key={currencyRate.code}
            initial={{
              opacity: 0,
              y: 24,
            }}
            animate={{
              opacity: 1,
              y: 0,
            }}
            transition={{
              duration: 0.42,
              delay: index * 0.12,
              ease: 'easeOut',
            }}
          >
            <CurrencyCard
              currencyRate={currencyRate}
            />
          </MotionBox>
        ))}

        {!isPending && !isError && (
  <Box
    sx={{
      display: 'flex',
      width: '100%',
      justifyContent: 'center',
      pt: 0.3,
    }}
  >
    <LiveUpdateBadge />
  </Box>
)}
      </Stack>

     <Box
  component="footer"
  sx={{
    display: 'flex',
    width: '100%',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 2,
    pt: 2.5,
    borderTop: `1px solid ${flowlyPalette.auth.glassBorder}`,
  }}
>
  <Typography
    sx={{
      color: 'rgba(255, 255, 255, 0.42)',
      fontSize: '0.69rem',
      lineHeight: 1.4,
      whiteSpace: 'nowrap',
    }}
  >
    All Rights Reserved
  </Typography>

  <Typography
    component="a"
    href="#"
    sx={{
      color: 'rgba(255, 255, 255, 0.55)',
      fontSize: '0.69rem',
      lineHeight: 1.4,
      textDecoration: 'none',
      whiteSpace: 'nowrap',
      transition: 'color 160ms ease',

      '&:hover': {
        color: flowlyPalette.auth.panelTextPrimary,
      },
    }}
  >
    Privacy Policy
  </Typography>
</Box>
    </Box>
  );
};