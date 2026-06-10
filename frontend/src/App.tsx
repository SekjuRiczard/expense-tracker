import {
  useAuth,
} from './features/auth';

const App = () => {
  const {
    state,
  } = useAuth();

  return (
    <main>
      <h1>Flowly</h1>

      <pre>
        {JSON.stringify(state, null, 2)}
      </pre>
    </main>
  );
};

export default App;