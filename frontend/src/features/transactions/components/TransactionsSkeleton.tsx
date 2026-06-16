import { Skeleton, Stack } from '@mui/material';

const ROW_COUNT = 6;

export const TransactionsSkeleton = () => {
  return (
    <Stack sx={{ gap: 1, p: 2 }}>
      {Array.from({ length: ROW_COUNT }).map((_, i) => (
        <Skeleton key={i} height={52} variant="rounded" />
      ))}
    </Stack>
  );
};
