import {
  Paper,
  Typography,
} from '@mui/material';
import { flowlyPalette, } from '../theme';

export interface PlaceholderPageProps {
  readonly title: string;
}

export const PlaceholderPage = ({
  title,
}: PlaceholderPageProps) => {
  return (
    <Paper
      elevation={0}
      sx={{
        p: 3,
        border: `1px solid ${flowlyPalette.dashboard.border}`,
        borderRadius: 3,
      }}
    >
      <Typography
        component="h2"
        sx={{
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: '1.25rem',
          fontWeight: 800,
        }}
      >
        {title}
      </Typography>

      <Typography
        sx={{
          mt: 1,
          color: flowlyPalette.dashboard.textSecondary,
        }}
      >
        This module will be implemented in the next stage.
      </Typography>
    </Paper>
  );
};
