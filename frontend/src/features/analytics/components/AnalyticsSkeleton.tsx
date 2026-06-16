import { Box, Skeleton } from '@mui/material';

export const AnalyticsSkeleton = () => {
  return (
    <Box>
      <Skeleton
        height={72}
        sx={{ mb: 3, borderRadius: '16px' }}
        variant="rectangular"
      />

      <Box
        sx={{
          display: 'grid',
          gridTemplateColumns: {
            xs: 'repeat(2, minmax(0, 1fr))',
            lg: 'repeat(4, minmax(0, 1fr))',
          },
          gap: 2,
          mb: 3,
        }}
      >
        {Array.from({ length: 4 }).map((_, i) => (
          <Skeleton
            height={104}
            key={i}
            sx={{ borderRadius: '18px' }}
            variant="rectangular"
          />
        ))}
      </Box>

      <Box
        sx={{
          display: 'grid',
          gridTemplateColumns: { xs: '1fr', lg: 'repeat(3, minmax(0, 1fr))' },
          gap: 3,
          mb: 3,
        }}
      >
        <Skeleton
          height={360}
          sx={{ borderRadius: '20px', gridColumn: { lg: 'span 1' } }}
          variant="rectangular"
        />
        <Skeleton
          height={360}
          sx={{ borderRadius: '20px', gridColumn: { lg: 'span 2' } }}
          variant="rectangular"
        />
      </Box>

      <Skeleton
        height={220}
        sx={{ borderRadius: '20px' }}
        variant="rectangular"
      />
    </Box>
  );
};
