import PropTypes from 'prop-types';

function Expiration( { expiration } ) {
	return (
		<div className="user-atlas__notice">
			<b>Cache Expiry Time:</b> { expiration }
		</div>
	);
}

Expiration.propTypes = {
	expiration: PropTypes.string.isRequired,
};

export default Expiration;
