<?php

namespace Rarst\Fragment_Cache;

/**
 * Abstract base class for implementation of fragment type handler.
 */
abstract class Fragment_Cache {

	static $in_callback = false;
	public $timeout;
	protected $type;

	/**
	 * Create object and set parameters from passed.
	 *
	 * @param array $args
	 */
	public function __construct( $args ) {

		$this->type    = $args['type'];
		$this->timeout = $args['timeout'];
	}

	/**
	 * Enable fragment handler.
	 */
	abstract public function enable();

	/**
	 * Disable fragment handler.
	 */
	abstract public function disable();

	/**
	 * Wrapper to retrieve data through TLC Transient.
	 *
	 * @param string $name
	 * @param array  $args
	 * @param mixed  $salt
	 *
	 * @return mixed
	 */
	public function fetch( $name, $args, $salt = '' ) {

		if ( self::$in_callback )
			return $this->callback( $name, $args );

		$salt      = maybe_serialize( $salt );
		$transient = tlc_transient( 'fragment-cache-' . $this->type . '-' . $name . $salt )
				->updates_with( array( $this, 'callback' ), array( $name, $args ) )
				->expires_in( $this->timeout );

		self::$in_callback = true;
		$output            = $transient->get();
		self::$in_callback = false;

		return $output;
	}

	/**
	 * Used to generate data to be cached.
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return string
	 */
	abstract public function callback( $name, $args );

	/**
	 * Get human-readable HTML comment with timestamp to append to cached fragment.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function get_comment( $name ) {

		return '<!-- ' . esc_html( $name ) . ' ' . esc_html( $this->type ) . ' cached on ' . date_i18n( DATE_RSS ) . ' -->';
	}
}