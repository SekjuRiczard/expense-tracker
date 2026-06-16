import { Skeleton, Stack } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';

const ROW_COUNT = 5;

export const CategoriesSkeleton = () => {
  return (
    <Stack sx={{ gap: 0 }}>
      {Array.from({ length: ROW_COUNT }).map((_, i) => (
        <Stack
          key={i}
          sx={{
            alignItems: 'center',
            flexDirection: 'row',
            gap: 1.5,
            px: 3,
            py: 2,
            borderTop:
              i === 0
                ? 'none'
                : `1px solid ${flowlyPalette.dashboard.borderSoft}`,
          }}
        >
          <Skeleton height={40} variant="rounded" width={40} />
          <Stack sx={{ gap: 0.5, flex: 1 }}>
            <Skeleton height={14} variant="rounded" width="30%" />
            <Skeleton height={12} variant="rounded" width="20%" />
          </Stack>
        </Stack>
      ))}
    </Stack>
  );
};
