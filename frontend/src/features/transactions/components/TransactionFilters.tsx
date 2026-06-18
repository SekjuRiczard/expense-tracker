import { FilterListRounded } from '@mui/icons-material';
import {
  Box,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { Category } from '../../categories';
import type { Wallet } from '../../wallets';
import type { TransactionFilters as Filters, TransactionType } from '../types';

export interface TransactionFiltersProps {
  readonly filters: Filters;
  readonly wallets: readonly Wallet[];
  readonly categories: readonly Category[];
  readonly onChange: (partial: Partial<Filters>) => void;
}

const selectSx = {
  minWidth: 150,
  '& .MuiOutlinedInput-root': {
    height: 40,
    borderRadius: '10px',
    backgroundColor: flowlyPalette.dashboard.background,
    fontSize: '0.82rem',
    '& fieldset': { borderColor: flowlyPalette.dashboard.border },
  },
} as const;

const ALL_VALUE = '__all__';

export const TransactionFilters = ({
  filters,
  wallets,
  categories,
  onChange,
}: TransactionFiltersProps) => {
  const dateRangeInvalid =
    Boolean(filters.from) &&
    Boolean(filters.to) &&
    (filters.from as string) > (filters.to as string);

  return (
    <Box
      sx={{
        mb: 3,
        p: 2,
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: '16px',
        backgroundColor: flowlyPalette.dashboard.surface,
      }}
    >
      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'row',
          flexWrap: 'wrap',
          gap: 2,
        }}
      >
        <FilterListRounded
          sx={{ color: flowlyPalette.dashboard.textMuted, fontSize: 20 }}
        />

        <FormControl size="small" sx={selectSx}>
          <InputLabel id="filter-type-label">Type</InputLabel>
          <Select
            label="Type"
            labelId="filter-type-label"
            onChange={(e) => {
              const value = e.target.value;
              onChange({
                type:
                  value === ALL_VALUE
                    ? undefined
                    : (value as TransactionType),
              });
            }}
            value={filters.type ?? ALL_VALUE}
          >
            <MenuItem value={ALL_VALUE}>All types</MenuItem>
            <MenuItem value="income">Income</MenuItem>
            <MenuItem value="expense">Expense</MenuItem>
          </Select>
        </FormControl>

        <FormControl size="small" sx={selectSx}>
          <InputLabel id="filter-wallet-label">Wallet</InputLabel>
          <Select
            label="Wallet"
            labelId="filter-wallet-label"
            onChange={(e) => {
              const value = e.target.value;
              onChange({
                walletId:
                  value === ALL_VALUE ? undefined : Number(value),
              });
            }}
            value={
              filters.walletId !== undefined
                ? String(filters.walletId)
                : ALL_VALUE
            }
          >
            <MenuItem value={ALL_VALUE}>All wallets</MenuItem>
            {wallets.map((wallet) => (
              <MenuItem key={wallet.id} value={String(wallet.id)}>
                {wallet.name}
              </MenuItem>
            ))}
          </Select>
        </FormControl>

        <FormControl size="small" sx={selectSx}>
          <InputLabel id="filter-category-label">Category</InputLabel>
          <Select
            label="Category"
            labelId="filter-category-label"
            onChange={(e) => {
              const value = e.target.value;
              onChange({
                categoryId:
                  value === ALL_VALUE ? undefined : Number(value),
              });
            }}
            value={
              filters.categoryId !== undefined
                ? String(filters.categoryId)
                : ALL_VALUE
            }
          >
            <MenuItem value={ALL_VALUE}>All categories</MenuItem>
            {categories.map((category) => (
              <MenuItem key={category.id} value={String(category.id)}>
                {category.name}
              </MenuItem>
            ))}
          </Select>
        </FormControl>

        <Stack
          sx={{
            alignItems: 'center',
            flexDirection: 'row',
            gap: 1,
          }}
        >
          <TextField
            label="From"
            onChange={(e) => {
              onChange({ from: e.target.value || undefined });
            }}
            size="small"
            slotProps={{ inputLabel: { shrink: true } }}
            sx={{
              '& .MuiOutlinedInput-root': {
                height: 40,
                borderRadius: '10px',
                backgroundColor: flowlyPalette.dashboard.background,
                fontSize: '0.82rem',
              },
            }}
            type="date"
            value={filters.from ?? ''}
          />

          <Typography sx={{ color: flowlyPalette.dashboard.textMuted }}>
            &mdash;
          </Typography>

          <TextField
            error={dateRangeInvalid}
            helperText={dateRangeInvalid ? 'From must be before To' : undefined}
            label="To"
            onChange={(e) => {
              onChange({ to: e.target.value || undefined });
            }}
            size="small"
            slotProps={{ inputLabel: { shrink: true } }}
            sx={{
              '& .MuiOutlinedInput-root': {
                height: 40,
                borderRadius: '10px',
                backgroundColor: flowlyPalette.dashboard.background,
                fontSize: '0.82rem',
              },
            }}
            type="date"
            value={filters.to ?? ''}
          />
        </Stack>
      </Stack>
    </Box>
  );
};
