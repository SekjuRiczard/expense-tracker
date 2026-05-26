export const Button = ({ children, ...props }) => {

    return <button {...props} style={{ padding: '10px 20px', cursor: 'pointer', ...props.style }}>{children}</button>;
};