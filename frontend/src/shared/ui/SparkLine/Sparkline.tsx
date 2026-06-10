export interface SparklineProps {
  readonly values: readonly number[];
  readonly color: string;
  readonly ariaLabel: string;
}

const WIDTH = 104;
const HEIGHT = 34;
const PADDING = 2;

const createPoints = (
  values: readonly number[],
): string => {
  if (values.length < 2) {
    return '';
  }

  const minimum = Math.min(...values);
  const maximum = Math.max(...values);
  const range = maximum - minimum;

  return values
    .map((value, index) => {
      const x =
        PADDING
        + (
          index
          / (values.length - 1)
        ) * (WIDTH - PADDING * 2);

      const normalizedValue = range === 0
        ? 0.5
        : (value - minimum) / range;

      const y =
        HEIGHT
        - PADDING
        - normalizedValue * (HEIGHT - PADDING * 2);

      return `${x},${y}`;
    })
    .join(' ');
};

export const Sparkline = ({
  values,
  color,
  ariaLabel,
}: SparklineProps) => {
  return (
    <svg
      aria-label={ariaLabel}
      role="img"
      viewBox={`0 0 ${WIDTH} ${HEIGHT}`}
      width="104"
      height="34"
    >
      <polyline
        fill="none"
        points={createPoints(values)}
        stroke={color}
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth="2.5"
        style={{
          filter: `drop-shadow(0 3px 5px ${color}55)`,
        }}
      />
    </svg>
  );
};