import {
  DeleteOutlineRounded,
  EditOutlined,
  MoreHorizRounded,
  VisibilityOutlined,
} from '@mui/icons-material';
import {
  IconButton,
  ListItemIcon,
  ListItemText,
  Menu,
  MenuItem,
} from '@mui/material';
import { useState } from 'react';
import { flowlyPalette } from '../../../app/theme';

export interface BudgetActionsMenuProps {
  readonly onViewDetails: () => void;
  readonly onEdit: () => void;
  readonly onDelete: () => void;
}

export const BudgetActionsMenu = ({
  onViewDetails,
  onEdit,
  onDelete,
}: BudgetActionsMenuProps) => {
  const [anchorEl, setAnchorEl] = useState<HTMLElement | null>(null);
  const open = Boolean(anchorEl);

  const handleClose = () => {
    setAnchorEl(null);
  };

  return (
    <>
      <IconButton
        aria-label="Budget actions"
        onClick={(e) => { setAnchorEl(e.currentTarget); }}
        size="small"
        sx={{
          color: flowlyPalette.dashboard.textMuted,
          '&:hover': { color: flowlyPalette.dashboard.textSecondary },
        }}
      >
        <MoreHorizRounded fontSize="small" />
      </IconButton>

      <Menu
        anchorEl={anchorEl}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
        onClose={handleClose}
        open={open}
        transformOrigin={{ vertical: 'top', horizontal: 'right' }}
        slotProps={{ paper: { sx: { borderRadius: '12px', minWidth: 180 } } }}
      >
        <MenuItem
          onClick={() => { handleClose(); onViewDetails(); }}
        >
          <ListItemIcon>
            <VisibilityOutlined fontSize="small" />
          </ListItemIcon>
          <ListItemText>View details</ListItemText>
        </MenuItem>

        <MenuItem
          onClick={() => { handleClose(); onEdit(); }}
        >
          <ListItemIcon>
            <EditOutlined fontSize="small" />
          </ListItemIcon>
          <ListItemText>Edit budget</ListItemText>
        </MenuItem>

        <MenuItem
          onClick={() => { handleClose(); onDelete(); }}
          sx={{ color: flowlyPalette.dashboard.rose }}
        >
          <ListItemIcon>
            <DeleteOutlineRounded
              fontSize="small"
              sx={{ color: flowlyPalette.dashboard.rose }}
            />
          </ListItemIcon>
          <ListItemText>Delete budget</ListItemText>
        </MenuItem>
      </Menu>
    </>
  );
};
