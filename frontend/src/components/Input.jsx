export const Input = ({ ...props }) => {
    return <input {...props} style={{ padding: '10px', margin: '10px 0', width: '100%', ...props.style }} />;
};