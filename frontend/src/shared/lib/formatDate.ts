export const formatDate = (
  value: string,
): string => {
  return new Intl.DateTimeFormat(
    'pl-PL',
  ).format(new Date(value));
};
