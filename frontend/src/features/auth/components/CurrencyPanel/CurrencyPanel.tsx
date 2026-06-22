import {
  Alert,
  Box,
  Button,
  Skeleton,
  Stack,
  Typography,
  useMediaQuery,
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

const CurrencyCardSkeleton = ({
  compact,
}: {
  readonly compact: boolean;
}) => {
  return (
    <Skeleton
      animation="wave"
      variant="rounded"
      sx={{
        flex: 1,
        minHeight: 0,
        height: compact ? 'clamp(108px, 18dvh, 150px)' : 'clamp(120px, 22dvh, 206px)',
        borderRadius: '20px',
        backgroundColor: 'rgba(255, 255, 255, 0.08)',
      }}
    />
  );
};

export const CurrencyPanel = () => {
  const compact = useMediaQuery('(max-height: 820px)');

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
        height: '100%',
        maxHeight: '100%',
        minHeight: 0,
        flexDirection: 'column',
        overflow: 'hidden',
      }}
    >
      <Stack
        sx={{
          display: 'flex',
          flex: 1,
          minHeight: 0,
          justifyContent: 'center',
          gap: compact
            ? 0.75
            : {
              md: 1,
              lg: 1.25,
              xl: 1.5,
            },
          overflow: 'hidden',
        }}
      >
        {isPending && (
          <>
            <CurrencyCardSkeleton compact={compact} />
            <CurrencyCardSkeleton compact={compact} />
            <CurrencyCardSkeleton compact={compact} />
          </>
        )}

        {isError && (
          <Stack
            sx={{
              gap: 1.5,
            }}
          >
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
            sx={{
              display: 'flex',
              flex: 1,
              minHeight: 0,
            }}
          >
            <CurrencyCard
              compact={compact}
              currencyRate={currencyRate}
            />
          </MotionBox>
        ))}

        {!isPending && !isError && (
          <Box
            sx={{
              display: 'flex',
              width: '100%',
              flexShrink: 0,
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
          flexShrink: 0,
          alignItems: 'center',
          justifyContent: 'space-between',
          gap: 2,
          pt: compact ? 1.5 : 2.5,
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
