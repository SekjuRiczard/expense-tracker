import { Box } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { Category } from '../types';
import { CategoriesEmptyState } from './CategoriesEmptyState';
import { CategoryRow } from './CategoryRow';

export interface CategoryListProps {
  readonly categories: readonly Category[];
  readonly onEdit: (category: Category) => void;
  readonly onDelete: (category: Category) => void;
}

export const CategoryList = ({
  categories,
  onEdit,
  onDelete,
}: CategoryListProps) => {
  if (categories.length === 0) {
    return <CategoriesEmptyState />;
  }

  const sorted = [...categories].sort((a, b) => {
    if (a.isDefault !== b.isDefault) {
      return a.isDefault ? -1 : 1;
    }
    return a.name.localeCompare(b.name);
  });

  return (
    <Box
      sx={{
        '& > * + *': {
          borderTop: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        },
      }}
    >
      {sorted.map((category) => (
        <CategoryRow
          category={category}
          key={category.id}
          onDelete={onDelete}
          onEdit={onEdit}
        />
      ))}
    </Box>
  );
};
