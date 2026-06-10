export const calculatePercentageChange = (
  currentValue: number,
  previousValue: number,
): number => {
  if (previousValue === 0) {
    return 0;
  }

  const percentageChange =
    ((currentValue - previousValue) / previousValue) * 100;

  return Number(
    percentageChange.toFixed(2),
  );
};