import { Box, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';

export interface AnalyticsStatCardProps {
  readonly label: string;
  readonly value: string;
  readonly valueColor?: string;
}

export const AnalyticsStatCard = ({
  label,
  value,
  valueColor,
}: AnalyticsStatCardProps) => {
  return (
    <Box
      sx={{
        p: 2.5,
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: '18px',
        backgroundColor: flowlyPalette.dashboard.surface,
      }}
    >
      <Typography
        sx={{
          color: flowlyPalette.dashboard.textSecondary,
          fontSize: '0.75rem',
          fontWeight: 500,
        }}
      >
        {label}
      </Typography>

      <Typography
        sx={{
          mt: 0.75,
          color: valueColor ?? flowlyPalette.dashboard.textPrimary,
          fontSize: '1.4rem',
          fontWeight: 700,
          fontVariantNumeric: 'tabular-nums',
          lineHeight: 1.2,
        }}
      >
        {value}
      </Typography>
    </Box>
  );
};
