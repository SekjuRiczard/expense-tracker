import { Box, Skeleton } from '@mui/material';

const SKELETON_COUNT = 5;

export const WalletsSkeleton = () => {
  return (
    <Box
      sx={{
        display: 'grid',
        gridTemplateColumns: {
          xs: '1fr',
          md: 'repeat(2, minmax(0, 1fr))',
          lg: 'repeat(3, minmax(0, 1fr))',
        },
        gap: 2.5,
      }}
    >
      {Array.from({ length: SKELETON_COUNT }).map((_, i) => (
        <Skeleton
          key={i}
          height={220}
          sx={{ borderRadius: '20px' }}
          variant="rectangular"
        />
      ))}
    </Box>
  );
};
