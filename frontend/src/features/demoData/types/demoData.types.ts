import type { z } from 'zod';
import type {
  generateDemoDataResponseSchema,
  clearDemoDataResponseSchema,
} from '../schemas';

export type GenerateDemoDataResponse = z.infer<
  typeof generateDemoDataResponseSchema
>;

export type ClearDemoDataResponse = z.infer<
  typeof clearDemoDataResponseSchema
>;
