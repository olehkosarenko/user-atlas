/**
 * Request JSON from the plugin REST API.
 *
 * Resolves with the response body when the request succeeds and the body has
 * the expected shape, otherwise throws an error with a message for the user.
 *
 * @param {string} url   Request URL.
 * @param {string} nonce REST nonce of a logged-in user, empty for visitors.
 * @return {Promise<Object>} Response body with the `content` key.
 */
export default async function request( url, nonce = '' ) {
	const headers = { Accept: 'application/json' };
	if ( nonce ) {
		headers[ 'X-WP-Nonce' ] = nonce;
	}

	const response = await fetch( url, { method: 'GET', headers } );

	let data = null;
	try {
		data = await response.json();
	} catch {
		// The body is not JSON, for example an HTML error page.
	}

	if ( ! response.ok ) {
		throw new Error( data?.message || 'Request failed' );
	}

	if ( ! data || data.content === undefined || data.content === null ) {
		throw new Error( 'Unexpected response' );
	}

	return data;
}
