import React, { useMemo, useEffect, useState, useCallback } from 'react';
import './App.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import Header from './components/Header';
import UsersTable from './components/UsersTable';
import UserDetails from './components/UserDetails';
import Button from './components/Button';
import Expiration from './components/Expiration';
import Loader from './components/Loader/Loader';
import MainObject from './utils/mainObject';
import flattenJSON from './utils/flattenJSON';

function App() {
	const mainObject = useMemo(() => new MainObject(), []);
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

	const fetchData = useCallback(async ( url, isDetails = false ) => {
		setLoadingDisplay( true );
		setError( null );
		setExpiration( null );
		try {
			const response = await fetch( url, {
				method: 'GET',
				cache: 'no-store',
			} );
			const data = await response.json();
			if (
				! response?.ok ||
				( data?.status && data.status !== 'success' )
			) {
				throw new Error( data?.message || 'Incorrect request' );
			}
			if ( ! data && ! data?.data?.content ) {
				throw new Error( 'No data found' );
			}
			let dataArray = data?.data?.content || data;
			if ( isDetails ) {
				dataArray = flattenJSON( dataArray );
				setUsersDisplay( false );
				setDetails( dataArray );
				setDetailsDisplay( true );
				setLoadUsersButtonDisplay( true );
			} else {
				setLoadUsersButtonDisplay( false );
				setUsers( dataArray );
			}
			if ( ! mainObject.isReactDev && mainObject.isUserAdmin ) {
				setExpiration( 'No cache' );
				if ( data?.data?.expiration ) {
					setExpiration( data?.data?.expiration );
				}
			}
		} catch ( errorMessage ) {
			console.error( errorMessage );
			setError( errorMessage.toString() );
			if ( isDetails ) {
				setLoadDetailsButtonDisplay( true );
			} else {
				setLoadUsersButtonDisplay( true );
			}
		} finally {
			setLoadingDisplay( false );
		}
	}, [mainObject]);

	useEffect( () => {
		fetchData( mainObject.usersListUrl() );
	}, [fetchData, mainObject] );

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
		<div className="wrapper">
			<div className="container">
				<div className="row">
					<div className="col">
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
						{ expiration && (
							<Expiration expiration={ expiration } />
						) }
						{ error && <p className="error">{ error }</p> }
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
			</div>
		</div>
	);
}

export default App;
