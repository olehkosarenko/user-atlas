import React from 'react';
import PropTypes from 'prop-types';

function UserDetail( { detail } ) {
	if ( detail.length !== 2 || detail[ 0 ] === '' || detail[ 1 ] === '' ) {
		return null;
	}

	return (
		<tr>
			<td>{ detail[ 0 ] }</td>
			<td>{ detail[ 1 ] }</td>
		</tr>
	);
}

UserDetail.propTypes = {
	detail: PropTypes.array.isRequired,
};

export default UserDetail;
