export type DashboardRange =
  | 'monthly'
  | 'quarterly'
  | 'yearly';

export interface DashboardPeriod {
  readonly from: string;
  readonly to: string;
  readonly currency: 'PLN';
}

const formatDate = (
  date: Date,
): string => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
};

const createPeriod = (
  from: Date,
  to: Date,
): DashboardPeriod => {
  return {
    from: formatDate(from),
    to: formatDate(to),
    currency: 'PLN',
  };
};

export const getDashboardPeriod = (
  range: DashboardRange,
  today = new Date(),
): DashboardPeriod => {
  const year = today.getFullYear();
  const month = today.getMonth();

  if (range === 'monthly') {
    return createPeriod(
      new Date(year, month, 1),
      new Date(year, month + 1, 0),
    );
  }

  if (range === 'quarterly') {
    const quarterStartMonth = Math.floor(month / 3) * 3;

    return createPeriod(
      new Date(year, quarterStartMonth, 1),
      new Date(year, quarterStartMonth + 3, 0),
    );
  }

  return createPeriod(
    new Date(year, 0, 1),
    new Date(year, 11, 31),
  );
};

export const getPreviousDashboardPeriod = (
  range: DashboardRange,
  today = new Date(),
): DashboardPeriod => {
  const year = today.getFullYear();
  const month = today.getMonth();

  if (range === 'monthly') {
    return createPeriod(
      new Date(year, month - 1, 1),
      new Date(year, month, 0),
    );
  }

  if (range === 'quarterly') {
    const quarterStartMonth = Math.floor(month / 3) * 3;

    return createPeriod(
      new Date(year, quarterStartMonth - 3, 1),
      new Date(year, quarterStartMonth, 0),
    );
  }

  return createPeriod(
    new Date(year - 1, 0, 1),
    new Date(year - 1, 11, 31),
  );
};
