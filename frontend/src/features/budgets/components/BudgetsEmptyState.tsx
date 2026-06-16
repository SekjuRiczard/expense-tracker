import { SavingsOutlined } from '@mui/icons-material';
import { Box, Button, Stack, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';

export interface BudgetsEmptyStateProps {
  readonly onCreateClick: () => void;
}

export const BudgetsEmptyState = ({
  onCreateClick,
}: BudgetsEmptyStateProps) => {
  return (
    <Stack
      sx={{
        alignItems: 'center',
        justifyContent: 'center',
        gap: 1.5,
        textAlign: 'center',
        py: 8,
        px: 3,
        backgroundColor: '#FFFFFF',
        border: '1px solid #F1F5F9',
        borderRadius: '20px',
      }}
    >
      <Box
        sx={{
          display: 'grid',
          placeItems: 'center',
          width: 56,
          height: 56,
          borderRadius: '16px',
          backgroundColor: flowlyPalette.dashboard.indigoSoft,
          color: flowlyPalette.dashboard.indigoDark,
        }}
      >
        <SavingsOutlined />
      </Box>

      <Typography
        sx={{
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: '1rem',
          fontWeight: 700,
        }}
      >
        No budgets found.
      </Typography>

      <Typography
        sx={{
          color: flowlyPalette.dashboard.textSecondary,
          fontSize: '0.85rem',
          maxWidth: 340,
        }}
      >
        Create your first budget to start tracking how much you spend against
        your limits.
      </Typography>

      <Button
        onClick={onCreateClick}
        variant="contained"
        sx={{
          mt: 1,
          borderRadius: '12px',
          background: 'linear-gradient(135deg, #4F46E5, #4338CA)',
          boxShadow: 'none',
          fontWeight: 700,
          textTransform: 'none',
          '&:hover': {
            background: 'linear-gradient(135deg, #6366F1, #4F46E5)',
            boxShadow: 'none',
          },
        }}
      >
        Add budget
      </Button>
    </Stack>
  );
};
