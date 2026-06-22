import TrendingDownRounded from '@mui/icons-material/TrendingDownRounded';
import TrendingFlatRounded from '@mui/icons-material/TrendingFlatRounded';
import TrendingUpRounded from '@mui/icons-material/TrendingUpRounded';
import {
  Box,
  Stack,
  Typography,
} from '@mui/material';
import { flowlyPalette, } from '../../../../app/theme';
import { Sparkline, } from '../../../../shared/ui/SparkLine';
import type {
  CurrencyRate,
} from '../../../currency';

export interface CurrencyCardProps {
  readonly currencyRate: CurrencyRate;
  readonly compact?: boolean;
}

export const CurrencyCard = ({
  currencyRate,
  compact = false,
}: CurrencyCardProps) => {
  const trendColor = currencyRate.trend === 'down'
    ? flowlyPalette.auth.error
    : currencyRate.trend === 'up'
      ? '#00D89C'
      : flowlyPalette.auth.panelTextSecondary;

  const TrendIcon = currencyRate.trend === 'down'
    ? TrendingDownRounded
    : currencyRate.trend === 'up'
      ? TrendingUpRounded
      : TrendingFlatRounded;

  const inset = compact
    ? 12
    : {
      md: 14,
      lg: 18,
      xl: 21,
    };

  return (
    <Box
      sx={{
        position: 'relative',
        width: '100%',
        flex: 1,
        minHeight: 0,
        height: compact
          ? 'clamp(108px, 18dvh, 150px)'
          : 'clamp(120px, 22dvh, 206px)',
        overflow: 'hidden',
        border: '1px solid rgba(255, 255, 255, 0.12)',
        borderRadius: compact ? '16px' : '20px',
        background: `
          linear-gradient(
            105deg,
            rgba(91, 96, 156, 0.5) 0%,
            rgba(58, 64, 91, 0.72) 48%,
            rgba(43, 50, 64, 0.9) 100%
          )
        `,
        boxShadow: '0 18px 38px rgba(0, 0, 0, 0.12)',
        backdropFilter: 'blur(18px)',
      }}
    >
      <Box
        sx={{
          position: 'absolute',
          top: inset,
          left: inset,
        }}
      >
        <Typography
          aria-hidden="true"
          sx={{
            color: 'rgba(255, 255, 255, 0.94)',
            fontSize: compact
              ? '1.25rem'
              : {
                md: '1.35rem',
                lg: '1.6rem',
                xl: '1.9rem',
              },
            fontWeight: 800,
            lineHeight: 1,
          }}
        >
          {currencyRate.symbol}
        </Typography>

        <Typography
          sx={{
            mt: compact ? 0.5 : 0.75,
            color: 'rgba(255, 255, 255, 0.5)',
            fontSize: compact ? '0.72rem' : '0.82rem',
            fontWeight: 750,
          }}
        >
          {currencyRate.pair}
        </Typography>

        <Typography
          sx={{
            mt: compact
              ? 0.75
              : {
                md: 1,
                lg: 1.5,
                xl: 2.1,
              },
            color: '#FFFFFF',
            fontSize: compact
              ? '1.2rem'
              : {
                md: '1.35rem',
                lg: '1.6rem',
                xl: '1.9rem',
              },
            fontWeight: 850,
            letterSpacing: '-0.035em',
            lineHeight: 1,
          }}
        >
          {currencyRate.formattedRate}
        </Typography>

        <Typography
          sx={{
            mt: compact ? 0.35 : 0.75,
            color: 'rgba(255, 255, 255, 0.4)',
            fontSize: compact ? '0.65rem' : '0.72rem',
            fontWeight: 650,
          }}
        >
          PLN per {currencyRate.code}
        </Typography>
      </Box>

      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'row',
          gap: 0.45,
          position: 'absolute',
          top: inset,
          right: inset,
          color: trendColor,
        }}
      >
        <TrendIcon
          sx={{
            fontSize: compact ? 16 : 18,
          }}
        />

        <Typography
          sx={{
            color: 'inherit',
            fontSize: compact ? '0.76rem' : '0.84rem',
            fontWeight: 850,
          }}
        >
          {currencyRate.formattedChange}
        </Typography>
      </Stack>

      <Box
        sx={{
          position: 'absolute',
          bottom: inset,
          left: inset,
          display: 'flex',
          alignItems: 'flex-end',
          transform: compact ? 'scale(0.85)' : 'none',
          transformOrigin: 'bottom left',
        }}
      >
        <Sparkline
          ariaLabel={`${currencyRate.pair} exchange rate chart for the last 7 publications`}
          color={trendColor}
          values={currencyRate.history}
        />
      </Box>

      <Typography
        sx={{
          position: 'absolute',
          right: inset,
          bottom: inset,
          color: 'rgba(255, 255, 255, 0.48)',
          fontSize: compact ? '0.65rem' : '0.72rem',
          fontWeight: 650,
        }}
      >
        7 days
      </Typography>
    </Box>
  );
};
