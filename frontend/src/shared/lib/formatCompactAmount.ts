export const formatCompactAmount = (
  amount: number,
): string => {
  return new Intl.NumberFormat(
    'en-US',
    {
      notation: 'compact',
      maximumFractionDigits: 1,
    },
  ).format(amount / 100);
};
