import {
  Navigate,
  Route,
  Routes,
} from 'react-router-dom';
import { AppLayout } from '../layouts/AppLayout';
import { DashboardPage } from '../../features/dashboard';
import { WalletsPage } from '../../features/wallets/pages';
import { TransactionsPage } from '../../features/transactions/pages';
import { CategoriesPage } from '../../features/categories/pages';
import { AnalyticsPage } from '../../features/analytics/pages';
import { BudgetsPage } from '../../features/budgets/pages';
import { SettingsPage } from '../../features/settings';

export const AppRouter = () => {
  return (
    <Routes>
      <Route element={<AppLayout />}>
        <Route
          element={<DashboardPage />}
          path="/dashboard"
        />

        <Route
          element={<WalletsPage />}
          path="/wallets"
        />

        <Route
          element={<TransactionsPage />}
          path="/transactions"
        />

        <Route
          element={<CategoriesPage />}
          path="/categories"
        />

        <Route
          element={<BudgetsPage />}
          path="/budgets"
        />

        <Route
          element={<AnalyticsPage />}
          path="/analytics"
        />
      </Route>

      <Route
        element={<SettingsPage />}
        path="/settings"
      />

      <Route
        element={<Navigate replace to="/settings" />}
        path="/Settings"
      />

      <Route
        element={<Navigate replace to="/dashboard" />}
        path="*"
      />
    </Routes>
  );
};
