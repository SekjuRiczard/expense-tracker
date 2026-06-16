import {
  AccountBalanceWalletOutlined,
  AnalyticsOutlined,
  CategoryOutlined,
  DashboardOutlined,
  PaymentsOutlined,
  SavingsOutlined,
  SettingsOutlined,
} from '@mui/icons-material';
import type {
  SvgIconComponent,
} from '@mui/icons-material';

export interface NavigationItem {
  readonly label: string;
  readonly path: string;
  readonly icon: SvgIconComponent;
}

export const navigationItems: readonly NavigationItem[] = [
  {
    label: 'Overview',
    path: '/dashboard',
    icon: DashboardOutlined,
  },
  {
    label: 'Wallets',
    path: '/wallets',
    icon: AccountBalanceWalletOutlined,
  },
  {
    label: 'Transactions',
    path: '/transactions',
    icon: PaymentsOutlined,
  },
  {
    label: 'Categories',
    path: '/categories',
    icon: CategoryOutlined,
  },
  {
    label: 'Budgets',
    path: '/budgets',
    icon: SavingsOutlined,
  },
  {
    label: 'Analytics',
    path: '/analytics',
    icon: AnalyticsOutlined,
  },
  {
    label: 'Settings',
    path: '/settings',
    icon: SettingsOutlined,
  },
];
