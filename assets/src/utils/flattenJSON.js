export default function flattenJSON( obj, parentKey = '' ) {
	let result = [];
	for ( let key in obj ) {
		if ( ! obj.hasOwnProperty( key ) ) {
			continue;
		}

		let newKey = parentKey ? `${ key }` : key;
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
