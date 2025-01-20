import React from 'react';
import PropTypes from 'prop-types';
import UserItem from './UserItem';

function UsersTable({ users, openDetailsClick }) {
    return (
        <div className="table-responsive">
            <table className="table table-striped table-hover">
                <thead>
                <tr>
                    <th>id</th>
                    <th>name</th>
                    <th>username</th>
                </tr>
                </thead>
                <tbody>{users.map((user) => (
                    <UserItem key={user.id} user={user} onClick={openDetailsClick} />
                ))}</tbody>
            </table>
        </div>
    );
}

UsersTable.propTypes = {
    users: PropTypes.arrayOf(PropTypes.object).isRequired,
    openDetailsClick: PropTypes.func.isRequired,
};

export default UsersTable;
