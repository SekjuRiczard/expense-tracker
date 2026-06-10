export const formatCurrency = (
  amount: number,
  currency: string,
): string => {
  return new Intl.NumberFormat(
    'pl-PL',
    {
      style: 'currency',
      currency,
    },
  ).format(amount / 100);
};
