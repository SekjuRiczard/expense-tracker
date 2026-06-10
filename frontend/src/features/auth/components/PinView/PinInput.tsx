import {
  Box,
} from '@mui/material';
import {
  motion,
} from 'motion/react';
import {
  useEffect,
  useRef,
  useState,
  type ChangeEvent,
  type ClipboardEvent,
  type KeyboardEvent,
} from 'react';
import { PinDigit, } from './PinDigit';

const PIN_LENGTH = 6;

const createEmptyPin = (): string[] => {
  return Array.from(
    {
      length: PIN_LENGTH,
    },
    () => '',
  );
};

const normalizeDigits = (
  value: string,
): string => {
  return value
    .replace(/\D/g, '')
    .slice(0, PIN_LENGTH);
};

const MotionBox = motion.create(Box);

export interface PinInputProps {
  readonly disabled?: boolean;
  readonly resetKey: number;
  readonly shakeKey: number;
  readonly onComplete: (pin: string) => void;
}

export const PinInput = ({
  disabled = false,
  resetKey,
  shakeKey,
  onComplete,
}: PinInputProps) => {
  const inputRefs = useRef<Array<HTMLInputElement | null>>([]);
  const submittedPinRef = useRef('');

  const [digits, setDigits,] = useState<string[]>(
    createEmptyPin,
  );

  useEffect(() => {
    setDigits(createEmptyPin());
    submittedPinRef.current = '';

    window.setTimeout(() => {
      inputRefs.current[0]?.focus();
    }, 0);
  }, [resetKey,]);

  const updateDigits = (
    nextDigits: string[],
  ): void => {
    setDigits(nextDigits);

    const pin = nextDigits.join('');

    if (pin.length < PIN_LENGTH) {
      submittedPinRef.current = '';

      return;
    }

    if (submittedPinRef.current === pin) {
      return;
    }

    submittedPinRef.current = pin;
    onComplete(pin);
  };

  const handleChange = (
    index: number,
    event: ChangeEvent<HTMLInputElement>,
  ): void => {
    const digit = normalizeDigits(
      event.target.value,
    ).slice(-1);

    const nextDigits = [...digits,];
    nextDigits[index] = digit;

    updateDigits(nextDigits);

    if (digit && index < PIN_LENGTH - 1) {
      inputRefs.current[index + 1]?.focus();
    }
  };

  const handleKeyDown = (
    index: number,
    event: KeyboardEvent<HTMLInputElement>,
  ): void => {
    if (event.key === 'Backspace') {
      event.preventDefault();

      const nextDigits = [...digits,];

      if (nextDigits[index]) {
        nextDigits[index] = '';
        updateDigits(nextDigits);

        return;
      }

      if (index > 0) {
        nextDigits[index - 1] = '';
        updateDigits(nextDigits);
        inputRefs.current[index - 1]?.focus();
      }

      return;
    }

    if (
      event.key === 'ArrowLeft'
      && index > 0
    ) {
      inputRefs.current[index - 1]?.focus();
    }

    if (
      event.key === 'ArrowRight'
      && index < PIN_LENGTH - 1
    ) {
      inputRefs.current[index + 1]?.focus();
    }
  };

  const handlePaste = (
    event: ClipboardEvent<HTMLInputElement>,
  ): void => {
    event.preventDefault();

    const pastedDigits = normalizeDigits(
      event.clipboardData.getData('text'),
    );

    if (!pastedDigits) {
      return;
    }

    const nextDigits = createEmptyPin();

    pastedDigits
      .split('')
      .forEach((digit, index) => {
        nextDigits[index] = digit;
      });

    updateDigits(nextDigits);

    const nextEmptyIndex = nextDigits.findIndex(
      (digit) => digit === '',
    );

    if (nextEmptyIndex === -1) {
      inputRefs.current[PIN_LENGTH - 1]?.focus();

      return;
    }

    inputRefs.current[nextEmptyIndex]?.focus();
  };

  return (
    <MotionBox
      key={shakeKey}
      animate={
        shakeKey > 0
          ? {
            x: [0, -10, 10, -8, 8, -4, 4, 0,],
          }
          : {
            x: 0,
          }
      }
      transition={{
        duration: 0.42,
      }}
      sx={{
        display: 'grid',
        width: '100%',
        justifyContent: 'center',
        gridTemplateColumns: {
          xs: 'repeat(6, 42px)',
          sm: 'repeat(6, 52px)',
        },
        gap: {
          xs: 0.7,
          sm: 1,
        },
      }}
    >
      {digits.map((digit, index) => (
        <MotionBox
          key={`pin-digit-${index}`}
          initial={{
            opacity: 0,
            y: 10,
          }}
          animate={{
            opacity: 1,
            y: 0,
          }}
          transition={{
            duration: 0.2,
            delay: index * 0.045,
          }}
        >
          <PinDigit
            disabled={disabled}
            index={index}
            inputRef={(element) => {
              inputRefs.current[index] = element;
            }}
            onChange={(event) => {
              handleChange(index, event);
            }}
            onKeyDown={(event) => {
              handleKeyDown(index, event);
            }}
            onPaste={handlePaste}
            value={digit}
          />
        </MotionBox>
      ))}
    </MotionBox>
  );
};