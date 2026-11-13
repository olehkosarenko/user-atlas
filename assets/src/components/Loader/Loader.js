const Loader = () => (
	<div className="user-atlas__loader" role="status">
		<span className="user-atlas__screen-reader-text">Loading</span>
		<div className="user-atlas__loader-dots" aria-hidden="true">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
);

export default Loader;
