import {
  Drawer,
} from '@mui/material';
import { AppSidebar, } from './AppSidebar';

export interface MobileNavigationDrawerProps {
  readonly open: boolean;
  readonly onClose: () => void;
}

export const MobileNavigationDrawer = ({
  open,
  onClose,
}: MobileNavigationDrawerProps) => {
  return (
    <Drawer
      ModalProps={{
        keepMounted: true,
      }}
      onClose={onClose}
      open={open}
      sx={{
        display: {
          xs: 'block',
          md: 'none',
        },
        '& .MuiDrawer-paper': {
          width: 248,
        },
      }}
    >
      <AppSidebar />
    </Drawer>
  );
};
