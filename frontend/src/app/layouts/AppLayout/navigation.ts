import AccountBalanceWalletOutlined from '@mui/icons-material/AccountBalanceWalletOutlined';
import AnalyticsOutlined from '@mui/icons-material/AnalyticsOutlined';
import CategoryOutlined from '@mui/icons-material/CategoryOutlined';
import DashboardOutlined from '@mui/icons-material/DashboardOutlined';
import PaymentsOutlined from '@mui/icons-material/PaymentsOutlined';
import SavingsOutlined from '@mui/icons-material/SavingsOutlined';
import type { SvgIconComponent } from '@mui/icons-material';

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
];
