import { afterEach, describe, expect, it, vi } from 'vitest';
import MainObject from '../mainObject';

describe( 'MainObject', () => {
	afterEach( () => {
		vi.unstubAllGlobals();
	} );

	it( 'reads settings from global_user_atlas', () => {
		vi.stubGlobal( 'window', {
			global_user_atlas: {
				restUrl: 'https://example.com/wp-json/user-atlas/v1/',
				restNonce: 'abc123',
				title: 'Team',
				isUserAdmin: true,
			},
		} );

		const mainObject = new MainObject();

		expect( mainObject.restUrl ).toBe(
			'https://example.com/wp-json/user-atlas/v1/'
		);
		expect( mainObject.restNonce ).toBe( 'abc123' );
		expect( mainObject.title ).toBe( 'Team' );
		expect( mainObject.isUserAdmin ).toBe( true );
	} );

	it( 'falls back to defaults when global_user_atlas is missing', () => {
		vi.stubGlobal( 'window', {} );

		const mainObject = new MainObject();

		expect( mainObject.restUrl ).toBe( '' );
		expect( mainObject.restNonce ).toBe( '' );
		expect( mainObject.title ).toBe( 'User Atlas' );
		expect( mainObject.isUserAdmin ).toBe( false );
	} );

	it( 'builds the users list URL', () => {
		vi.stubGlobal( 'window', {
			global_user_atlas: { restUrl: '/wp-json/user-atlas/v1/' },
		} );

		expect( new MainObject().usersListUrl() ).toBe(
			'/wp-json/user-atlas/v1/users'
		);
	} );

	it( 'builds the users list URL when pretty permalinks are off', () => {
		vi.stubGlobal( 'window', {
			global_user_atlas: { restUrl: '/?rest_route=/user-atlas/v1/' },
		} );

		expect( new MainObject().usersListUrl() ).toBe(
			'/?rest_route=/user-atlas/v1/users'
		);
	} );

	it( 'builds the user details URL', () => {
		vi.stubGlobal( 'window', {
			global_user_atlas: { restUrl: '/wp-json/user-atlas/v1/' },
		} );

		expect( new MainObject().userDetailsUrl( 5 ) ).toBe(
			'/wp-json/user-atlas/v1/users/5'
		);
	} );

	it( 'requires a user ID for the details URL', () => {
		vi.stubGlobal( 'window', {} );

		expect( () => new MainObject().userDetailsUrl() ).toThrow(
			'User ID is required'
		);
	} );
} );
