import PropTypes from 'prop-types';

function Button( { title, onClick } ) {
	return (
		<button
			className="user-atlas__button"
			onClick={ ( event ) => onClick( event ) }
		>
			{ title }
		</button>
	);
}

Button.propTypes = {
	title: PropTypes.string,
	onClick: PropTypes.func.isRequired,
};

export default Button;
