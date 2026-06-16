import { KeyRounded, LockOutlined, ShieldOutlined } from '@mui/icons-material';
import { Box } from '@mui/material';
import { SectionCard } from './SectionCard';
import { SecurityRow } from './SecurityRow';
import type { SettingsUserInfo } from '../types';

export interface SecuritySectionProps {
  readonly user: SettingsUserInfo;
  readonly onChangePassword: () => void;
  readonly onChangePin: () => void;
}

export const SecuritySection = ({
  user,
  onChangePassword,
  onChangePin,
}: SecuritySectionProps) => {
  return (
    <SectionCard
      icon={ShieldOutlined}
      iconBgColor="#ECFDF5"
      iconColor="#059669"
      subtitle="Password and PIN"
      title="Security"
    >
      <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1.5 }}>
        <SecurityRow
          actionLabel={user.hasPassword ? 'Change password' : 'Set password'}
          icon={KeyRounded}
          isSet={user.hasPassword}
          notSetLabel="Not set"
          onAction={onChangePassword}
          setLabel="Set"
          title="Password"
        />

        <SecurityRow
          actionLabel={user.hasPin ? 'Change PIN' : 'Set PIN'}
          icon={LockOutlined}
          isSet={user.hasPin}
          notSetLabel="Not set"
          onAction={onChangePin}
          setLabel="Configured"
          title="PIN"
        />
      </Box>
    </SectionCard>
  );
};
