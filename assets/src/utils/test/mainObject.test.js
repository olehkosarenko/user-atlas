import { afterEach, describe, expect, it, vi } from 'vitest';
import MainObject from '../mainObject';

describe( 'MainObject', () => {
	afterEach( () => {
		vi.unstubAllGlobals();
	} );

	it( 'reads settings from global_user_atlas', () => {
		vi.stubGlobal( 'window', {
			global_user_atlas: {
				ajaxUrl: 'https://example.com/wp-admin/admin-ajax.php',
				nonce: 'abc123',
				title: 'Team',
				isUserAdmin: true,
			},
		} );

		const mainObject = new MainObject();

		expect( mainObject.ajaxUrl ).toBe(
			'https://example.com/wp-admin/admin-ajax.php'
		);
		expect( mainObject.nonce ).toBe( 'abc123' );
		expect( mainObject.title ).toBe( 'Team' );
		expect( mainObject.isUserAdmin ).toBe( true );
	} );

	it( 'falls back to defaults when global_user_atlas is missing', () => {
		vi.stubGlobal( 'window', {} );

		const mainObject = new MainObject();

		expect( mainObject.ajaxUrl ).toBe( '' );
		expect( mainObject.nonce ).toBe( '' );
		expect( mainObject.title ).toBe( 'User Atlas' );
		expect( mainObject.isUserAdmin ).toBe( false );
	} );

	it( 'builds the users list URL', () => {
		vi.stubGlobal( 'window', {
			global_user_atlas: {
				ajaxUrl: '/wp-admin/admin-ajax.php',
				nonce: 'abc123',
			},
		} );

		expect( new MainObject().usersListUrl() ).toBe(
			'/wp-admin/admin-ajax.php?action=user_atlas&nonce=abc123&cmd=users'
		);
	} );

	it( 'builds the user details URL', () => {
		vi.stubGlobal( 'window', {
			global_user_atlas: {
				ajaxUrl: '/wp-admin/admin-ajax.php',
				nonce: 'abc123',
			},
		} );

		expect( new MainObject().userDetailsUrl( 5 ) ).toBe(
			'/wp-admin/admin-ajax.php?action=user_atlas&nonce=abc123&cmd=user&id=5'
		);
	} );

	it( 'requires a user ID for the details URL', () => {
		vi.stubGlobal( 'window', {} );

		expect( () => new MainObject().userDetailsUrl() ).toThrow(
			'User ID is required'
		);
	} );
} );
