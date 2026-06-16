import { AddRounded } from '@mui/icons-material';
import { Alert, Box, Button, Paper, Snackbar } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import type { AppLayoutOutletContext } from '../../../app/layouts/AppLayout';
import { flowlyPalette } from '../../../app/theme';
import {
  CategoriesSkeleton,
  CategoryFormDialog,
  CategoryList,
  CategoryTabs,
  DeleteCategoryDialog,
} from '../components';
import { useCategories } from '../hooks';
import type { Category, CategoryType } from '../types';

interface SnackbarState {
  readonly open: boolean;
  readonly message: string;
  readonly severity: 'success' | 'error';
}

export const CategoriesPage = () => {
  const { setHeaderOverride } = useOutletContext<AppLayoutOutletContext>();

  const categoriesQuery = useCategories();
  const categories = useMemo(
    () => categoriesQuery.data ?? [],
    [categoriesQuery.data],
  );

  const [activeType, setActiveType] = useState<CategoryType>('expense');
  const [createOpen, setCreateOpen] = useState(false);
  const [editing, setEditing] = useState<Category | null>(null);
  const [deleting, setDeleting] = useState<Category | null>(null);

  const [snackbar, setSnackbar] = useState<SnackbarState>({
    open: false,
    message: '',
    severity: 'success',
  });

  const showToast = (
    message: string,
    severity: 'success' | 'error' = 'success',
  ) => {
    setSnackbar({ open: true, message, severity });
  };

  const filtered = useMemo(
    () => categories.filter((c) => c.type === activeType),
    [categories, activeType],
  );

  useEffect(() => {
    setHeaderOverride({
      subtitle: 'Manage transaction categories',
      action: (
        <Button
          onClick={() => { setCreateOpen(true); }}
          startIcon={<AddRounded sx={{ fontSize: 18 }} />}
          variant="contained"
          sx={{
            display: { xs: 'none', sm: 'inline-flex' },
            minHeight: 38,
            px: 1.6,
            borderRadius: 2,
            background: 'linear-gradient(135deg, #4F46E5, #4338CA)',
            boxShadow: 'none',
            fontSize: '0.8rem',
            fontWeight: 700,
            textTransform: 'none',
            '&:hover': {
              background: 'linear-gradient(135deg, #6366F1, #4F46E5)',
              boxShadow: 'none',
            },
          }}
        >
          Add category
        </Button>
      ),
    });

    return () => {
      setHeaderOverride(null);
    };
  }, [setHeaderOverride]);

  return (
    <Box>
      <Paper
        elevation={0}
        sx={{
          overflow: 'hidden',
          border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
          borderRadius: '20px',
          backgroundColor: flowlyPalette.dashboard.surface,
        }}
      >
        <CategoryTabs activeType={activeType} onChange={setActiveType} />

        {categoriesQuery.isError && (
          <Box sx={{ p: 2 }}>
            <Alert
              action={
                <Button
                  color="inherit"
                  onClick={() => { void categoriesQuery.refetch(); }}
                  size="small"
                >
                  Retry
                </Button>
              }
              severity="error"
              sx={{ borderRadius: '12px' }}
            >
              Failed to load categories.
            </Alert>
          </Box>
        )}

        {!categoriesQuery.isError && categoriesQuery.isPending && (
          <CategoriesSkeleton />
        )}

        {!categoriesQuery.isError && !categoriesQuery.isPending && (
          <CategoryList
            categories={filtered}
            onDelete={(c) => { setDeleting(c); }}
            onEdit={(c) => { setEditing(c); }}
          />
        )}
      </Paper>

      <CategoryFormDialog
        defaultType={activeType}
        onClose={() => { setCreateOpen(false); }}
        open={createOpen}
        showToast={showToast}
      />

      <CategoryFormDialog
        category={editing}
        onClose={() => { setEditing(null); }}
        open={Boolean(editing)}
        showToast={showToast}
      />

      <DeleteCategoryDialog
        category={deleting}
        onClose={() => { setDeleting(null); }}
        open={Boolean(deleting)}
        showToast={showToast}
      />

      <Snackbar
        anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
        autoHideDuration={4000}
        onClose={() => { setSnackbar((s) => ({ ...s, open: false })); }}
        open={snackbar.open}
      >
        <Alert
          onClose={() => { setSnackbar((s) => ({ ...s, open: false })); }}
          severity={snackbar.severity}
          sx={{ borderRadius: '12px', fontWeight: 600 }}
          variant="filled"
        >
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Box>
  );
};
