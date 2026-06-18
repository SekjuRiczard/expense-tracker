export const formatPercentageChange = (
  percentageChange: number,
): string => {
  const prefix = percentageChange > 0
    ? '+'
    : '';

  return `${prefix}${percentageChange.toFixed(2)}%`;
};