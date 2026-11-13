import flattenJSON from '../flattenJSON';

describe( 'flattenJSON', () => {
	it( 'returns an empty list for an empty object', () => {
		expect( flattenJSON( {} ) ).toEqual( [] );
	} );

	it( 'turns a flat object into key and value pairs', () => {
		expect( flattenJSON( { id: 1, name: 'Leanne Graham' } ) ).toEqual( [
			[ 'id', '1' ],
			[ 'name', 'Leanne Graham' ],
		] );
	} );

	it( 'converts scalar values to strings', () => {
		expect(
			flattenJSON( { age: 30, active: true, deleted: false, note: null } )
		).toEqual( [
			[ 'age', '30' ],
			[ 'active', 'true' ],
			[ 'deleted', 'false' ],
			[ 'note', 'null' ],
		] );
	} );

	it( 'skips inherited properties', () => {
		const user = Object.create( { inherited: 'value' } );
		user.name = 'Ervin Howell';

		expect( flattenJSON( user ) ).toEqual( [ [ 'name', 'Ervin Howell' ] ] );
	} );

	it( 'keeps parent keys for nested objects', () => {
		const user = {
			name: 'Leanne Graham',
			address: {
				street: 'Kulas Light',
				geo: { lat: '-37.3159', lng: '81.1496' },
			},
			company: { name: 'Romaguera-Crona' },
		};

		expect( flattenJSON( user ) ).toEqual( [
			[ 'name', 'Leanne Graham' ],
			[ 'address.street', 'Kulas Light' ],
			[ 'address.geo.lat', '-37.3159' ],
			[ 'address.geo.lng', '81.1496' ],
			[ 'company.name', 'Romaguera-Crona' ],
		] );
	} );

	it( 'does not produce duplicate keys', () => {
		const keys = flattenJSON( {
			name: 'Leanne Graham',
			company: { name: 'Romaguera-Crona' },
		} ).map( ( [ key ] ) => key );

		expect( new Set( keys ).size ).toBe( keys.length );
	} );

	it( 'joins arrays of plain values into one string', () => {
		expect( flattenJSON( { tags: [ 'admin', 'editor', 3 ] } ) ).toEqual( [
			[ 'tags', 'admin, editor, 3' ],
		] );
	} );

	it( 'flattens arrays of objects by index', () => {
		expect(
			flattenJSON( { phones: [ { type: 'home', number: '123' } ] } )
		).toEqual( [
			[ 'phones.0.type', 'home' ],
			[ 'phones.0.number', '123' ],
		] );
	} );
} );
