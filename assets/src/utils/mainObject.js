export default class MainObject {
	constructor() {
		this.restUrl = this.getGlobalProperty( 'restUrl', '' );
		this.restNonce = this.getGlobalProperty( 'restNonce', '' );
		this.title = this.getGlobalProperty( 'title', 'User Atlas' );
		this.isUserAdmin = this.getGlobalProperty( 'isUserAdmin', false );
	}

	getGlobalProperty( property, defaultValue = '' ) {
		return window?.global_user_atlas?.[ property ] || defaultValue;
	}

	usersListUrl() {
		return `${ this.restUrl }users`;
	}

	userDetailsUrl( id ) {
		if ( ! id ) {
			throw new Error( 'User ID is required' );
		}

		return `${ this.restUrl }users/${ encodeURIComponent( id ) }`;
	}
}
