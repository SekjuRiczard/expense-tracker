import {
  AutoAwesomeRounded,
  DeleteForeverRounded,
} from '@mui/icons-material';
import {
  Alert,
  Box,
  Button,
  CircularProgress,
  Paper,
  Typography,
} from '@mui/material';
import { useState } from 'react';
import { flowlyPalette } from '../../../app/theme';
import {
  useClearDemoData,
  useDemoDataStatus,
  useGenerateDemoData,
} from '../hooks';
import { ClearDemoDataDialog } from './ClearDemoDataDialog';

export interface DemoDataActionsProps {
  readonly showToast: (message: string, severity: 'success' | 'error') => void;
  readonly enabled?: boolean;
}

export const DemoDataActions = ({
  showToast,
  enabled = true,
}: DemoDataActionsProps) => {
  const [warningMessage, setWarningMessage] = useState<string | null>(null);
  const [clearDialogOpen, setClearDialogOpen] = useState(false);

  const statusQuery = useDemoDataStatus({ enabled });
  const demoDataExists = statusQuery.data?.demoDataExists ?? false;

  const generateMutation = useGenerateDemoData({
    onSuccess: () => {
      setWarningMessage(null);
      showToast('Demo data has been generated.', 'success');
    },
    onDataExists: () => {
      setWarningMessage(
        'Demo data has already been generated. Clear it before generating again.',
      );
    },
    onForbidden: () => {
      showToast('You do not have permission to manage demo data.', 'error');
    },
    onError: () => {
      showToast('Something went wrong. Please try again.', 'error');
    },
  });

  const clearMutation = useClearDemoData({
    onSuccess: () => {
      setWarningMessage(null);
      setClearDialogOpen(false);
      showToast('Demo data has been cleared.', 'success');
    },
    onForbidden: () => {
      setClearDialogOpen(false);
      showToast('You do not have permission to manage demo data.', 'error');
    },
    onError: () => {
      setClearDialogOpen(false);
      showToast('Something went wrong. Please try again.', 'error');
    },
  });

  const isBusy =
    generateMutation.isPending ||
    clearMutation.isPending ||
    statusQuery.isPending;

  return (
    <>
      <Paper
        component="section"
        elevation={0}
        sx={{
          p: { xs: 2.5, sm: 3 },
          border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
          borderRadius: '20px',
          backgroundColor: flowlyPalette.dashboard.surface,
        }}
      >
        <Box
          sx={{
            display: 'flex',
            alignItems: 'flex-start',
            gap: 2,
            mb: 2,
          }}
        >
          <Box
            aria-hidden="true"
            sx={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              width: 40,
              height: 40,
              borderRadius: '10px',
              backgroundColor: '#F5F3FF',
              flexShrink: 0,
            }}
          >
            <AutoAwesomeRounded
              sx={{ color: '#7C3AED', fontSize: 20 }}
            />
          </Box>

          <Box>
            <Typography
              component="h2"
              sx={{
                color: flowlyPalette.dashboard.textPrimary,
                fontSize: '1rem',
                fontWeight: 800,
                letterSpacing: '-0.02em',
                lineHeight: 1.25,
              }}
            >
              Demo data
            </Typography>

            <Typography
              sx={{
                mt: 0.25,
                color: flowlyPalette.dashboard.textSecondary,
                fontSize: '0.8rem',
              }}
            >
              Generate sample wallets, budgets and transactions for testing the
              dashboard.
            </Typography>
          </Box>
        </Box>

        {warningMessage && (
          <Alert
            severity="warning"
            sx={{ mb: 2, borderRadius: '12px', fontSize: '0.82rem' }}
          >
            {warningMessage}
          </Alert>
        )}

        <Box
          sx={{
            display: 'flex',
            flexWrap: 'wrap',
            gap: 1.5,
          }}
        >
          <Button
            aria-busy={generateMutation.isPending}
            disabled={isBusy || demoDataExists}
            onClick={() => { generateMutation.mutate(); }}
            startIcon={
              generateMutation.isPending
                ? (
                    <CircularProgress
                      size={15}
                      sx={{ color: 'inherit' }}
                    />
                  )
                : <AutoAwesomeRounded sx={{ fontSize: 17 }} />
            }
            variant="contained"
            sx={{
              borderRadius: '12px',
              background: demoDataExists
                ? flowlyPalette.dashboard.indigoSoft
                : 'linear-gradient(135deg, #7C3AED, #6D28D9)',
              color: demoDataExists
                ? flowlyPalette.dashboard.indigo
                : '#fff',
              boxShadow: 'none',
              fontWeight: 700,
              textTransform: 'none',
              '&:hover': {
                background: 'linear-gradient(135deg, #8B5CF6, #7C3AED)',
                boxShadow: 'none',
              },
              '&.Mui-disabled': {
                background: demoDataExists
                  ? flowlyPalette.dashboard.indigoSoft
                  : undefined,
                color: demoDataExists
                  ? flowlyPalette.dashboard.indigo
                  : undefined,
              },
            }}
          >
            {generateMutation.isPending
              ? 'Generating...'
              : demoDataExists
                ? 'Demo data already generated'
                : 'Generate demo data'}
          </Button>

          {demoDataExists && (
            <Button
              aria-busy={clearMutation.isPending}
              disabled={isBusy}
              onClick={() => { setClearDialogOpen(true); }}
              startIcon={
                clearMutation.isPending
                  ? (
                      <CircularProgress
                        size={15}
                        sx={{ color: '#E11D48' }}
                      />
                    )
                  : <DeleteForeverRounded sx={{ fontSize: 17 }} />
              }
              variant="outlined"
              sx={{
                borderRadius: '12px',
                borderColor: '#FECDD3',
                color: '#E11D48',
                fontWeight: 700,
                textTransform: 'none',
                '&:hover': {
                  borderColor: '#FDA4AF',
                  backgroundColor: flowlyPalette.dashboard.roseSoft,
                },
                '&.Mui-disabled': {
                  borderColor: '#FECDD3',
                  color: '#FDA4AF',
                },
              }}
            >
              {clearMutation.isPending ? 'Clearing...' : 'Clear demo data'}
            </Button>
          )}
        </Box>
      </Paper>

      <ClearDemoDataDialog
        isPending={clearMutation.isPending}
        onClose={() => { setClearDialogOpen(false); }}
        onConfirm={() => { clearMutation.mutate(); }}
        open={clearDialogOpen}
      />
    </>
  );
};
