import { MoreHorizRounded } from '@mui/icons-material';
import { IconButton, ListItemIcon, ListItemText, Menu, MenuItem } from '@mui/material';
import { useState } from 'react';
import { flowlyPalette } from '../../../app/theme';

export interface WalletActionsMenuProps {
  readonly onViewDetails: () => void;
  readonly onEdit: () => void;
  readonly onDelete: () => void;
}

export const WalletActionsMenu = ({
  onViewDetails,
  onEdit,
  onDelete,
}: WalletActionsMenuProps) => {
  const [anchorEl, setAnchorEl] = useState<HTMLElement | null>(null);
  const open = Boolean(anchorEl);

  const handleOpen = (e: React.MouseEvent<HTMLElement>) => {
    e.stopPropagation();
    setAnchorEl(e.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleAction = (action: () => void) => () => {
    handleClose();
    action();
  };

  return (
    <>
      <IconButton
        aria-label="Wallet actions"
        onClick={handleOpen}
        size="small"
        sx={{
          color: flowlyPalette.dashboard.textMuted,
          '&:hover': {
            color: flowlyPalette.dashboard.textSecondary,
            backgroundColor: flowlyPalette.dashboard.background,
          },
        }}
      >
        <MoreHorizRounded sx={{ fontSize: 20 }} />
      </IconButton>

      <Menu
        anchorEl={anchorEl}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
        onClose={handleClose}
        open={open}
        transformOrigin={{ vertical: 'top', horizontal: 'right' }}
        slotProps={{
          paper: {
            sx: {
              mt: 0.5,
              borderRadius: '12px',
              border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
              boxShadow: '0 4px 20px rgba(0,0,0,0.08)',
              minWidth: 160,
            },
          },
        }}
      >
        <MenuItem
          onClick={handleAction(onViewDetails)}
          sx={{ borderRadius: '8px', mx: 0.5, fontSize: '0.85rem' }}
        >
          <ListItemText primary="View details" />
        </MenuItem>

        <MenuItem
          onClick={handleAction(onEdit)}
          sx={{ borderRadius: '8px', mx: 0.5, fontSize: '0.85rem' }}
        >
          <ListItemText primary="Edit wallet" />
        </MenuItem>

        <MenuItem
          onClick={handleAction(onDelete)}
          sx={{
            borderRadius: '8px',
            mx: 0.5,
            fontSize: '0.85rem',
            color: '#E11D48',
            '&:hover': { backgroundColor: flowlyPalette.dashboard.roseSoft },
          }}
        >
          <ListItemIcon sx={{ minWidth: 32, color: 'inherit' }} />
          <ListItemText primary="Delete wallet" />
        </MenuItem>
      </Menu>
    </>
  );
};
