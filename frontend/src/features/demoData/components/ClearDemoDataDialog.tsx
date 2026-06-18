import { DeleteForeverRounded } from '@mui/icons-material';
import {
  Box,
  Button,
  CircularProgress,
  Dialog,
  DialogContent,
  DialogTitle,
  Typography,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';

export interface ClearDemoDataDialogProps {
  readonly open: boolean;
  readonly isPending: boolean;
  readonly onClose: () => void;
  readonly onConfirm: () => void;
}

export const ClearDemoDataDialog = ({
  open,
  isPending,
  onClose,
  onConfirm,
}: ClearDemoDataDialogProps) => {
  const handleClose = () => {
    if (isPending) return;
    onClose();
  };

  return (
    <Dialog
      maxWidth="xs"
      onClose={handleClose}
      open={open}
      slotProps={{
        paper: {
          sx: {
            borderRadius: '20px',
            width: '100%',
          },
        },
      }}
    >
      <DialogTitle
        sx={{
          pb: 0,
          pt: 3,
          px: 3,
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: '1.1rem',
          fontWeight: 800,
          letterSpacing: '-0.02em',
        }}
      >
        Clear demo data?
      </DialogTitle>

      <DialogContent sx={{ px: 3, pt: '16px !important', pb: 3 }}>
        <Typography
          sx={{
            mb: 3,
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.88rem',
            lineHeight: 1.6,
          }}
        >
          This will remove only generated demo data. Your real data will not be
          removed.
        </Typography>

        <Box
          sx={{
            display: 'flex',
            gap: 1.5,
            justifyContent: 'flex-end',
          }}
        >
          <Button
            disabled={isPending}
            onClick={handleClose}
            variant="outlined"
            sx={{
              borderRadius: '12px',
              borderColor: flowlyPalette.dashboard.border,
              color: flowlyPalette.dashboard.textSecondary,
              fontWeight: 600,
              textTransform: 'none',
              '&:hover': {
                borderColor: flowlyPalette.dashboard.textMuted,
                backgroundColor: flowlyPalette.dashboard.background,
              },
            }}
          >
            Cancel
          </Button>

          <Button
            aria-busy={isPending}
            disabled={isPending}
            onClick={onConfirm}
            startIcon={
              isPending
                ? <CircularProgress size={15} sx={{ color: '#fff' }} />
                : <DeleteForeverRounded sx={{ fontSize: 17 }} />
            }
            variant="contained"
            sx={{
              borderRadius: '12px',
              backgroundColor: '#E11D48',
              boxShadow: 'none',
              fontWeight: 700,
              textTransform: 'none',
              '&:hover': {
                backgroundColor: '#BE123C',
                boxShadow: 'none',
              },
              '&.Mui-disabled': {
                backgroundColor: '#FECDD3',
                color: '#FDA4AF',
              },
            }}
          >
            {isPending ? 'Clearing...' : 'Clear demo data'}
          </Button>
        </Box>
      </DialogContent>
    </Dialog>
  );
};
