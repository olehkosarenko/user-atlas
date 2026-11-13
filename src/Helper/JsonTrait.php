<?php
/**
 * The Json class
 *
 * @package WpApp\UserAtlas\Helper
 */

namespace WpApp\UserAtlas\Helper;

/**
 * The Json class
 */
trait JsonTrait {
	/**
	 * Get by key
	 *
	 * @param string $fileName The file name.
	 * @param string $key The key.
	 *
	 * @return array
	 */
	public function getByKey( string $fileName = '', string $key = '' ): array {
		$arr = $this->getContents( $fileName );

		return ! empty( $arr[ $key ] ) ? $arr[ $key ] : [];
	}

	/**
	 * Get contents
	 *
	 * @param string $file The file name.
	 *
	 * @return array
	 *
	 * phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	 */
	public function getContents( string $file = '' ): array {
		if ( empty( $file ) ) {
			return [];
		}

		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return [];
		}

		$data = file_get_contents( $file ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- Reads the bundled config.json, not a remote URL.
		$arr  = json_decode( $data, true );

		return is_array( $arr ) ? $arr : [];
	}
}
