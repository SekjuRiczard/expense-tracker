import {
  AddRounded,
} from '@mui/icons-material';
import {
  Alert,
  Box,
  Button,
  Link,
  Paper,
  Skeleton,
  Stack,
  Typography,
} from '@mui/material';
import {
  Link as RouterLink,
} from 'react-router-dom';
import { flowlyPalette, } from '../../../../app/theme';
import type {
  TransactionList,
} from '../../../transactions';
import { RecentTransactionMobileCard, } from './RecentTransactionMobileCard';
import { RecentTransactionsTable, } from './RecentTransactionsTable';

export interface RecentTransactionsSectionProps {
  readonly data?: TransactionList;
  readonly error: boolean;
  readonly loading: boolean;
}

export const RecentTransactionsSection = ({
  data,
  error,
  loading,
}: RecentTransactionsSectionProps) => {
  return (
    <Paper
      component="section"
      elevation={0}
      sx={{
        mt: 3,
        overflow: 'hidden',
        border: `1px solid ${flowlyPalette.dashboard.border}`,
        borderRadius: 3,
        backgroundColor: flowlyPalette.dashboard.surface,
      }}
    >
      <Stack
  sx={{
    flexDirection: {
      xs: 'column',
      sm: 'row',
    },
    alignItems: {
      xs: 'stretch',
      sm: 'center',
    },
    justifyContent: 'space-between',
    gap: 1.5,
    px: 2.5,
    py: 2,
    borderBottom: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
  }}
>
  <Stack
    sx={{
      alignItems: 'center',
      flexDirection: 'row',
      gap: 1.1,
    }}
  >
          <Typography
            component="h2"
            sx={{
              color: flowlyPalette.dashboard.textPrimary,
              fontSize: '1.05rem',
              fontWeight: 900,
              letterSpacing: '-0.025em',
            }}
          >
            Recent transactions
          </Typography>

          <Link
            component={RouterLink}
            to="/transactions"
            underline="hover"
            sx={{
              color: flowlyPalette.dashboard.indigoDark,
              fontSize: '0.82rem',
              fontWeight: 800,
            }}
          >
            View all ({data?.pagination.totalItems ?? 0}) ›
          </Link>
        </Stack>

        <Button
          startIcon={<AddRounded />}
          variant="contained"
          sx={{
            alignSelf: {
              xs: 'flex-start',
              sm: 'auto',
            },
            borderRadius: 2,
            backgroundColor: flowlyPalette.dashboard.action,
            fontSize: '0.8rem',
            fontWeight: 800,
            '&:hover': {
              backgroundColor: '#1E293B',
            },
          }}
        >
          New transaction
        </Button>
      </Stack>

      {error && (
        <Box
          sx={{
            p: 2,
          }}
        >
          <Alert severity="error">
            Recent transactions could not be loaded.
          </Alert>
        </Box>
      )}

      {!error && loading && (
        <Stack
          sx={{
            gap: 1,
            p: 2,
          }}
        >
          {Array.from({
            length: 5,
          }).map((_, index) => (
            <Skeleton
              key={`transaction-skeleton-${index}`}
              height={54}
              variant="rounded"
            />
          ))}
        </Stack>
      )}

      {!error && !loading && data?.items.length === 0 && (
        <Box
          sx={{
            p: 2,
          }}
        >
          <Alert severity="info">
            No transactions yet. Your latest transactions will appear here.
          </Alert>
        </Box>
      )}

      {!error && !loading && Boolean(data?.items.length) && (
        <>
          <Box
            sx={{
              display: {
                xs: 'none',
                md: 'block',
              },
            }}
          >
            <RecentTransactionsTable
              transactions={data?.items ?? []}
            />
          </Box>

          <Box
            sx={{
              display: {
                xs: 'block',
                md: 'none',
              },
            }}
          >
            {data?.items.map((transaction) => (
              <RecentTransactionMobileCard
                key={transaction.id}
                transaction={transaction}
              />
            ))}
          </Box>
        </>
      )}
    </Paper>
  );
};
