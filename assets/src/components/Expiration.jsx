import React from 'react';
import PropTypes from 'prop-types';

function Expiration( { expiration } ) {
	return (
		<div className="alert alert-light">
			<b>Cache Expiry Time:</b> { expiration }
		</div>
	);
}

Expiration.propTypes = {
	expiration: PropTypes.string.isRequired,
};

export default Expiration;
