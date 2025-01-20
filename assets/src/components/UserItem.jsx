import React from 'react';
import PropTypes from 'prop-types';

function UserItem({ user, onClick }) {
    const userHref = `#user_id=${user.id}`;

    return (
        <tr>
            <td>
                <a href={userHref} onClick={event => onClick(event, user.id)}>
                    {user.id}
                </a>
            </td>
            <td>
                <a href={userHref} onClick={event => onClick(event, user.id)}>
                    {user.name}
                </a>
            </td>
            <td>
                <a href={userHref} onClick={event => onClick(event, user.id)}>
                    {user.username}
                </a>
            </td>
        </tr>
    );
}

UserItem.propTypes = {
    user: PropTypes.object.isRequired,
    onClick: PropTypes.func.isRequired,
};

export default UserItem;
