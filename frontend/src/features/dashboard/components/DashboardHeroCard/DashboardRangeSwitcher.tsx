import {
  KeyboardArrowDownRounded,
} from '@mui/icons-material';
import {
  Box,
  Button,
  IconButton,
  Stack,
} from '@mui/material';
import { flowlyPalette, } from '../../../../app/theme';
import type {
  DashboardRange,
} from '../../../../shared/lib';

const ranges: readonly {
  readonly value: DashboardRange;
  readonly label: string;
}[] = [
  {
    value: 'monthly',
    label: 'Monthly',
  },
  {
    value: 'quarterly',
    label: 'Quarterly',
  },
  {
    value: 'yearly',
    label: 'Yearly',
  },
];

export interface DashboardRangeSwitcherProps {
  readonly value: DashboardRange;
  readonly onChange: (value: DashboardRange) => void;
}

export const DashboardRangeSwitcher = ({
  value,
  onChange,
}: DashboardRangeSwitcherProps) => {
  return (
    <Stack
      sx={{
        alignItems: 'center',
        flexDirection: 'row',
        gap: 1,
      }}
    >
      <Stack
        sx={{
          flexDirection: 'row',
          gap: 0.25,
          p: 0.4,
          borderRadius: 999,
          backgroundColor: flowlyPalette.dashboard.borderSoft,
        }}
      >
        {ranges.map((range) => {
          const isActive = range.value === value;

          return (
            <Button
              key={range.value}
              onClick={() => {
                onChange(range.value);
              }}
              size="small"
              sx={{
                minWidth: 'auto',
                px: {
                  xs: 1.1,
                  sm: 1.5,
                },
                py: 0.7,
                borderRadius: 999,
                backgroundColor: isActive
                  ? flowlyPalette.dashboard.surface
                  : 'transparent',
                boxShadow: isActive
                  ? '0 1px 3px rgba(15, 23, 42, 0.10)'
                  : 'none',
                color: isActive
                  ? flowlyPalette.dashboard.textPrimary
                  : flowlyPalette.dashboard.textSecondary,
                fontSize: '0.75rem',
                fontWeight: 750,
                '&:hover': {
                  backgroundColor: flowlyPalette.dashboard.surface,
                },
              }}
            >
              {range.label}
            </Button>
          );
        })}
      </Stack>

      <Box>
        <IconButton
          aria-label="Select custom period"
          size="small"
          sx={{
            color: flowlyPalette.dashboard.textSecondary,
          }}
        >
          < KeyboardArrowDownRounded />
        </IconButton>
      </Box>
    </Stack>
  );
};
