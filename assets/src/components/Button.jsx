import React from 'react';
import PropTypes from 'prop-types';

function Button( { title, onClick } ) {
	return (
		<button
			className="btn btn-primary me-2"
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
