import {
  CheckCircleOutlined,
  CancelOutlined,
} from '@mui/icons-material';
import type { SvgIconComponent } from '@mui/icons-material';
import { Box, Button, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';

export interface SecurityRowProps {
  readonly icon: SvgIconComponent;
  readonly title: string;
  readonly isSet: boolean;
  readonly setLabel: string;
  readonly notSetLabel: string;
  readonly actionLabel: string;
  readonly onAction: () => void;
  readonly actionDisabled?: boolean;
}

export const SecurityRow = ({
  icon: Icon,
  title,
  isSet,
  setLabel,
  notSetLabel,
  actionLabel,
  onAction,
  actionDisabled = false,
}: SecurityRowProps) => {
  return (
    <Box
      sx={{
        display: 'flex',
        alignItems: 'center',
        gap: 2,
        p: 2,
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: '14px',
        backgroundColor: flowlyPalette.dashboard.background,
      }}
    >
      <Box
        aria-hidden="true"
        sx={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          width: 36,
          height: 36,
          borderRadius: '9px',
          backgroundColor: '#F8FAFC',
          border: `1px solid ${flowlyPalette.dashboard.border}`,
          flexShrink: 0,
        }}
      >
        <Icon
          sx={{
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: 18,
          }}
        />
      </Box>

      <Box sx={{ flex: 1, minWidth: 0 }}>
        <Typography
          sx={{
            color: flowlyPalette.dashboard.textPrimary,
            fontSize: '0.85rem',
            fontWeight: 700,
            lineHeight: 1.25,
          }}
        >
          {title}
        </Typography>

        <Box
          sx={{
            display: 'flex',
            alignItems: 'center',
            gap: 0.5,
            mt: 0.4,
          }}
        >
          {isSet ? (
            <CheckCircleOutlined
              sx={{ color: '#059669', fontSize: 14 }}
            />
          ) : (
            <CancelOutlined
              sx={{ color: flowlyPalette.dashboard.textMuted, fontSize: 14 }}
            />
          )}

          <Typography
            sx={{
              color: isSet ? '#059669' : flowlyPalette.dashboard.textMuted,
              fontSize: '0.75rem',
              fontWeight: 500,
            }}
          >
            {isSet ? setLabel : notSetLabel}
          </Typography>
        </Box>
      </Box>

      <Button
        disabled={actionDisabled}
        onClick={onAction}
        size="small"
        variant="outlined"
        sx={{
          flexShrink: 0,
          borderColor: flowlyPalette.dashboard.border,
          color: flowlyPalette.dashboard.textPrimary,
          borderRadius: '10px',
          fontSize: '0.78rem',
          fontWeight: 600,
          textTransform: 'none',
          whiteSpace: 'nowrap',
          '&:hover': {
            borderColor: flowlyPalette.dashboard.indigo,
            color: flowlyPalette.dashboard.indigoDark,
            backgroundColor: flowlyPalette.dashboard.indigoSoft,
          },
        }}
      >
        {actionLabel}
      </Button>
    </Box>
  );
};
