import React from "react";

const AttentionIcon = ({
	className = "",
	width = 16,
	height = 16,
	...props
}) => {
	return (
		<svg
			width={width}
			height={height}
			viewBox="0 0 16 17"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
			className={className}
			{...props}
		>
			<path
				d="M8 0.5C12.42 0.5 16 4.08 16 8.5C16 12.92 12.42 16.5 8 16.5C3.58 16.5 0 12.92 0 8.5C0 4.08 3.58 0.5 8 0.5ZM9.13 9.88L9.48 3.42H6.52L6.87 9.88H9.13ZM9.04 13.24C9.28 13.01 9.41 12.69 9.41 12.28C9.41 11.86 9.29 11.54 9.05 11.31C8.81 11.08 8.46 10.96 7.99 10.96C7.52 10.96 7.17 11.08 6.92 11.31C6.67 11.54 6.55 11.86 6.55 12.28C6.55 12.69 6.68 13.01 6.93 13.24C7.19 13.47 7.54 13.58 7.99 13.58C8.44 13.58 8.79 13.47 9.04 13.24Z"
				fill="#1E1E1E"
			/>
		</svg>
	);
};

export default AttentionIcon;
