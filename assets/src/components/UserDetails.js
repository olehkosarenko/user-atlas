import PropTypes from 'prop-types';
import UserDetail from './UserDetail';

function UsersDetails( { details } ) {
	return (
		<div
			className="user-atlas__table-wrap"
			role="region"
			aria-labelledby="user-atlas-details-caption"
			tabIndex="0"
		>
			<table className="user-atlas__table">
				<caption
					id="user-atlas-details-caption"
					className="user-atlas__screen-reader-text"
				>
					User details
				</caption>
				<thead>
					<tr>
						<th scope="col">Field</th>
						<th scope="col">Value</th>
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
