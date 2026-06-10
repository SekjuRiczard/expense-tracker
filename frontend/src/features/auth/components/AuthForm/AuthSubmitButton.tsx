import {
  ArrowForwardRounded,
} from '@mui/icons-material';
import {
  Button,
  CircularProgress,
} from '@mui/material';
import { flowlyPalette, } from '../../../../app/theme';

export interface AuthSubmitButtonProps {
  readonly label: string;
  readonly isSubmitting: boolean;
}

export const AuthSubmitButton = ({
  label,
  isSubmitting,
}: AuthSubmitButtonProps) => {
  return (
    <Button
      disabled={isSubmitting}
      endIcon={
        isSubmitting
          ? undefined
          : <ArrowForwardRounded />
      }
      fullWidth
      type="submit"
      variant="contained"
      sx={{
        minHeight: 54,
        borderRadius: 2.5,
        background: `linear-gradient(
          135deg,
          ${flowlyPalette.auth.buttonGradientStart} 0%,
          ${flowlyPalette.auth.buttonGradientEnd} 100%
        )`,
        boxShadow: '0 10px 22px rgba(52, 72, 197, 0.22)',
        fontSize: '0.975rem',
        fontWeight: 750,
        textTransform: 'none',
        transition: 'transform 180ms ease, box-shadow 180ms ease',

        '&:hover': {
          boxShadow: '0 14px 28px rgba(52, 72, 197, 0.28)',
          transform: 'translateY(-1px)',
        },

        '&.Mui-disabled': {
          color: flowlyPalette.auth.panelTextPrimary,
          background: 'linear-gradient(135deg, #98A2D9 0%, #ABB3EA 100%)',
        },
      }}
    >
      {isSubmitting
        ? (
          <CircularProgress
            color="inherit"
            size={22}
          />
        )
        : label}
    </Button>
  );
};