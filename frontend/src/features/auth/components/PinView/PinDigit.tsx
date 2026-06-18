import {
  Box,
  InputBase,
} from '@mui/material';
import type {
  ChangeEventHandler,
  ClipboardEventHandler,
  KeyboardEventHandler,
} from 'react';
import { flowlyPalette, } from '../../../../app/theme';

export interface PinDigitProps {
  readonly value: string;
  readonly index: number;
  readonly disabled?: boolean;
  readonly inputRef: (element: HTMLInputElement | null) => void;
  readonly onChange: ChangeEventHandler<HTMLInputElement>;
  readonly onKeyDown: KeyboardEventHandler<HTMLInputElement>;
  readonly onPaste: ClipboardEventHandler<HTMLInputElement>;
}

export const PinDigit = ({
  value,
  index,
  disabled = false,
  inputRef,
  onChange,
  onKeyDown,
  onPaste,
}: PinDigitProps) => {
  return (
    <Box
      sx={{
        display: 'grid',
        width: {
          xs: 42,
          sm: 52,
        },
        height: {
          xs: 50,
          sm: 60,
        },
        placeItems: 'center',
        border: `1px solid ${flowlyPalette.auth.border}`,
        borderRadius: 2,
        backgroundColor: flowlyPalette.auth.background,
        transition: 'border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease',

        '&:focus-within': {
          borderColor: flowlyPalette.auth.focus,
          boxShadow: `0 0 0 4px ${flowlyPalette.auth.focusGlow}`,
          transform: 'scale(1.045)',
        },
      }}
    >
      <InputBase
        autoComplete={index === 0
          ? 'one-time-code'
          : 'off'}
        disabled={disabled}
        inputRef={inputRef}
        onChange={onChange}
        onKeyDown={onKeyDown}
        onPaste={onPaste}
        value={value}
        slotProps={{
          input: {
            'aria-label': `PIN digit ${index + 1}`,
            inputMode: 'numeric',
            maxLength: 1,
            pattern: '[0-9]*',
          },
        }}
        sx={{
          width: '100%',
          height: '100%',

          '& input': {
            p: 0,
            color: flowlyPalette.auth.textPrimary,
            fontSize: {
              xs: '1.35rem',
              sm: '1.55rem',
            },
            fontWeight: 850,
            textAlign: 'center',
          },
        }}
      />
    </Box>
  );
};