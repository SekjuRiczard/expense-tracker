import { Box, Paper, Typography } from '@mui/material';
import type { SvgIconComponent } from '@mui/icons-material';
import type { ReactNode } from 'react';
import { flowlyPalette } from '../../../app/theme';

export interface SectionCardProps {
  readonly icon: SvgIconComponent;
  readonly iconBgColor: string;
  readonly iconColor: string;
  readonly title: string;
  readonly subtitle: string;
  readonly children: ReactNode;
}

export const SectionCard = ({
  icon: Icon,
  iconBgColor,
  iconColor,
  title,
  subtitle,
  children,
}: SectionCardProps) => {
  return (
    <Paper
      component="section"
      elevation={0}
      sx={{
        p: { xs: 2.5, sm: 3 },
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: '20px',
        backgroundColor: flowlyPalette.dashboard.surface,
        height: '100%',
      }}
    >
      <Box
        sx={{
          display: 'flex',
          alignItems: 'flex-start',
          gap: 2,
          mb: 2.5,
        }}
      >
        <Box
          aria-hidden="true"
          sx={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            width: 40,
            height: 40,
            borderRadius: '10px',
            backgroundColor: iconBgColor,
            flexShrink: 0,
          }}
        >
          <Icon sx={{ color: iconColor, fontSize: 20 }} />
        </Box>

        <Box>
          <Typography
            component="h2"
            sx={{
              color: flowlyPalette.dashboard.textPrimary,
              fontSize: '1rem',
              fontWeight: 800,
              lineHeight: 1.25,
              letterSpacing: '-0.02em',
            }}
          >
            {title}
          </Typography>

          <Typography
            sx={{
              mt: 0.25,
              color: flowlyPalette.dashboard.textSecondary,
              fontSize: '0.8rem',
            }}
          >
            {subtitle}
          </Typography>
        </Box>
      </Box>

      {children}
    </Paper>
  );
};
