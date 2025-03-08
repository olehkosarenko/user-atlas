import React from 'react';
import PropTypes from 'prop-types';
import UserDetail from './UserDetail';

function UsersDetails( { details } ) {
	return (
		<div className="table-responsive">
			<table className="table table-striped table-hover">
				<thead>
					<tr>
						<th>Param</th>
						<th>Value</th>
					</tr>
				</thead>
				<tbody>
					{ details.length > 0 ? (
						details.map( ( detail, index ) => (
							<UserDetail key={ index } detail={ detail } />
						) )
					) : (
						<tr>
							<td colSpan="2">No details available</td>
						</tr>
					) }
				</tbody>
			</table>
		</div>
	);
}

UsersDetails.propTypes = {
	details: PropTypes.array.isRequired,
};

export default UsersDetails;
