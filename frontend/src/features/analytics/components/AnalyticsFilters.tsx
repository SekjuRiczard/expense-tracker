import {
  Box,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { AnalyticsCurrency } from '../types';

export interface AnalyticsFiltersProps {
  readonly from: string;
  readonly to: string;
  readonly currency: AnalyticsCurrency;
  readonly invalidRange: boolean;
  readonly onFromChange: (value: string) => void;
  readonly onToChange: (value: string) => void;
  readonly onCurrencyChange: (value: AnalyticsCurrency) => void;
}

const dateFieldSx = {
  '& .MuiOutlinedInput-root': {
    height: 40,
    borderRadius: '10px',
    backgroundColor: flowlyPalette.dashboard.background,
    fontSize: '0.82rem',
  },
} as const;

export const AnalyticsFilters = ({
  from,
  to,
  currency,
  invalidRange,
  onFromChange,
  onToChange,
  onCurrencyChange,
}: AnalyticsFiltersProps) => {
  return (
    <Box
      sx={{
        mb: 3,
        p: 2,
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: '16px',
        backgroundColor: flowlyPalette.dashboard.surface,
      }}
    >
      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'row',
          flexWrap: 'wrap',
          gap: 2,
        }}
      >
        <Typography
          sx={{
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.75rem',
            fontWeight: 600,
          }}
        >
          Period:
        </Typography>

        <TextField
          label="From"
          onChange={(e) => { onFromChange(e.target.value); }}
          size="small"
          slotProps={{ inputLabel: { shrink: true } }}
          sx={dateFieldSx}
          type="date"
          value={from}
        />

        <Typography sx={{ color: flowlyPalette.dashboard.textMuted }}>
          &mdash;
        </Typography>

        <TextField
          error={invalidRange}
          helperText={invalidRange ? 'From must be before To' : undefined}
          label="To"
          onChange={(e) => { onToChange(e.target.value); }}
          size="small"
          slotProps={{ inputLabel: { shrink: true } }}
          sx={dateFieldSx}
          type="date"
          value={to}
        />

        <FormControl
          size="small"
          sx={{ minWidth: 120, ml: { lg: 'auto' } }}
        >
          <InputLabel id="analytics-currency-label">Currency</InputLabel>
          <Select
            label="Currency"
            labelId="analytics-currency-label"
            onChange={(e) => {
              onCurrencyChange(e.target.value as AnalyticsCurrency);
            }}
            value={currency}
            sx={{
              height: 40,
              borderRadius: '10px',
              backgroundColor: flowlyPalette.dashboard.background,
              fontSize: '0.82rem',
            }}
          >
            <MenuItem value="PLN">PLN</MenuItem>
            <MenuItem value="EUR">EUR</MenuItem>
            <MenuItem value="USD">USD</MenuItem>
          </Select>
        </FormControl>
      </Stack>
    </Box>
  );
};
