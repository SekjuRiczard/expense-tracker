import {
  Navigate,
  Route,
  Routes,
} from 'react-router-dom';
import { AppLayout } from '../layouts/AppLayout';
import { DashboardPage } from '../../features/dashboard';
import { WalletsPage } from '../../features/wallets/pages';
import { SettingsPage } from '../../features/settings';
import { PlaceholderPage } from './PlaceholderPage';

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
          element={<PlaceholderPage title="Transactions" />}
          path="/transactions"
        />

        <Route
          element={<PlaceholderPage title="Categories" />}
          path="/categories"
        />

        <Route
          element={<PlaceholderPage title="Budgets" />}
          path="/budgets"
        />

        <Route
          element={<PlaceholderPage title="Analytics" />}
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
