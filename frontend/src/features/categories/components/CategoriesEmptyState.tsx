import { Box, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';

export const CategoriesEmptyState = () => {
  return (
    <Box sx={{ py: 6, px: 2, textAlign: 'center' }}>
      <Typography
        sx={{
          color: flowlyPalette.dashboard.textSecondary,
          fontSize: '0.82rem',
        }}
      >
        No categories found.
      </Typography>
    </Box>
  );
};
