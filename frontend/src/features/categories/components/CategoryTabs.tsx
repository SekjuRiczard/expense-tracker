import { Box, Tab, Tabs } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { CategoryType } from '../types';

export interface CategoryTabsProps {
  readonly activeType: CategoryType;
  readonly onChange: (type: CategoryType) => void;
}

export const CategoryTabs = ({ activeType, onChange }: CategoryTabsProps) => {
  return (
    <Box
      sx={{
        px: 1,
        pt: 1,
        borderBottom: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
      }}
    >
      <Tabs
        onChange={(_e, value: CategoryType) => { onChange(value); }}
        value={activeType}
        slotProps={{
          indicator: {
            sx: { backgroundColor: flowlyPalette.dashboard.indigo, height: 2 },
          },
        }}
        sx={{
          minHeight: 'auto',
          '& .MuiTab-root': {
            minHeight: 'auto',
            py: 1.25,
            px: 2,
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: '0.85rem',
            fontWeight: 600,
            textTransform: 'none',
            '&:hover': { color: flowlyPalette.dashboard.textPrimary },
            '&.Mui-selected': { color: flowlyPalette.dashboard.indigoDark },
          },
        }}
      >
        <Tab label="Expense categories" value="expense" />
        <Tab label="Income categories" value="income" />
      </Tabs>
    </Box>
  );
};
