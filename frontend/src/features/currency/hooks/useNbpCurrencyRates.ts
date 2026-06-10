import {
  useQueries,
} from '@tanstack/react-query';
import {
  getNbpCurrencyRateSeries,
} from '../api';
import {
  supportedCurrencies,
  type CurrencyRate,
} from '../types';
import {
  calculatePercentageChange,
  formatPercentageChange,
  getCurrencyTrend,
} from '../utils';

const REFRESH_INTERVAL_MS = 60_000;
const STALE_TIME_MS = 55_000;

export const useNbpCurrencyRates = () => {
  const queries = useQueries({
    queries: supportedCurrencies.map((currency) => ({
      queryKey: [
        'nbp',
        'currency-rate',
        currency.code,
      ],
      queryFn: () => {
        return getNbpCurrencyRateSeries(currency.code);
      },
      refetchInterval: REFRESH_INTERVAL_MS,
      staleTime: STALE_TIME_MS,
      retry: 2,
    })),
  });

  const data: readonly CurrencyRate[] = queries.flatMap(
    (query, index) => {
      if (!query.data) {
        return [];
      }

      const currency = supportedCurrencies[index];
      const latestRate = query.data.rates.at(-1);
      const previousRate = query.data.rates.at(-2);

      if (!latestRate || !previousRate) {
        return [];
      }

      const changePercentage = calculatePercentageChange(
        latestRate.mid,
        previousRate.mid,
      );

      return [
        {
          code: currency.code,
          symbol: currency.symbol,
          pair: `${currency.code}/PLN`,

          rate: latestRate.mid,
          formattedRate: latestRate.mid.toFixed(4),

          changePercentage,
          formattedChange: formatPercentageChange(
            changePercentage,
          ),
          trend: getCurrencyTrend(
            changePercentage,
          ),

          effectiveDate: latestRate.effectiveDate,
          history: query.data.rates.map(
            (rate) => rate.mid,
          ),
        },
      ];
    },
  );

  const isPending = queries.some(
    (query) => query.isPending,
  );

  const isError = queries.some(
    (query) => query.isError,
  );

  const error = queries.find(
    (query) => query.error,
  )?.error ?? null;

  const refetch = async (): Promise<void> => {
    await Promise.all(
      queries.map(
        async (query) => query.refetch(),
      ),
    );
  };

  return {
    data,
    error,
    isError,
    isPending,
    refetch,
  };
};