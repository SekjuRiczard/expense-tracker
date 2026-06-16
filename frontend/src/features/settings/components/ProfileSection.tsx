import { PersonOutlineRounded } from '@mui/icons-material';
import { Box } from '@mui/material';
import { SectionCard } from './SectionCard';
import { ProfileRow } from './ProfileRow';
import type { SettingsUserInfo } from '../types';

export interface ProfileSectionProps {
  readonly user: SettingsUserInfo;
}

export const ProfileSection = ({ user }: ProfileSectionProps) => {
  return (
    <SectionCard
      icon={PersonOutlineRounded}
      iconBgColor="#EEF2FF"
      iconColor="#4F46E5"
      subtitle="Account data — read only"
      title="Profile"
    >
      <Box component="dl" sx={{ m: 0 }}>
        <ProfileRow label="Username" value={user.username} />
        <ProfileRow label="Email" value={user.email} />
        <ProfileRow label="Role" value={user.roleLabel} />
      </Box>
    </SectionCard>
  );
};
