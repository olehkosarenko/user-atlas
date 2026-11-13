export default class MainObject {
	constructor() {
		this.ajaxUrl = this.getGlobalProperty( 'ajaxUrl', '' );
		this.nonce = this.getGlobalProperty( 'nonce', '' );
		this.title = this.getGlobalProperty( 'title', 'User Atlas' );
		this.isUserAdmin = this.getGlobalProperty( 'isUserAdmin', false );
	}

	getGlobalProperty( property, defaultValue = '' ) {
		return window?.global_user_atlas?.[ property ] || defaultValue;
	}

	usersListUrl() {
		return `${ this.ajaxUrl }?action=user_atlas&nonce=${ this.nonce }&cmd=users`;
	}

	userDetailsUrl( id ) {
		if ( ! id ) {
			throw new Error( 'User ID is required' );
		}

		return `${ this.ajaxUrl }?action=user_atlas&nonce=${ this.nonce }&cmd=user&id=${ id }`;
	}
}
