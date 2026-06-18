import { ChevronLeftRounded, ChevronRightRounded } from '@mui/icons-material';
import { Box, IconButton, Stack, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { TransactionPagination as Pagination } from '../types';

export interface TransactionsPaginationProps {
  readonly pagination: Pagination;
  readonly onPageChange: (page: number) => void;
}

export const TransactionsPagination = ({
  pagination,
  onPageChange,
}: TransactionsPaginationProps) => {
  const { page, totalPages, totalItems } = pagination;

  const navButtonSx = {
    width: 36,
    height: 36,
    border: `1px solid ${flowlyPalette.dashboard.border}`,
    borderRadius: '10px',
    color: flowlyPalette.dashboard.textSecondary,
    '&:hover': {
      backgroundColor: flowlyPalette.dashboard.background,
      color: flowlyPalette.dashboard.textPrimary,
    },
    '&.Mui-disabled': {
      color: flowlyPalette.dashboard.textMuted,
      opacity: 0.5,
    },
  } as const;

  return (
    <Box
      sx={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        flexWrap: 'wrap',
        gap: 1.5,
        px: 3,
        py: 2,
        borderTop: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
      }}
    >
      <Typography
        sx={{
          color: flowlyPalette.dashboard.textSecondary,
          fontSize: '0.8rem',
        }}
      >
        Page {totalPages === 0 ? 0 : page} of {totalPages} &middot; {totalItems}{' '}
        {totalItems === 1 ? 'transaction' : 'transactions'}
      </Typography>

      <Stack sx={{ alignItems: 'center', flexDirection: 'row', gap: 1 }}>
        <IconButton
          aria-label="Previous page"
          disabled={page <= 1}
          onClick={() => { onPageChange(page - 1); }}
          sx={navButtonSx}
        >
          <ChevronLeftRounded />
        </IconButton>

        <IconButton
          aria-label="Next page"
          disabled={page >= totalPages}
          onClick={() => { onPageChange(page + 1); }}
          sx={navButtonSx}
        >
          <ChevronRightRounded />
        </IconButton>
      </Stack>
    </Box>
  );
};
