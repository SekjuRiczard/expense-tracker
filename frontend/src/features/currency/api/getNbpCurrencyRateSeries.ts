import {
  nbpCurrencyRateSeriesSchema,
} from '../schemas';
import type {
  NbpCurrencyRateSeries,
  SupportedCurrencyCode,
} from '../types';
import {
  nbpHttpClient,
} from './nbpHttpClient';

const HISTORY_LENGTH = 7;

export const getNbpCurrencyRateSeries = async (
  code: SupportedCurrencyCode,
): Promise<NbpCurrencyRateSeries> => {
  const response = await nbpHttpClient.get<unknown>(
    `/exchangerates/rates/a/${code.toLowerCase()}/last/${HISTORY_LENGTH}/`,
    {
      params: {
        format: 'json',
      },
    },
  );

  const parsedResponse = nbpCurrencyRateSeriesSchema.parse(
    response.data,
  );

  if (parsedResponse.code !== code) {
    throw new Error(
      `Unexpected currency code returned by NBP API: ${parsedResponse.code}`,
    );
  }

  return parsedResponse;
};