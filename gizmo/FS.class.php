<?php
/**
 * @package WebGizmo
 * @author Alexander R B Whillas
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 **/

// // // // // // // // // // // // // // // // //
// Basic includes
// // // // // // // // // // // // // // // // //

/**
 * Global variable configuration constants
 */
include 'config.php';

/**
 * HTML tag rendering functions library.
 */
include 'includes/html.php';

/**
 * Handy debugging functions
 */
include 'includes/debug.php';

/**
 * Error handling setup
 */
include 'includes/error_handling.php';




// // // // // // // // // // // // // // // // //
// Boring stuff...
// // // // // // // // // // // // // // // // //

// Check PHP version is high enough
if(version_compare(PHP_VERSION, '5.0.0') <= 0)
	trigger_error('You need at least PHP 5.0 to use Web Gizmo! You have version '.PHP_VERSION, E_ERROR);

// Make sure our Classes get included automatically
set_include_path( implode(PATH_SEPARATOR, array(get_include_path(), GIZMO_PATH, GIZMO_PATH.'/handlers', PLUGINS_PATH)) );
spl_autoload_extensions('.class.php');
spl_autoload_register();	// Use default autoload implementation coz its fast


/**
 * FS class
 * 
 * Encapsulates all the File System (FS) details for Gizmo.
 * It was called FS as Gizmo was original called FS-CMS.
 * Is a Singleton, with the static get() method. 
 *
 * @package	WebGizmo
 * @todo Put some more Exception throwing + handling in
 */
class FS
{
	/**
	 * Stactic copy of self. Used in the Singleton design pattern.
	 * @var FS
	 */
	private static $_instance;
	
	/**
	 * List of files to ignore
	 * Would be nice if arrays could be in Class Constants :(
	 * @var Array
	 */
	private $EXCLUDE_FILES = array('.', '..', '.DS_Store', 'Thumbs.db');
	
	/**
	 * Current absolute content path
	 * @var Path
	 * @see FS::currentPath()
	 */
	private $path;
	
	/**
	 * @var Path
	 */
	private $contentRoot;
	
	/**
	 * @var	Path
	 */
	private $templatesRoot;

	/**
	 * @var	Path
	 */
	private $pluginsRoot;
	
	/**
	 * @var	Array	Array of content variables to be added to the Template
	 * 		before rendering.
	 * @see FS::add()
	 */
	public $content;
	
	/**
	 * List of file references. 
	 * 
	 * @var	Array	The Key is the path and the value is the MIME type.
	 */
	private $fileReferences = array();
	
	/**
	 * @param	String	Content path override, should be the absolute path to 
	 * 		the content folder on the server. Default is WEB_ROOT + CONTENT_DIR
	 * @param	String	Templates path override, should be the absolute path to 
	 * 		the templates root folder on the server. Default is WEB_ROOT + TEMPLATES_DIR
	 */
	private function __construct($content_path = '', $templates_path = '', $plugins_path = '')
	{
		$content_path 	= (empty($content_path)) 	? WEB_ROOT . CONTENT_DIR					: $content_path;
		$content_path 	= (MULTI_LINGUAL) 			? $content_path . '/'.$this->getLanguage() 	: $content_path;

		$templates_path = (empty($templates_path))	? WEB_ROOT . TEMPLATES_DIR					: $templates_path;
		$plugins_path = (empty($plugins_path))		? PLUGINS_PATH								: $plugins_path;
		
		// __set() should turn these into Path objects and store them in $this->_paths. uber nur PHP5.3 
		$this->contentRoot = new Path($content_path, true);

		$this->templatesRoot = new Path($templates_path, true);

		$this->pluginsRoot = new Path($plugins_path	, true);

		// Current real Content path

		$VPath = FS::getPath();

		if(file_exists($content_path . $VPath))
		{
			$this->path = new Path($content_path . $VPath);
		}
		else
		{
			// Assume its a Virtual path so try to convert it
			if($real = $this->virtualToReal($VPath, $content_path))
			{
				$this->path = new Path($content_path . $real);
			}
			else
			{
				// if nothing matches then 404 :( 
				header("HTTP/1.0 404 Not Found");
				die("Could not find path $VPath");
			}	
		}

		// If we're on the DEFAULT_START path then treat it as the new root path	
		if(!FS::getPath()->get() && DEFAULT_START != '/')
		{
			$real_default_start = FS::virtualToReal(DEFAULT_START, $content_path);
			
			$this->path = new Path($content_path . $real_default_start, true);
		}
		
		$this->content = array('head' => '', 'foot' => '');
	}

	/**
	 * Singleton constructor/factory method.
	 *
	 * @return 	FS	Global instance of the FS object
	 **/
	public static function get($content_path = '', $templates_path = '', $plugins_path = '') 
	{		
        if (!self::$_instance)
        {
            self::$_instance = new FS($content_path, $templates_path, $plugins_path);
        }
        return self::$_instance;
	}
	
