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
} );
