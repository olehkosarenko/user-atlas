import PropTypes from 'prop-types';
import UserItem from './UserItem';

function UsersTable( { users, openDetailsClick } ) {
	return (
		<div
			className="user-atlas__table-wrap"
			role="region"
			aria-labelledby="user-atlas-users-caption"
			tabIndex="0"
		>
			<table className="user-atlas__table">
				<caption
					id="user-atlas-users-caption"
					className="user-atlas__screen-reader-text"
				>
					Users
				</caption>
				<thead>
					<tr>
						<th scope="col">ID</th>
						<th scope="col">Name</th>
						<th scope="col">Username</th>
					</tr>
				</thead>
				<tbody>
					{ users.map( ( user ) => (
						<UserItem
							key={ user.id }
							user={ user }
							onClick={ openDetailsClick }
						/>
					) ) }
				</tbody>
			</table>
		</div>
	);
}

UsersTable.propTypes = {
	users: PropTypes.arrayOf( PropTypes.object ).isRequired,
	openDetailsClick: PropTypes.func.isRequired,
};

export default UsersTable;
