import { TrendingDownRounded, TrendingUpRounded } from "@mui/icons-material";
import { Alert, Box, Paper, Skeleton, Stack, Typography } from "@mui/material";
import { flowlyPalette } from "../../../../app/theme";
import { formatCurrency, type DashboardRange } from "../../../../shared/lib";
import type { AnalyticsDashboard } from "../../../analytics";
import { DashboardAreaChart } from "./DashboardAreaChart";
import { DashboardRangeSwitcher } from "./DashboardRangeSwitcher";

export interface DashboardHeroCardProps {
  readonly data?: AnalyticsDashboard;
  readonly error: boolean;
  readonly loading: boolean;
  readonly range: DashboardRange;
  readonly balanceChangePercentage: number | null;
  readonly onRangeChange: (range: DashboardRange) => void;
}

const SummaryValue = ({
  color,
  label,
  value,
}: {
  readonly color: string;
  readonly label: string;
  readonly value: string;
}) => {
  return (
    <Box>
      <Stack
        sx={{
          alignItems: "center",
          flexDirection: "row",
          gap: 0.8,
        }}
      >
        <Box
          sx={{
            width: 8,
            height: 8,
            borderRadius: "50%",
            backgroundColor: color,
          }}
        />

        <Typography
          sx={{
            color: flowlyPalette.dashboard.textSecondary,
            fontSize: "0.78rem",
            fontWeight: 700,
          }}
        >
          {label}
        </Typography>
      </Stack>

      <Typography
        sx={{
          mt: 0.65,
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: {
            xs: "1.35rem",
            sm: "1.65rem",
          },
          fontWeight: 900,
          letterSpacing: "-0.04em",
          fontVariantNumeric: "tabular-nums",
        }}
      >
        {value}
      </Typography>
    </Box>
  );
};

export const DashboardHeroCard = ({
  data,
  error,
  loading,
  range,
  balanceChangePercentage,
  onRangeChange,
}: DashboardHeroCardProps) => {
  if (loading) {
    return (
      <Skeleton
        sx={{
          borderRadius: 3,
        }}
        variant="rounded"
        height={430}
      />
    );
  }

  if (error || !data) {
    return (
      <Alert severity="error">Financial overview could not be loaded.</Alert>
    );
  }

  const isPositiveTrend = (balanceChangePercentage ?? 0) >= 0;

  const TrendIcon = isPositiveTrend ? TrendingUpRounded : TrendingDownRounded;

  return (
    <Paper
      component="section"
      elevation={0}
      sx={{
        p: 3,
        border: `1px solid ${flowlyPalette.dashboard.borderSoft}`,
        borderRadius: 3,
        backgroundColor: flowlyPalette.dashboard.surface,
      }}
    >
      <Stack
        sx={{
          flexDirection: {
            xs: "column",
            md: "row",
          },
          alignItems: {
            xs: "stretch",
            md: "center",
          },
          justifyContent: "space-between",
          gap: 2,
        }}
      >
        <Stack
          sx={{
            flexDirection: "row",
            gap: {
              xs: 3,
              sm: 5,
            },
          }}
        >
          <SummaryValue
            color={flowlyPalette.dashboard.indigo}
            label="Income"
            value={formatCurrency(data.summary.income, data.summary.currency)}
          />

          <SummaryValue
            color={flowlyPalette.dashboard.emerald}
            label="Balance"
            value={formatCurrency(data.summary.balance, data.summary.currency)}
          />
        </Stack>

        <DashboardRangeSwitcher onChange={onRangeChange} value={range} />
      </Stack>

      <Box
        sx={{
          mt: 3,
        }}
      >
        <DashboardAreaChart
          currency={data.summary.currency}
          points={data.cashFlow}
        />
      </Box>

      <Stack
        sx={{
          alignItems: "center",
          flexDirection: "row",
          justifyContent: "space-between",
          gap: 2,
          mt: 1,
        }}
      >
        <Typography
          sx={{
            color: flowlyPalette.dashboard.textMuted,
            fontSize: "0.78rem",
            fontWeight: 700,
          }}
        >
          Month
        </Typography>

        {balanceChangePercentage === null ? (
          <Typography
            sx={{
              color: flowlyPalette.dashboard.textMuted,
              fontSize: "0.78rem",
              fontWeight: 700,
            }}
          >
            Previous period comparison unavailable
          </Typography>
        ) : (
          <Stack
            sx={{
              alignItems: "center",
              flexDirection: "row",
              gap: 0.5,
              color: isPositiveTrend
                ? flowlyPalette.dashboard.emerald
                : flowlyPalette.dashboard.rose,
            }}
          >
            <TrendIcon fontSize="small" />

            <Typography
              sx={{
                color: "inherit",
                fontSize: "0.78rem",
                fontWeight: 800,
              }}
            >
              {balanceChangePercentage > 0 ? "+" : ""}
              {balanceChangePercentage.toFixed(1)}% vs previous period
            </Typography>
          </Stack>
        )}
      </Stack>
    </Paper>
  );
};
