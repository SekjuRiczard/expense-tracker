import {
  DeleteOutlineRounded,
  EditOutlined,
  LocalOfferRounded,
} from '@mui/icons-material';
import { Box, IconButton, Stack, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';
import type { Category } from '../types';
import { CATEGORY_TYPE_LABELS } from './categoryHelpers';

export interface CategoryRowProps {
  readonly category: Category;
  readonly onEdit: (category: Category) => void;
  readonly onDelete: (category: Category) => void;
}

const Pill = ({
  label,
  background,
  color,
}: {
  readonly label: string;
  readonly background: string;
  readonly color: string;
}) => (
  <Box
    component="span"
    sx={{
      display: 'inline-flex',
      alignItems: 'center',
      px: 1,
      py: 0.35,
      borderRadius: '999px',
      fontSize: '0.62rem',
      fontWeight: 600,
      lineHeight: 1,
      backgroundColor: background,
      color,
    }}
  >
    {label}
  </Box>
);

export const CategoryRow = ({
  category,
  onEdit,
  onDelete,
}: CategoryRowProps) => {
  const isIncome = category.type === 'income';

  const actionButtonSx = {
    width: 36,
    height: 36,
    borderRadius: '10px',
    color: flowlyPalette.dashboard.textSecondary,
  } as const;

  return (
    <Stack
      sx={{
        alignItems: 'center',
        flexDirection: 'row',
        justifyContent: 'space-between',
        gap: 2,
        px: 3,
        py: 2,
        transition: 'background-color 150ms ease',
        '&:hover': { backgroundColor: 'rgba(248, 250, 252, 0.5)' },
      }}
    >
      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'row',
          gap: 1.5,
          minWidth: 0,
        }}
      >
        <Box
          aria-hidden="true"
          sx={{
            display: 'grid',
            width: 40,
            height: 40,
            flexShrink: 0,
            placeItems: 'center',
            borderRadius: '12px',
            backgroundColor: isIncome
              ? flowlyPalette.dashboard.emeraldSoft
              : flowlyPalette.dashboard.roseSoft,
            color: isIncome
              ? flowlyPalette.dashboard.emerald
              : flowlyPalette.dashboard.rose,
          }}
        >
          <LocalOfferRounded sx={{ fontSize: 19 }} />
        </Box>

        <Box sx={{ minWidth: 0 }}>
          <Typography
            sx={{
              color: flowlyPalette.dashboard.textPrimary,
              fontSize: '0.88rem',
              fontWeight: 600,
            }}
          >
            {category.name}
          </Typography>

          <Stack
            sx={{
              alignItems: 'center',
              flexDirection: 'row',
              gap: 0.75,
              mt: 0.5,
            }}
          >
            <Pill
              background={
                isIncome
                  ? flowlyPalette.dashboard.emeraldSoft
                  : flowlyPalette.dashboard.roseSoft
              }
              color={isIncome ? '#047857' : '#BE123C'}
              label={CATEGORY_TYPE_LABELS[category.type]}
            />

            {category.isDefault && (
              <Pill
                background="#F1F5F9"
                color={flowlyPalette.dashboard.textSecondary}
                label="Default"
              />
            )}
          </Stack>
        </Box>
      </Stack>

      <Stack sx={{ alignItems: 'center', flexDirection: 'row', gap: 0.5 }}>
        <IconButton
          aria-label="Edit category"
          onClick={() => { onEdit(category); }}
          sx={{
            ...actionButtonSx,
            '&:hover': { backgroundColor: '#F1F5F9' },
          }}
        >
          <EditOutlined sx={{ fontSize: 18 }} />
        </IconButton>

        <IconButton
          aria-label="Delete category"
          disabled={category.isDefault}
          onClick={() => { onDelete(category); }}
          sx={{
            ...actionButtonSx,
            '&:hover': {
              backgroundColor: flowlyPalette.dashboard.roseSoft,
              color: flowlyPalette.dashboard.rose,
            },
            '&.Mui-disabled': { opacity: 0.4 },
          }}
        >
          <DeleteOutlineRounded sx={{ fontSize: 18 }} />
        </IconButton>
      </Stack>
    </Stack>
  );
};
