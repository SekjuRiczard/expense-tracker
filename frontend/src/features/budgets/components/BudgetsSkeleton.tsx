import { Box, Skeleton, Stack } from '@mui/material';

const SkeletonCard = () => (
  <Box
    sx={{
      backgroundColor: '#FFFFFF',
      border: '1px solid #F1F5F9',
      borderRadius: '20px',
      p: 3,
      display: 'flex',
      flexDirection: 'column',
      gap: 2.75,
    }}
  >
    <Stack
      sx={{
        alignItems: 'flex-start',
        flexDirection: 'row',
        justifyContent: 'space-between',
      }}
    >
      <Box>
        <Skeleton height={20} variant="text" width={140} />
        <Skeleton height={14} variant="text" width={180} />
      </Box>
      <Skeleton height={28} variant="circular" width={28} />
    </Stack>

    <Skeleton height={30} variant="text" width={160} />
    <Skeleton height={8} sx={{ borderRadius: '999px' }} variant="rectangular" width="100%" />

    <Stack
      sx={{
        alignItems: 'center',
        flexDirection: 'row',
        justifyContent: 'space-between',
      }}
    >
      <Skeleton height={16} variant="text" width={140} />
      <Skeleton height={16} variant="text" width={120} />
    </Stack>
  </Box>
);

export const BudgetsSkeleton = () => {
  return (
    <Box
      sx={{
        display: 'grid',
        gap: '20px',
        gridTemplateColumns: { xs: '1fr', md: 'repeat(2, 1fr)' },
      }}
    >
      {Array.from({ length: 4 }).map((_, index) => (
        <SkeletonCard key={index} />
      ))}
    </Box>
  );
};
