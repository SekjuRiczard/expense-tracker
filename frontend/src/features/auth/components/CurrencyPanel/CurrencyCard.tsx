import {
  TrendingDownRounded,
  TrendingFlatRounded,
  TrendingUpRounded,
} from '@mui/icons-material';
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
}

export const CurrencyCard = ({
  currencyRate,
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

  return (
    <Box
      sx={{
        position: 'relative',
        width: '100%',
        minHeight: {
          md: 182,
          lg: 206,
          xl: 230,
        },
        overflow: 'hidden',
        border: '1px solid rgba(255, 255, 255, 0.12)',
        borderRadius: '20px',
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
          top: {
            md: 18,
            lg: 22,
          },
          left: {
            md: 18,
            lg: 21,
          },
        }}
      >
        <Typography
          aria-hidden="true"
          sx={{
            color: 'rgba(255, 255, 255, 0.94)',
            fontSize: {
              md: '1.65rem',
              lg: '1.9rem',
            },
            fontWeight: 800,
            lineHeight: 1,
          }}
        >
          {currencyRate.symbol}
        </Typography>

        <Typography
          sx={{
            mt: 1,
            color: 'rgba(255, 255, 255, 0.5)',
            fontSize: '0.82rem',
            fontWeight: 750,
          }}
        >
          {currencyRate.pair}
        </Typography>

        <Typography
          sx={{
            mt: {
              md: 1.5,
              lg: 2.1,
            },
            color: '#FFFFFF',
            fontSize: {
              md: '1.6rem',
              lg: '1.9rem',
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
            mt: 1,
            color: 'rgba(255, 255, 255, 0.4)',
            fontSize: '0.72rem',
            fontWeight: 650,
          }}
        >
          PLN per {currencyRate.code}
        </Typography>
      </Box>

      <Stack
        alignItems="center"
        direction="row"
        spacing={0.45}
        sx={{
          position: 'absolute',
          top: {
            md: 17,
            lg: 21,
          },
          right: {
            md: 18,
            lg: 21,
          },
          color: trendColor,
        }}
      >
        <TrendIcon
          sx={{
            fontSize: 18,
          }}
        />

        <Typography
          sx={{
            color: 'inherit',
            fontSize: '0.84rem',
            fontWeight: 850,
          }}
        >
          {currencyRate.formattedChange}
        </Typography>
      </Stack>

      <Box
        sx={{
          position: 'absolute',
          bottom: {
            md: 14,
            lg: 17,
          },
          left: {
            md: 18,
            lg: 21,
          },
          display: 'flex',
          alignItems: 'flex-end',
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
          right: {
            md: 18,
            lg: 21,
          },
          bottom: {
            md: 17,
            lg: 20,
          },
          color: 'rgba(255, 255, 255, 0.48)',
          fontSize: '0.72rem',
          fontWeight: 650,
        }}
      >
        7 days
      </Typography>
    </Box>
  );
};