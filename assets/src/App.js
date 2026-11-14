import { useMemo, useEffect, useState, useCallback } from '@wordpress/element';
import './scss/main.scss';
import Header from './components/Header';
import UsersTable from './components/UsersTable';
import UserDetails from './components/UserDetails';
import Button from './components/Button';
import Expiration from './components/Expiration';
import Loader from './components/Loader/Loader';
import MainObject from './utils/mainObject';
import flattenJSON from './utils/flattenJSON';
import request from './utils/request';

function App() {
	const mainObject = useMemo( () => new MainObject(), [] );
	const [ users, setUsers ] = useState( [] );
	const [ details, setDetails ] = useState( [] );
	const [ detailsId, setDetailsId ] = useState( null );
	const [ error, setError ] = useState( null );
	const [ expiration, setExpiration ] = useState( null );
	const [ usersDisplay, setUsersDisplay ] = useState( true );
	const [ detailsDisplay, setDetailsDisplay ] = useState( false );
	const [ loadUsersButtonDisplay, setLoadUsersButtonDisplay ] =
		useState( false );
	const [ loadDetailsButtonDisplay, setLoadDetailsButtonDisplay ] =
		useState( false );
	const [ loadingDisplay, setLoadingDisplay ] = useState( true );

	const fetchData = useCallback(
		async ( url, isDetails = false ) => {
			setLoadingDisplay( true );
			setError( null );
			setExpiration( null );
			setLoadDetailsButtonDisplay( false );
			try {
				const data = await request( url, mainObject.restNonce );
				if ( isDetails ) {
					setUsersDisplay( false );
					setDetails( flattenJSON( data.content ) );
					setDetailsDisplay( true );
					setLoadUsersButtonDisplay( true );
				} else {
					setLoadUsersButtonDisplay( false );
					setUsers(
						Array.isArray( data.content ) ? data.content : []
					);
				}
				if ( mainObject.isUserAdmin ) {
					setExpiration( data.expiration || 'No cache' );
				}
			} catch ( requestError ) {
				setError( requestError.message );
				if ( isDetails ) {
					setLoadDetailsButtonDisplay( true );
				} else {
					setLoadUsersButtonDisplay( true );
				}
			} finally {
				setLoadingDisplay( false );
			}
		},
		[ mainObject ]
	);

	useEffect( () => {
		fetchData( mainObject.usersListUrl() );
	}, [ fetchData, mainObject ] );

	const handleOpenDetailsClick = ( event, id ) => {
		event.preventDefault();
		setDetailsId( id );
		fetchData( mainObject.userDetailsUrl( id ), true );
	};

	const handleUsersLoadButton = () => {
		setUsersDisplay( true );
		setDetailsDisplay( false );
		fetchData( mainObject.usersListUrl() );
	};

	const handleDetailsLoadButton = () => {
		fetchData( mainObject.userDetailsUrl( detailsId ), true );
	};

	return (
		<div className="user-atlas">
			<div className="user-atlas__container">
				<Header title={ mainObject.title } />
				{ users.length > 0 && usersDisplay && (
					<UsersTable
						users={ users }
						openDetailsClick={ handleOpenDetailsClick }
					/>
				) }
				{ ! users.length &&
					usersDisplay &&
					! detailsDisplay &&
					! error &&
					! loadingDisplay && (
						<div>
							<p>No users found</p>
							{ ! loadUsersButtonDisplay && (
								<Button
									title="Reload Users Table"
									onClick={ handleUsersLoadButton }
								/>
							) }
						</div>
					) }
				{ details.length > 0 && detailsDisplay && (
					<UserDetails details={ details } />
				) }
				{ expiration && <Expiration expiration={ expiration } /> }
				{ error && <p className="user-atlas__error">{ error }</p> }
				{ loadUsersButtonDisplay && (
					<Button
						title="Load Users Table"
						onClick={ handleUsersLoadButton }
					/>
				) }
				{ loadDetailsButtonDisplay && (
					<Button
						title="Reload User Details"
						onClick={ handleDetailsLoadButton }
					/>
				) }
				{ loadingDisplay && <Loader /> }
			</div>
		</div>
	);
}

export default App;
