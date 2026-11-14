import { afterEach, describe, expect, it, vi } from 'vitest';
import request from '../request';

const stubFetch = ( { ok = true, body, invalidJson = false } ) => {
	const fetchMock = vi.fn().mockResolvedValue( {
		ok,
		json: invalidJson
			? () => Promise.reject( new SyntaxError( 'Unexpected token' ) )
			: () => Promise.resolve( body ),
	} );
	vi.stubGlobal( 'fetch', fetchMock );

	return fetchMock;
};

describe( 'request', () => {
	afterEach( () => {
		vi.unstubAllGlobals();
	} );

	it( 'returns the body of a successful response', async () => {
		const body = { content: [ { id: 1 } ], expiration: '2026-01-01' };
		stubFetch( { body } );

		await expect( request( '/users' ) ).resolves.toEqual( body );
	} );

	it( 'accepts an empty list as a valid response', async () => {
		stubFetch( { body: { content: [] } } );

		await expect( request( '/users' ) ).resolves.toEqual( {
			content: [],
		} );
	} );

	it( 'sends no nonce for visitors', async () => {
		const fetchMock = stubFetch( { body: { content: [] } } );

		await request( '/users' );

		expect( fetchMock ).toHaveBeenCalledWith( '/users', {
			method: 'GET',
			headers: { Accept: 'application/json' },
		} );
	} );

	it( 'sends the REST nonce of a logged-in user', async () => {
		const fetchMock = stubFetch( { body: { content: [] } } );

		await request( '/users', 'abc123' );

		expect( fetchMock ).toHaveBeenCalledWith( '/users', {
			method: 'GET',
			headers: { Accept: 'application/json', 'X-WP-Nonce': 'abc123' },
		} );
	} );

	it( 'throws the server message when the request fails', async () => {
		stubFetch( {
			ok: false,
			body: {
				code: 'user_atlas_api_error',
				message: 'API is temporarily unavailable',
			},
		} );

		await expect( request( '/users' ) ).rejects.toThrow(
			'API is temporarily unavailable'
		);
	} );

	it( 'throws a default message when a failed response is not JSON', async () => {
		stubFetch( { ok: false, invalidJson: true } );

		await expect( request( '/users' ) ).rejects.toThrow( 'Request failed' );
	} );

	it( 'throws when a successful response has no content', async () => {
		stubFetch( { body: { success: false, data: 'Invalid nonce' } } );

		await expect( request( '/users' ) ).rejects.toThrow(
			'Unexpected response'
		);
	} );

	it( 'throws when a successful response is not JSON', async () => {
		stubFetch( { invalidJson: true } );

		await expect( request( '/users' ) ).rejects.toThrow(
			'Unexpected response'
		);
	} );
} );