	/**
	 * So when this object is used as a function it returns the current content Path.
	 * Handy in the Savant templates where an instance of FS is already instantiated
	 * 
	 * @see FS::parse()
	 * @return 	Path	Array of FileContent
	 */
	public function __invoke() 
	{
		return $this->get();
	}


	/**
	 * @return Path object
	 */
	public function templatePath($format = 'html')
	{
		return new Path($this->templatesRoot->get() .'/'. $format . THEME_DIR);
	}
	
	/**
	 * Getter for the content path
	 *
	 * @return String
	 **/
	public function currentPath()
	{
		return $this->path;
	}
	
	/**
	 * Getter for the content root/base path
	 *
	 * @return String
	 **/
	public function contentRoot()
	{
		return $this->contentRoot;
	}
	
	/**
	 * Getter for the root path to the content directory
	 *
	 * @return String
	 **/
	public function templatesRoot()
	{
		return $this->templatesRoot;
	}
	
	/**
	 * Getter for the root path to the content directory
	 *
	 * @return String
	 **/
	public function pluginsRoot()
	{
		return $this->pluginsRoot;
	}	
	
	/**
	 * Process a HTTP request returning the requested format.
	 *
	 * @return String
	 * @todo Send MIME in the header. Need a standard format/extension to MIME lookup.
	 **/
	public function http($format = 'html', $version = HTML_DEFAULT_VERSION)
	{
		if($this->path->is())
		{
			// Get the Layoutor for the format
			if($Layout = GizFormat::make($format, $version))
			{
				// Send HTTP headers
				// ???

				// output the renered content. 
				echo $Layout->render();
			}
			else
				trigger_error('Could not find render for given format: '.$format);
		}
	}
	
	/**
	 * mod_rewrite aware i.e. checks the REWRITE_URLS global
	 * 
	 * @param	$dir	String|SplFileInfo
	 * @return 	String	the correct URL for links given a virtual path.
	 * @deprecated Use Path->url() instead.
	 * @see REWRITE_URLS
	 */
	static public function getURL($dir)
	{
		if($dir instanceof FSObject)
		{
			// Figure out its URL by subtracking the web root
			if($dir->isDir())
				$from = WEB_ROOT . CONTENT_DIR;
			else
				$from = WEB_ROOT;
			
			$dir = Path::open($from)->from($dir->getPathname());
		}
		
		return (REWRITE_URLS) 	// If we're using Apaches mod_rewrite...
			? ((BASE_URL_PATH)	// ... append the BASE_URL_PATH to the $dir...
				? ((BASE_URL_PATH == '/')	// ...only if BASE_URL_PATH is not '/'
					? ''
					: BASE_URL_PATH.'/')
				: '') . "$dir" // force objects __toString()
			: BASE_URL_PATH . "?path=$dir";	// ... ugly URLs then.
	}

	/**
	 * Get the CGI virtual 'path' variable value
	 *
	 * @return String	The current virtual path (relative to the base URL)
	 * @todo make this a gett'r for a Singleton
	 * @todo rename to reflect that its a virtual URL _not_ a system path
	 */
	public static function getPath()
	{
		return (isset($_GET['path'])) 
			? new Path($_GET['path'])
			: new Path('/');
	}
	
	/**
	 * @return 	String	Value of the CGI "path" variable i.e. the "virtual path"
	 * @deprecated Use Path->url() instead.
	 */
	public function vpath()
	{
		return $this->getPath()->get();
	}

	/**
	 * Convert an Virtual path to a Real path
	 *
	 * @param	Path
	 * @param	String	Base path to prefix to the given Virtual path when 
	 * 					looking for sub folders
	 *
	 * @return Mixed	The real path on success, FALSE if it could no be found
	 * @todo Clean this up. Make recursive.
	 **/
	public function virtualToReal($Virtual, $base_path)
	{
		if(!$Virtual instanceof Path)
			$Virtual = new Path($Virtual);
		
		// Virtual so have to look for it...
		$current = '';
		foreach($Virtual->parts() as $part)
		{
			if(!empty($part))
				if(file_exists($base_path . '/'. $part))
				{
					$current .= '/'.$part;
				}
				else
				{
					// Get a list of all the folders in the $current path
					
					$found = false;						
					
					// Compare their clean names to the given part
					foreach(scandir($base_path . $current) as $file)
					{
						if(!in_array($file, array('.', '..')) AND FSObject::cleanName($file) == $part)
						{
							// if we have a match then add the real name to the $current path.
							$FSObject = FSObject::make($base_path . $current .'/'. $file);
							$current .= '/'.$FSObject->getFilename();
							$found = true;
							break;
						}
					}
				
					// if nothing matches. 
					if(!$found)
					{
						return false;
					}
				}
		}
		return $current;
	}
	
