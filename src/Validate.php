<?php
/**
 * The Validation class provides utility methods for validating and ensuring the integrity of input parameters
 * related to Amazon S3 operations.
 *
 * This class offers methods to:
 *
 * - Validate AWS access key IDs and secret access keys to ensure they consist of alphanumeric characters.
 * - Validate S3 object keys to ensure they contain only safe and permissible characters.
 * - Validate S3 bucket names to ensure they follow Amazon S3's naming conventions.
 * - Validate S3 region strings to ensure they meet the required format.
 * - Check the validity of S3 endpoint URLs without a protocol.
 * - Validate duration values to ensure they are positive integers.
 * - Check if query strings contain only valid characters.
 *
 * By offering dedicated validation methods, this class ensures that Amazon S3-related data is consistently
 * and securely processed, reducing the risk of errors and vulnerabilities.
 *
 * @package     ArrayPress\Utils\S3
 * @subpackage  Validation
 * @copyright   Copyright (c) 2023, ArrayPress Limited
 * @license     GPL2+
 * @since       1.0.0
 * @author      David Sherlock
 * @description Provides utility methods for validating Amazon S3-related parameters.
 */

namespace ArrayPress\Utils\S3;

use Exception;

/**
 * Check if the class `Validate` is defined, and if not, define it.
 */
if ( ! class_exists( __NAMESPACE__ . '\\Validate' ) ) :

	/**
	 * Validation
	 *
	 * Offers utility methods for validating and ensuring the integrity of input parameters associated with S3 operations.
	 */
	class Validate {

		/**
		 * Check if a value is valid using a specified validation method.
		 *
		 * @param mixed  $value       The value to validate.
		 * @param string $method_name The name of the validation method to call.
		 *
		 * @return bool True if the value is valid according to the specified method, false otherwise.
		 */
		public static function is_valid( $value, string $method_name ): bool {
			if ( method_exists( self::class, $method_name ) ) {
				try {
					// Call the specified validation method
					self::$method_name( $value );

					return true; // Return true if validation passes
				} catch ( Exception $e ) {
					// Catch any exceptions and return false
					return false;
				}
			} else {
				// If the method doesn't exist, return false
				return false;
			}
		}

		/**
		 * Validate AWS access key ID.
		 *
		 * @param string $key The AWS access key ID to validate.
		 *
		 * @return bool True if valid, false otherwise.
		 * @throws Exception If the access key ID is invalid.
		 */
		public static function access_key( string $key ): bool {
			if ( ! preg_match( '/^[A-Za-z0-9]+$/', $key ) ) {
				throw new Exception( "Invalid AWS access key ID characters. It should be alphanumeric." );
			}

			return true;
		}

		/**
		 * Validate AWS secret access key.
		 *
		 * @param string $key The AWS secret access key to validate.
		 *
		 * @return bool True if valid, false otherwise.
		 * @throws Exception If the secret access key is invalid.
		 */
		public static function secret_key( string $key ): bool {
			if ( ! preg_match( '/^[A-Za-z0-9]+$/', $key ) ) {
				throw new Exception( "Invalid AWS secret access key characters. It should be alphanumeric." );
			}

			return true;
		}

		/**
		 * Validate the S3 object key to ensure it contains safe and permissible characters.
		 *
		 * Example:
		 * Input: "my_folder/my_file.txt*"
		 * Output: false
		 *
		 * @param string $key The S3 object key to validate.
		 *
		 * @throws Exception If the key is invalid.
		 */
		public static function object_key( string $key ): bool {
			if ( ! preg_match( '/^[a-zA-Z0-9\-_\.\/]*$/', $key ) ) {
				throw new Exception( "Invalid S3 object key. Only alphanumeric characters, hyphens, underscores, dots, and slashes are allowed." );
			}

			return true;
		}

		/**
		 * Validate if the provided bucket name conforms to S3's naming conventions.
		 *
		 * Example:
		 * Input: "my.bucket-123"
		 * Output: true
		 *
		 * @param string $bucket The bucket name to validate.
		 *
		 * @return bool True if the bucket name is valid.
		 * @throws Exception If the bucket name is invalid.
		 *
		 */
		public static function bucket( string $bucket ): bool {
			// Check length
			$length = strlen( $bucket );
			if ( $length < 3 || $length > 63 ) {
				throw new Exception( "Invalid bucket name length. It should be between 3 and 63 characters." );
			}

			// Check characters
			if ( ! preg_match( '/^[a-z0-9\-\.]+$/', $bucket ) ) {
				throw new Exception( "Invalid bucket name characters. Only lowercase letters, numbers, hyphens, and dots are allowed." );
			}

			return true;
		}

		/**
		 * Check if a string represents a valid Amazon S3 region.
		 *
		 * Example:
		 * Input: "us-west-1"
		 * Output: true
		 *
		 * @param string $region The S3 region string to check.
		 *
		 * @return bool True if the region is valid.
		 * @throws Exception If the region is invalid.
		 */
		public static function region( string $region ): bool {
			if ( ! preg_match( '/^[a-z0-9\-]+$/', $region ) ) {
				throw new Exception( "Invalid S3 region. It should contain only lowercase letters, numbers, and hyphens." );
			}

			return true;
		}

		/**
		 * Check if the endpoint is a valid domain name without a protocol.
		 *
		 * Example:
		 * Input: "https://my.endpoint.com/"
		 * Output: true (valid)
		 *
		 * @param string $endpoint The endpoint to check.
		 *
		 * @throws Exception If the endpoint is invalid.
		 */
		public static function endpoint( string $endpoint ): bool {
			// Remove any protocol prefixes
			$sanitized = preg_replace( '#^https?://#', '', rtrim( $endpoint, '/' ) );

			// Validate URL format
			if ( filter_var( 'https://' . $sanitized, FILTER_VALIDATE_URL ) === false ) {
				throw new Exception( "Invalid endpoint format. It should be a valid URL." );
			}

			// Ensure that the endpoint has a valid TLD
			if ( ! preg_match( '/\.[a-z]{2,}(?:\.[a-z]{2,})?$/', $sanitized ) ) {
				throw new Exception( "Invalid top-level domain in the endpoint." );
			}

			// Finally, strip any invalid characters
			$sanitized = preg_replace( '/[^a-zA-Z0-9\-\.]/', '', $sanitized );

			// Check if the sanitized endpoint is empty
			if ( empty( $sanitized ) ) {
				throw new Exception( "Invalid endpoint. It should not be empty." );
			}

			return true;
		}

		/**
		 * Validate if a value is a valid duration (positive integer representing a duration).
		 *
		 * @param mixed $duration The value to check.
		 *
		 * @return bool True if the duration is valid.
		 * @throws Exception If the duration is invalid.
		 *
		 */
		public static function duration( $duration ): bool {
			if ( ! is_int( $duration ) || $duration <= 0 ) {
				throw new Exception( "Invalid duration value. It should be a positive integer representing a duration." );
			}

			return true;
		}

		/**
		 * Check if the query string contains only valid characters.
		 *
		 * @param string $query_string The query string to check.
		 *
		 * @return bool True if the query string is valid.
		 * @throws Exception If the query string is invalid.
		 *
		 */
		public static function extra_query_string( string $query_string ): bool {
			if ( ! preg_match( '/^[a-zA-Z0-9\-_=&]*$/', $query_string ) ) {
				throw new Exception( "Invalid query string characters." );
			}

			return true;
		}

	}

endif;