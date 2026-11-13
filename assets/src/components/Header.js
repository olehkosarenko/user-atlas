import PropTypes from 'prop-types';

const Header = ( { title } ) => (
	<header className="user-atlas__header">
		<h1>{ title }</h1>
	</header>
);

Header.propTypes = {
	title: PropTypes.string.isRequired,
};
export default Header;