	/**
	 * Condense a Real path to a human friendly Virtual path i.e. all parts 
	 * consisting of their "clean names".
	 * 
	 * Doesn't check the path exists, just processes the paths parts.
	 * 
	 * @param	Path
	 *
	 * @return String	Virtual path from the Real path.
	 **/
	public static function realToVirtual($real)
	{
		if($real instanceof Path)
		{
			$out = array();

			foreach($real->parts() as $part)
			{
				if(!empty($part))
				{
					// FS::rbasename($part, FSObject::getExtension($part)));
					$out[] = FSObject::cleanName($part);
				}
			}
			return Path::open($out);
		}
		else
			trigger_error('Need a Path to convert to virtual path, given: '.what($real));
	}
	
	/**
	 * Builds a list of HTML links to the top level folders in the Content folder
	 * 
	 * @param	Integer	Number of nested levels deep the menu should show.
	 * @param	Boolean	Output nested lists of links for all the menu items. False means just the current path.
	 * 
	 * @return 	Array	List of HTML anchor tags to the top level folders.
	 */
	function _old_menu()
	{
		$out = array();

		foreach($this->contentRoot()->query('folders.has.^[^_]') as $Dir)
		{
			$class = ( $this->currentPath()->url() == $Dir->getURL() )? 'Selected': '';
			
			$out[$Dir->getCleanName()] = $Dir->htmlLink(null, $class);
		}

		return $out;
	}
	
	/**
	 * Return a multi-layered HTML list of HTML lists.
	 * 
	 * @param	Integer	From the $root, the depth that should be shown.
	 * @param	Boolean	Should the current path (leaf) be expanded and shown?
	 * @param	Path	The root the menu should start from.
	 *
	 * @return String
	 **/
	public function menu($depth = 1, $showLeaf = false, Path $root = null)
	{
		$out = array();
		
		if($root == null)
			$root = $this->contentRoot();
		
		if($showLeaf)
			$tree = FS::getMenu($root, $depth, $this->currentPath());
		else
			$tree = FS::getMenu($root, $depth);
		
		return $tree;
	}

	/**
	 * Recursively build a HTML list of links representing the folder hierarchy.
	 * 
	 * @param	Path	Path to get the list of folders from.
	 * @param	integer	Depth to decent into. building sub-lists
	 * @param	Path	The leaf branch which will be show all the way despite the given $depth
	 * @param	String	Regular expression to filter folder names with. Defaults to filtering out folders starting with an underscore '_'
	 * 
	 * @return 	String	Nested HTML Lists of links (anchors).
	 */
	public function getMenu(Path $path, $depth = 0, Path $leaf = null, $regEx = '^[^_]')
	{
		$out = array();
		
		if($depth > 0 OR (!is_null($leaf) AND $leaf->isChildOf($path)))
			foreach($path->query('folders')->name($regEx) as $Dir)
			{
				$class = ( $this->currentPath()->isChildOf($Dir->getPath()) )? 'Selected': '';
				$out[] = $Dir->htmlLink(null, $class) . FS::getMenu($Dir->getPath(), $depth - 1, $leaf, $regEx);
			}
		
		return (count($out) > 0)? ul($out, 'Menu'): '';
	}
	
	/**
	 * Get the Content tree starting at the given virtual path
	 * 
	 * @param	String	Virtual content path to start from. Should begin with a '/'.
	 * @param	Boolean	Only return Directories, ignore files?
	 * @return 	Array	Multidimensional array representing the directory structure.
	 **/
	public function getContentTree($virtual_root = '', $depth = 1, $only_dirs = true)
	{
		return FS::getDirectoryTree($this->contentRoot().$virtual_root, $depth, $only_dirs);
	}
	
	
	// / / / / / / / / / / / / / / / / / / / / / / / / /
	// Static funcitons
	// / / / / / / / / / / / / / / / / / / / / / / / / /
	
	/**
	 * 'bare bones' recursive method to extract directories and files
	 * 
	 * @param	String	Directory to start scanning from
	 * @param	Integer	How many levels deep should we go?
	 * @param	Boolean	Only return Directories, ignore files?
	 * @return 	Array	
	 * @author	Dustin
	 * @see http://de2.php.net/manual/en/function.scandir.php#88006
	 **/
	public static function getDirectoryTree($outerDir, $depth = 1, $only_dirs = true)
	{
		// remove .. & .
		$dirs = array_diff( scandir( $outerDir ), Array( '.', '..' ) );
		
		$out = Array();
		
		foreach( $dirs as $d )
			if( is_dir($outerDir.'/'.$d) and $depth )
			{
				$out[ $outerDir.'/'.$d ] = FS::getDirectoryTree( $outerDir.'/'.$d, $depth - 1);
			} 
			elseif(!$only_dirs)
			{
				$out[ $outerDir.'/'.$d ] = $d;
			}
		
		return $out;		
	}

