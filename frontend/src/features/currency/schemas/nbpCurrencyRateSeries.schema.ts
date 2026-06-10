import {
  z,
} from 'zod';

export const nbpRateSchema = z.object({
  no: z.string(),
  effectiveDate: z.string(),
  mid: z.number(),
});

export const nbpCurrencyRateSeriesSchema = z.object({
  table: z.string(),
  currency: z.string(),
  code: z.string(),
  rates: z
    .array(nbpRateSchema)
    .min(2),
});