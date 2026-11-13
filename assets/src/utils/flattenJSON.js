export default function flattenJSON( obj, parentKey = '' ) {
	let result = [];
	for ( const key in obj ) {
		if ( ! obj.hasOwnProperty( key ) ) {
			continue;
		}

		const newKey = parentKey ? `${ key }` : key;
		if ( typeof obj[ key ] === 'object' && obj[ key ] !== null ) {
			result = result.concat( flattenJSON( obj[ key ], newKey ) );
		} else {
			result.push( [
				newKey,
				Array.isArray( obj[ key ] )
					? obj[ key ].join( ', ' )
					: String( obj[ key ] ),
			] );
		}
	}
	return result;
}