	/**
	 * Get a list of language codes the content is available in.
	 * Look at the top level of the Content folder and assume 
	 * the first level is a list of language codes.
	 *
	 * @return Array
	 **/
	function getContentLanaguages()
	{
		return array_keys(FS::getDirectoryTree(WEB_ROOT.CONTENT_DIR));
	}
	
	/**
	 * Determine the Language of the content
	 *
	 * @return String	ISO639 Two-letter primary language code
	 * @link http://www.w3.org/TR/REC-html40/struct/dirlang.html
	 **/
	function getLanguage()
	{
		$available = $this->getContentLanaguages();
		
		// If 'lang' has been set via GET
		if(isset($_GET['lang']) and in_array($_GET['lang'], $available))
		{
			setcookie('lang', $_GET['lang']);

			return $_GET['lang'];
		}
		
		// Has the language been set in a cookie?
		if(isset($_COOKIE['lang']) and in_array($_COOKIE['lang'], $available))
		{
			return $_COOKIE['lang'];
		}
		
		// Try and sniff the user agent langauge 
		if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) )
		{
			// example: ' fr-ch;q=0.3, da, en-us;q=0.8, en;q=0.5, fr;q=0.3' or 'en-US,en;q=0.8'
			foreach(explode(',', strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"] )) as $code)
			{
				$lang = substr( trim($code), 0, 2 ); // take just the 1st two char's
				
				if(in_array($lang, $available))
				{
					return $lang;
				}	
			}
		}
		
		// Is there a default set?
		if(DEFAULT_LANGUAGE)
		{
			return DEFAULT_LANGUAGE;
		}
		
		// No idea so just choose the last one available
		return end($available);
	}
	
	/**
	 * Make CSS class names specific to the current path for use in the HTML 
	 * <body> tag for example:
	 * <code>
	 * /content/some/path/to/current/content => SomePathToCurrentContent
	 * </code>
	 * 
	 * @return 	String
	 * @todo MOve this to a HTML Format render
	 */
	public function pathCSS($path = null)
	{		
		$path = (is_null($path))
			? $this->getPath()->get()
			: $path;
				
		// Build Classes based on Path parts names
		if($path)
		{
			foreach(explode('/', $path) as $folder)
			{
				if(!empty($folder))
					$out[] = str_replace(
						' ', '', 
						ucwords(
							preg_replace('/[^0-9A-Za-z]/', ' ', FSObject::clean($folder))
						)
					);
			}
			
			$out = implode(' ', $out);

			// Figureout depth

			$out .= ' Depth'.(count(explode('/', $path)) - 1);
			
			return $out;
		}
		else
			return 'Top';	// Homepage
	}
	
	/**
	 * Add content to the $var variable.
	 * Used to add HTML to the header/footer of the template.
	 * These variables will be made available to the template as $head or $foot etc.
	 * The value is 
	 * 
	 * @param	String	HTML to add to the variable.
	 * @param	String	Name of the variable. Usually 'head' or 'foot' for 
	 * 					standard HTML doc header or footer but can be anything.
	 * @param	Boolean	Add to the beginning of the header includes (i.e. instead of appended to the end)?
	 **/
	public static function add($html, $var = 'head', $prepend = false)
	{
		$fs = FS::get();

		if(isset($fs->content[$var]))
			if($prepend)
			{
				$fs->content[$var] = $html."\n".$fs->content[$var];
			}
			else
			{
				$fs->content[$var] .= $html;
			}
		else
			$fs->content[$var] = $html;
	}
	
	/**
	 * Add a reference to an external file.
	 * 
	 * This is typically a CSS or JavaScript file. The type of reference (i.e. 
	 * for HTML it will be ether a <link /> or <script></script> tag) will be 
	 * determined from the file extension is the second argument is 'auto' (its
	 * default value), otherwise this can be forced by passing a MIME type for 
	 * the file.
	 *
	 * @return boolean	TRUE if all went well, false if extension was not recognised.
	 **/
	public function addRef($path, $mime = 'auto')
	{
		$out = TRUE;
		
		if($mime = 'auto')
		{
			switch(FSObject::getEnd($path))
			{
				case 'js':
					$mime = 'text/javascript';
					break;
				
				case 'css':
					$mime = 'text/css';
					break;
				
				default:
					$mime = 'text/plain';
					$out = FALSE;
			}
		}
		
		$this->fileReferences[$path] = $mime;
		
		return $out;
	}
	
	/**
	 * Getter for the list of external file references
	 * 
	 * @see FS::fileReferences
	 *
	 * @return void
	 **/
	public function fileRefs()
	{
		return $this->fileReferences;
	}
}

