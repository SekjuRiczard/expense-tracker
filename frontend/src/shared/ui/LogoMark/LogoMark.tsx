import { Box } from '@mui/material';

export interface LogoMarkProps {
  readonly size?: number;
  readonly title?: string;
}

export const LogoMark = ({
  size = 40,
  title = 'Flowly',
}: LogoMarkProps) => {
  return (
    <Box
      aria-label={title}
      component="svg"
      role="img"
      sx={{
        width: size,
        height: size,
        flexShrink: 0,
        display: 'block',
      }}
      viewBox="0 0 48 48"
    >
      <defs>
        <linearGradient id="flowly-mark" x1="0" x2="1" y1="0" y2="1">
          <stop offset="0%" stopColor="#6366F1" />
          <stop offset="100%" stopColor="#4338CA" />
        </linearGradient>
      </defs>

      <rect
        fill="url(#flowly-mark)"
        height="48"
        rx="13"
        width="48"
        x="0"
        y="0"
      />

      <path
        d="M12 30 L21 21 L27 27 L36 18"
        fill="none"
        stroke="#FFFFFF"
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth="3.2"
      />

      <path
        d="M30 18 L36 18 L36 24"
        fill="none"
        stroke="#FFFFFF"
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth="3.2"
      />
    </Box>
  );
};
