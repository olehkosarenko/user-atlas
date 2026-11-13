const isObject = ( value ) => typeof value === 'object' && value !== null;

export default function flattenJSON( obj, parentKey = '' ) {
	let result = [];
	for ( const key in obj ) {
		if ( ! obj.hasOwnProperty( key ) ) {
			continue;
		}

		const newKey = parentKey ? `${ parentKey }.${ key }` : key;
		const value = obj[ key ];

		if ( Array.isArray( value ) && ! value.some( isObject ) ) {
			result.push( [ newKey, value.join( ', ' ) ] );
		} else if ( isObject( value ) ) {
			result = result.concat( flattenJSON( value, newKey ) );
		} else {
			result.push( [ newKey, String( value ) ] );
		}
	}
	return result;
}
