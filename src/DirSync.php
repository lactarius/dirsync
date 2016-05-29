<?php

namespace DirSync;


/**
 * Class DirSync
 *
 * @author Petr Blazicek 2016
 */
class DirSync implements IDirSync
{

	// system related
	const SYSTEM_ROOT = '/'; // System root - may vary
	const ROOT_KEY = '__root__'; // Root index
	// path related
	const CHAR_TILDA = '~';
	const CHAR_DOT = '.';
	const DIR_DOTS = [ '.', '..' ];
	// controls
	const CONTROLS = [ '+', '&', '#' ];
	const CTRL_WRITEABLE = '+';
	const CTRL_SYMLINK = '&';
	const CTRL_COMMAND = '#';


	// variables

	/** @var string Root (target) directory */
	protected $root;

	/** @var string JSON input */
	protected $json;

	/** @var array JSON decoded and parsed to associated array */
	protected $struct;


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
		$this->root = $this->checkDir( $path );

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
		$this->struct = $this->parseInput( $json );
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
		$path = $this->parsePath( $filePath );
		if ( !file_exists( $path ) ) {
			throw new DSException( sprintf( 'File "%s" not found.', $path ) );
		}

		$json = file_get_contents( $path );
		$this->struct = $this->parseInput( $json );
		$this->json = $json;

		return $this;
	}


	/**
	 * Decoded structure array getter
	 * 
	 * @return array
	 */
	public function getStruct()
	{
		return $this->struct;
	}


	public function sync( $options = null )
	{
		
	}


	// internal routines

	/**
	 * Analyzes intup JSON
	 * 
	 * @param string $json
	 * @return array
	 */
	protected function parseInput( $json )
	{
		$assoc = $this->convertJson( $json );
		return $this->parseAssoc( $assoc );
	}


	/**
	 * Tries to decode input string
	 * 
	 * @param string $json
	 * @return array
	 * @throws DSException
	 */
	protected function convertJson( $json )
	{
		$assoc = json_decode( $json, TRUE );
		if ( !is_array( $assoc ) ) {
			throw new DSException( 'The input string contains something completely different than JSON data.' );
		}

		return $assoc;
	}


	/**
	 * Analyzes and extracts control characters from
	 * decoded array
	 * 
	 * @param array $assoc
	 * @return array
	 */
	protected function parseAssoc( $assoc )
	{
		$struct = [ ];
		foreach ( $assoc as $key => $value ) {

			$ctrl = substr( $key, 0, 1 );
			if ( in_array( $ctrl, self::CONTROLS ) ) {
				$node = substr( $key, 1 );
			} else {
				$node = $key;
				$ctrl = NULL;
			}

			$struct[ $node ][ 0 ] = is_array( $value ) ? $this->parseAssoc( $value ) : $value;
			$struct[ $node ][ 1 ] = $ctrl;
		}

		return $struct;
	}


	protected function processInput( $path, $struct )
	{
		$dir = self::scandirExt( $path );
		$cwd = getcwd();
		chdir( $path );

		foreach ( $struct as $key => $value ) {

			if ( !in_array( $key, $dir ) ) {
				
			}
		}


		chdir( $cwd );
	}


	/**
	 * Directory listing without the dots ( . .. )
	 * 
	 * @param string $path
	 * @return array
	 */
	protected function scandirExt( $path )
	{
		return array_diff( scandir( $path ), self::DIR_DOTS );
	}


	/**
	 * Looks for alternative root places
	 * 
	 * @return string
	 */
	protected function lookupRoot()
	{
		if ( !empty( $this->struct[ self::ROOT_KEY ] ) ) { // input data contains the root path
			$path = $this->checkDir( $this->struct[ self::ROOT_KEY ][ 0 ] );
			unset( $this->struct[ self::ROOT_KEY ] ); // remove root info from input data
		} elseif ( defined( self::ROOT_KEY ) ) {  // root is defined somewhere
			$path = $this->checkDir( constant( self::ROOT_KEY ) );
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
	protected function checkDir( $path )
	{
		$path = $this->parsePath( $path );
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
	protected function parsePath( $path )
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


}
