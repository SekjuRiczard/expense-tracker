import {
  Box,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  Typography,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { CashFlowPoint } from '../types';
import { formatMoney, formatPeriodLong } from './analyticsHelpers';

export interface CashFlowTableProps {
  readonly data: readonly CashFlowPoint[];
  readonly currency: string;
}

const headCellSx = {
  py: 1.25,
  borderColor: flowlyPalette.dashboard.borderSoft,
  backgroundColor: 'rgba(248, 250, 252, 0.5)',
  color: flowlyPalette.dashboard.textSecondary,
  fontSize: '0.68rem',
  fontWeight: 600,
  letterSpacing: '0.04em',
  textTransform: 'uppercase',
} as const;

export const CashFlowTable = ({ data, currency }: CashFlowTableProps) => {
  return (
    <Box
      sx={{
        overflow: 'hidden',
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: '20px',
        backgroundColor: flowlyPalette.dashboard.surface,
      }}
    >
      <Box
        sx={{
          px: 3,
          py: 2,
          borderBottom: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        }}
      >
        <Typography
          component="h2"
          sx={{
            color: flowlyPalette.dashboard.textPrimary,
            fontSize: '1.05rem',
            fontWeight: 700,
          }}
        >
          Cash flow table
        </Typography>
      </Box>

      <Box sx={{ overflowX: 'auto' }}>
        <Table sx={{ minWidth: 560 }}>
          <TableHead>
            <TableRow>
              <TableCell sx={headCellSx}>Period</TableCell>
              <TableCell align="right" sx={headCellSx}>
                Income
              </TableCell>
              <TableCell align="right" sx={headCellSx}>
                Expenses
              </TableCell>
              <TableCell align="right" sx={headCellSx}>
                Balance
              </TableCell>
            </TableRow>
          </TableHead>

          <TableBody>
            {data.length === 0 ? (
              <TableRow>
                <TableCell
                  colSpan={4}
                  sx={{
                    py: 4,
                    borderColor: flowlyPalette.dashboard.borderSoft,
                    color: flowlyPalette.dashboard.textSecondary,
                    fontSize: '0.82rem',
                    textAlign: 'center',
                  }}
                >
                  No cash flow data available.
                </TableCell>
              </TableRow>
            ) : (
              data.map((point) => (
                <TableRow
                  key={point.period}
                  sx={{ '& td': { borderColor: flowlyPalette.dashboard.borderSoft } }}
                >
                  <TableCell>
                    <Typography
                      sx={{
                        color: flowlyPalette.dashboard.textPrimary,
                        fontSize: '0.8rem',
                        fontWeight: 500,
                      }}
                    >
                      {formatPeriodLong(point.period)}
                    </Typography>
                  </TableCell>

                  <TableCell align="right">
                    <Typography
                      sx={{
                        color: flowlyPalette.dashboard.emerald,
                        fontSize: '0.8rem',
                        fontVariantNumeric: 'tabular-nums',
                      }}
                    >
                      +{formatMoney(point.income, currency)}
                    </Typography>
                  </TableCell>

                  <TableCell align="right">
                    <Typography
                      sx={{
                        color: flowlyPalette.dashboard.rose,
                        fontSize: '0.8rem',
                        fontVariantNumeric: 'tabular-nums',
                      }}
                    >
                      {'\u2212'}
                      {formatMoney(point.expense, currency)}
                    </Typography>
                  </TableCell>

                  <TableCell align="right">
                    <Typography
                      sx={{
                        color: flowlyPalette.dashboard.textPrimary,
                        fontSize: '0.8rem',
                        fontWeight: 600,
                        fontVariantNumeric: 'tabular-nums',
                      }}
                    >
                      {formatMoney(point.balance, currency)}
                    </Typography>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </Box>
    </Box>
  );
};
