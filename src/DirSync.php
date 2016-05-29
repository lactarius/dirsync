<?php

namespace DirSync;


/**
 * Class DirSync
 *
 * @author Petr Blazicek 2016
 */
class DirSync implements IDirSync
{

	const SYSTEM_ROOT = '/'; // System root - may vary
	const ROOT_KEY = '__root__'; // Root index
	const CHAR_TILDA = '~';
	const CHAR_DOT = '.';


	// variables

	/** @var string Root (target) directory */
	protected $root;

	/** @var string JSON input */
	protected $json;

	/** @var array JSON decoded to associated array */
	protected $assoc;


	public function __construct()
	{
		
	}

	/**
	 * Root (target) directory getter
	 * 
	 * @return string
	 */
	public function getRootDir()
	{
		if ( !$this->root ) {
			$this->root = $this->lookupRoot();
		}

		return $this->root;
	}

	/**
	 * Root (target) directory setter
	 * 
	 * @param string $path
	 * @return self (fluent interface)
	 */
	public function setRootDir( $path )
	{
		$this->root = self::checkDir( $path );

		return $this;
	}

	/**
	 * Input JSON string getter
	 * 
	 * @return string
	 */
	public function getJsonInput()
	{
		return $this->json;
	}

	/**
	 * Input JSON string setter
	 * 
	 * @param string $json
	 * @return self (fluent interface)
	 */
	public function setJsonInput( $json )
	{
		$this->assoc = self::convertJson( $json );
		$this->json = $json;

		return $this;
	}

	/**
	 * Reads input json from file
	 * 
	 * @param string $filePath
	 * @return self (fluent interface)
	 * @throws DSException
	 */
	public function fromFile( $filePath )
	{
		$path = self::parsePath( $filePath );
		if ( !file_exists( $path ) ) {
			throw new DSException( sprintf( 'File "%s" not found.', $path ) );
		}

		$json = file_get_contents( $path );
		$this->assoc = self::convertJson( $json );
		$this->json = $json;

		return $this;
	}

	/**
	 * Decoded association array getter
	 * 
	 * @return array
	 */
	public function getAssocData()
	{
		return $this->assoc;
	}

	public function sync( $options = null )
	{
		
	}

	// internal routines

	/**
	 * Looks for alternative root places
	 * 
	 * @return string
	 */
	protected function lookupRoot()
	{
		if ( !empty( $this->assoc[ self::ROOT_KEY ] ) ) { // input data contains the root path
			$path = self::checkDir( $this->assoc[ self::ROOT_KEY ] );
			unset( $this->assoc[ self::ROOT_KEY ] ); // remove root info from input data
		} elseif ( defined( self::ROOT_KEY ) ) {  // root is defined somewhere
			$path = self::checkDir( constant( self::ROOT_KEY ) );
		} else {
			$path = self::SYSTEM_ROOT; // Gob be with you
		}

		return $path;
	}

	/**
	 * Checks directory existence,
	 * tries to create it if necessary.
	 * 
	 * @param string $path
	 * @return string
	 * @throws DSException
	 */
	protected static function checkDir( $path )
	{
		$path = self::parsePath( $path );
		if ( !is_dir( $path ) && !mkdir( $path ) ) {
			throw new DSException( sprintf( 'Root directory "%s" doesn\'t exist nor can\'t be created.',
								   $path ) );
		}

		return $path;
	}

	/**
	 * Analyzes path for shortcuts
	 * ~/ -> home directory
	 * ./ -> current directory
	 * 
	 * @param string $path
	 * @return string
	 */
	protected static function parsePath( $path )
	{
		// No comments, please... ;-)
		$path = trim( $path );
		$first = substr( $path, 0, 1 );
		$prefix = '';
		switch ( $first ) {
			case self::CHAR_TILDA:
				$prefix = getenv( 'HOME' );
				break;
			case self::CHAR_DOT:
				$prefix = getcwd();
		}

		return $prefix ? $prefix . substr( $path, 1 ) : $path;
	}

	/**
	 * Tries to decode input string
	 * 
	 * @param string $json
	 * @return array
	 * @throws DSException
	 */
	protected static function convertJson( $json )
	{
		$assoc = json_decode( $json, TRUE );
		if ( !is_array( $assoc ) ) {
			throw new DSException( 'The input string contains something completely different than JSON data.' );
		}

		return $assoc;
	}

}
