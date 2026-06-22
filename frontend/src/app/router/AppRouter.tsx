import {
  lazy,
  Suspense,
} from 'react';
import {
  Navigate,
  Route,
  Routes,
} from 'react-router-dom';
import { AppLayout } from '../layouts/AppLayout';

const DashboardPage = lazy(async () => {
  const module = await import('../../features/dashboard/pages/DashboardPage');

  return {
    default: module.DashboardPage,
  };
});

const WalletsPage = lazy(async () => {
  const module = await import('../../features/wallets/pages/WalletsPage');

  return {
    default: module.WalletsPage,
  };
});

const TransactionsPage = lazy(async () => {
  const module = await import('../../features/transactions/pages/TransactionsPage');

  return {
    default: module.TransactionsPage,
  };
});

const CategoriesPage = lazy(async () => {
  const module = await import('../../features/categories/pages/CategoriesPage');

  return {
    default: module.CategoriesPage,
  };
});

const BudgetsPage = lazy(async () => {
  const module = await import('../../features/budgets/pages/BudgetsPage');

  return {
    default: module.BudgetsPage,
  };
});

const AnalyticsPage = lazy(async () => {
  const module = await import('../../features/analytics/pages/AnalyticsPage');

  return {
    default: module.AnalyticsPage,
  };
});

const SettingsPage = lazy(async () => {
  const module = await import('../../features/settings/pages/SettingsPage');

  return {
    default: module.SettingsPage,
  };
});

export const AppRouter = () => {
  return (
    <Suspense fallback={null}>
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
    </Suspense>
  );
};
