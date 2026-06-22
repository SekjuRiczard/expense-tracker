import ArrowBackRounded from '@mui/icons-material/ArrowBackRounded';
import {
  Box,
  Button,
  CircularProgress,
  Stack,
  Typography,
} from '@mui/material';
import {
  AnimatePresence,
  motion,
} from 'motion/react';
import {
  useState,
} from 'react';
import { flowlyPalette, } from '../../../../app/theme';
import { useAuth, } from '../../hooks/useAuth';
import { getAuthErrorMessage, } from '../../utils';
import { PinHeader, } from './PinHeader';
import { PinInput, } from './PinInput';

export type PinViewMode =
  | 'setup'
  | 'verification';

type SetupStep =
  | 'enter'
  | 'confirm';

export interface PinViewProps {
  readonly mode: PinViewMode;
}

const MotionBox = motion.create(Box);

const PreviousPinPreview = () => {
  return (
    <Box
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
      {Array.from(
        {
          length: 6,
        },
        (_, index) => (
          <Box
            key={`previous-pin-${index}`}
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
              border: `1px solid ${flowlyPalette.auth.borderMuted}`,
              borderRadius: 2,
              backgroundColor: flowlyPalette.auth.backgroundMuted,
              color: flowlyPalette.auth.textMuted,
              fontSize: '1.3rem',
              fontWeight: 850,
            }}
          >
            •
          </Box>
        ),
      )}
    </Box>
  );
};

export const PinView = ({
  mode,
}: PinViewProps) => {
  const {
    logout,
    setupPin,
    verifyPin,
  } = useAuth();

  const [setupStep, setSetupStep,] = useState<SetupStep>('enter');
  const [firstPin, setFirstPin,] = useState<string | null>(null);

  const [resetKey, setResetKey,] = useState(0);
  const [shakeKey, setShakeKey,] = useState(0);

  const [errorMessage, setErrorMessage,] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting,] = useState(false);
  const [isGoingBack, setIsGoingBack,] = useState(false);

  const isVerification = mode === 'verification';
  const isConfirmStep =
    mode === 'setup'
    && setupStep === 'confirm';

  const clearInput = (): void => {
    setResetKey((currentKey) => currentKey + 1);
  };

  const showPinError = (
    message: string,
  ): void => {
    setErrorMessage(message);
    setShakeKey((currentKey) => currentKey + 1);
    clearInput();
  };

  const submitVerification = async (
    pin: string,
  ): Promise<void> => {
    setErrorMessage(null);
    setIsSubmitting(true);

    try {
      await verifyPin({
        pin,
      });
    } catch (error: unknown) {
      setIsSubmitting(false);
      showPinError(
        getAuthErrorMessage(error),
      );
    }
  };

  const submitSetup = async (
    pin: string,
  ): Promise<void> => {
    setErrorMessage(null);
    setIsSubmitting(true);

    try {
      await setupPin({
        pin,
      });
    } catch (error: unknown) {
      setIsSubmitting(false);
      showPinError(
        getAuthErrorMessage(error),
      );
    }
  };

  const handleComplete = (
    pin: string,
  ): void => {
    if (isVerification) {
      void submitVerification(pin);

      return;
    }

    if (setupStep === 'enter') {
      setFirstPin(pin);
      setSetupStep('confirm');
      setErrorMessage(null);
      clearInput();

      return;
    }

    if (firstPin !== pin) {
      showPinError('PIN codes do not match');

      return;
    }

    void submitSetup(pin);
  };

  const handleBack = async (): Promise<void> => {
    setErrorMessage(null);
    setIsGoingBack(true);

    try {
      await logout();
    } catch (error: unknown) {
      setErrorMessage(
        getAuthErrorMessage(error),
      );
      setIsGoingBack(false);
    }
  };

  if (isSubmitting) {
    return (
      <Stack
        sx={{
          alignItems: 'center',
          justifyContent: 'center',
          gap: 2,
          minHeight: 320,
        }}
      >
        <CircularProgress
          size={56}
          thickness={4}
          sx={{
            color: flowlyPalette.auth.focus,
          }}
        />

        <Typography
          sx={{
            color: flowlyPalette.auth.textSecondary,
            fontSize: '0.975rem',
            fontWeight: 700,
          }}
        >
          Verifying...
        </Typography>
      </Stack>
    );
  }

  const title = isVerification
    ? 'Enter your PIN'
    : isConfirmStep
      ? 'Confirm PIN'
      : 'Set up your PIN';

  const description = isVerification
    ? 'Enter your 6-digit PIN code to continue'
    : isConfirmStep
      ? 'Enter the same PIN code again to confirm it'
      : 'Enter a 6-digit PIN code to secure your account';

  return (
    <Stack
      sx={{
        alignItems: 'center',
        gap: 2.7,
        width: '100%',
      }}
    >
      <PinHeader
        description={description}
        title={title}
        variant={isConfirmStep
          ? 'check'
          : 'shield'}
      />

      <Stack
        sx={{
          alignItems: 'center',
          gap: 1.5,
          width: '100%',
        }}
      >
       <AnimatePresence>
  {isConfirmStep && (
    <MotionBox
      initial={{
        opacity: 0,
        y: -8,
      }}
      animate={{
        opacity: 1,
        y: 0,
      }}
      exit={{
        opacity: 0,
        y: -8,
      }}
      sx={{
        display: 'flex',
        width: '100%',
        justifyContent: 'center',
      }}
    >
      <PreviousPinPreview />
    </MotionBox>
  )}
</AnimatePresence>

        <PinInput
          disabled={isSubmitting}
          onComplete={handleComplete}
          resetKey={resetKey}
          shakeKey={shakeKey}
        />

        {errorMessage && (
          <Typography
            role="alert"
            sx={{
              color: '#D92D20',
              fontSize: '0.875rem',
              fontWeight: 700,
              textAlign: 'center',
            }}
          >
            {errorMessage}
          </Typography>
        )}
      </Stack>

      {isVerification && (
        <Button
          disabled={isGoingBack}
          fullWidth
          onClick={() => {
            void handleBack();
          }}
          startIcon={
            isGoingBack
              ? undefined
              : <ArrowBackRounded />
          }
          variant="text"
          sx={{
            minHeight: 48,
            color: flowlyPalette.auth.textSecondary,
            fontWeight: 700,
            textTransform: 'none',

            '&:hover': {
              backgroundColor: flowlyPalette.auth.backgroundMuted,
            },
          }}
        >
          {isGoingBack
            ? (
              <CircularProgress
                size={20}
                sx={{
                  color: flowlyPalette.auth.textSecondary,
                }}
              />
            )
            : 'Back'}
        </Button>
      )}
    </Stack>
  );
};