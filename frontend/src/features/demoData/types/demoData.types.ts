import type { z } from 'zod';
import type {
  generateDemoDataResponseSchema,
  clearDemoDataResponseSchema,
  demoDataStatusResponseSchema,
} from '../schemas';

export type GenerateDemoDataResponse = z.infer<
  typeof generateDemoDataResponseSchema
>;

export type ClearDemoDataResponse = z.infer<
  typeof clearDemoDataResponseSchema
>;

export type DemoDataStatusResponse = z.infer<
  typeof demoDataStatusResponseSchema
>;
