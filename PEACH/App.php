<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

require_once 'PEACH/Config.php';
require_once 'PEACH/Debug.php';
require_once 'PEACH/PEACH.php';
require_once 'HTML/Template/Flexy.php';

if(!defined('PEACH_APP_TEMPLATE_COMPILE_DIR')) {
	die('PEACH_APP_TEMPLATE_COMPILE_DIR not defined');
}

if(!defined('PEACH_APP_DIR')) {
	die("PEACH_APP_DIR not defined");
}

/**
* PEACH_App
*
* Note: this class is inherited by the modules, the modules are loaded into the session,
* this means reading the configuration for the app is only done once per session.
* It makes a big difference whether objects are loaded into the session or not, 
* keep this in mind while coding. 
*
* It should be possible to configure the module with $_GET vars and with a xml config file,
* or by defaults inside the class itself. $_GET vars have highest prio. get vars are set by
* setProperty(). to prevent abuse.
*
* @package PEACH
* @author Rob Halff <info@rhalff.com>
* @copyright Copyright (c) 2004 Rob Halff
* @version $Id: App.php,v 1.7 2004/06/11 19:19:19 rhalff Exp $
* @access public
*/

class PEACH_App extends PEACH {

    /*
     * The current classname 
     *
     * @var string 
     */
	var $className;

    /*
     * Output Object 
     *
     * @var object 
     */
	var $output;
    /*
     * Template Directory 
     *
     * @var string 
     */
	var $templateDir;
    /*
	 * Use configuration file or not, by default yes, override in App.
     *
     * @var boolean 
     */
	var $useConfig = true;
    /*
	 * whether to use authentication by default for the applications (can be overriden per App)
     *
     * @var boolean 
     */
	var $useAuth   = true;

	/* passed by PEACH_Site to us */
    /*
     * Which module file to use (deprecated) 
     *
     * @var string 
     */
	private $moduleFile;
    /*
     * Name of the current App 
     *
     * @var string 
     */
	private $modname;
    /*
     * Method/action to be called 
     *
     * @var string 
     */
	private $method;
    /*
     * Whether this is the current App 
     *
     * @var string 
     */
	private $isCurrent;
    /*
     * moddir (deprecated?) 
     *
     * @var string 
     */
	public  $moddir;
    /*
     * Appname ?? 
	 * must be overriden by extended class 
     *
     * @var string 
     */
	public $appname;

    /*
     * Flexy template object
     *
     * @var object 
     */
	var $tpl;

	final function init()
	{
		// get the moddir.. kinda risky to do it like this ?
		// e.g: PEACH_App_Poems_Manager
		$this->className = get_class($this);
		$this->moddir = PEACH_APP_DIR."/".$this->modname;

		// only setup the template object on create of the app object. so only once per session
		$this->initTemplate();

	}

	final function initTemplate()
	{

		PEACH_Debug::instance()->msg(__METHOD__, "initTemplate Called for {$this->modname}::{$this->method}()");
		$this->templateDir = PEACH_APP_DIR.'/'.$this->modname.'/Templates';

		if(!is_dir($this->templateDir)) {
			PEAR::RaiseError("Template Dir %s doesn&quote;t exist, the modulename found was %s for Application: %s"
					, $this->templateDir
					, $this->modname
					, $this->appname
					);
		}

		// provide template mechanism
		$options = array(
				'compileDir'    =>  PEACH_APP_TEMPLATE_COMPILE_DIR,  // where do you want to write to..
				'templateDir'   =>  array($this->templateDir,PEACH_CORE_TEMPLATE_DIR), // where are your templates, two dirs!
				'locale'        => 'en',    // works with gettext
				'textdomain'    => '',      // for gettext emulation with File_Gettext
				// eg. 'messages' (or you can use the template name.
				'textdomainDir' => '',      // eg. /var/www/site.com/locale
				// so the french po file is:
				// /var/www/site.com/local/fr/LC_MESSAGE/{textdomain}.po
				'forceCompile'  =>  false,  // only suggested for debugging

				'debug'         => false,   // prints a few errors

				'nonHTML'       => false,  // dont parse HTML tags (eg. email templates)
				'allowPHP'      => false,   // allow PHP in template
				'compiler'      => 'Standard', // which compiler to use.
				'compileToString' => false,    // should the compiler return a string
				// rather than writing to a file.
				'filters'       => array(),    // used by regex compiler..
				'numberFormat'  => ",2,'.',','",  // default number format  = eg. 1,200.00 ( {xxx:n} )
				'flexyIgnore'   => 0,        // turn on/off the tag to element code
				'strict'        => false,       // All elements in the template must be defined !
				'multiSource'   => true,       // Allow same template to exist in multiple places
				// So you can have user themes....
				'templateDirOrder' => 'reverse'       // set to 'reverse' to assume that first template
					// is the one use, rather than last (default)
					);

				$this->tpl = new HTML_Template_Flexy($options);

				if (PEAR::isError($this->tpl)) {
					echo $this->tpl->getMessage();
				}

	} 

	final public function display()
	{
		// set output to false if no

		if($this->output != false) {
			if(!isset($this->tpl)) {
				$this->output = PEAR::RaiseError('No template object initialized, if you use a constructor in your module make sure to initialize parent::__construct()', NULL, PEAR_ERROR_RETURN, E_USER_ERROR);
			}

			if(empty($this->template)) {
				$this->output = PEAR::RaiseError("Template not set for {$this->modname}::public_{$this->method}()!", NULL, PEAR_ERROR_RETURN, E_USER_ERROR);
			}

			if(PEAR::isError($this->output)) {
				// detect if the output object is a pear error message.
				// should we do this here ? it's kinda convenient.
				// So every module should leave the error message in tact and put it in output. 
				// can be in the core template dir 
				$this->template = 'error.tpl';
			}

			PEACH_Debug::instance()->msg(__METHOD__, "Displaying {$this->modname}::{$this->method}() using {$this->template}");
			$this->tpl->compile($this->template);
			return $this->tpl->bufferedOutputObject($this->output);

		} else {
			// return nothing
			return "";
		}

	}

	final function checkAuth($showLogin = false)
	{
		PEACH_Debug::instance()->msg(__METHOD__, "Checking Auth for {$this->modname}::{$this->method}()");

		$auth = PEACH_Auth::instance();
		if($auth->check()) {
			return true;
		} else {

			if($showLogin != false) {
				$this->template = 'login.tpl';
				$output = $auth->loginForm();
				$this->output($output);
			} else {
				$this->output(false);
			}

			return false;
		}


	}

	public function output($output)
	{

		$this->output = $output;

	}

	// Maintainance for PEACH_App
	final function appStart()
	{
		PEACH_Debug::instance()->msg(__METHOD__, "AppStart Called for {$this->modname}::{$this->method}()");
		// is called everytime the App Object is operated on
		// it's the first method called (on an existing object)

		// start with a new output object, clearing any output allready present in the object
		$this->output = new StdClass;

		// Reset the template, otherwise the template is remembered, although a different method
		// is called..
		$this->template = '';
	}

	final function appEnd()
	{
		// unset the current method
		PEACH_Debug::instance()->msg(__METHOD__, "AppEnd Called for {$this->modname}::{$this->method}()");
		$this->method = '';

	}

	// To be extended by the Peach Application
	public function start()
	{

	}

	// To be extended by the Peach Application
	public function end()
	{
		// is called everytime the App Object is operated on
		// it's the last method called before output is being done (on an existing object) 

	}

	// To be extended by the Peach Application
	public function epilogue()
	{
		// is called everytime the App Object is operated on
		// it's the last method called AFTER all output has beeing done

	}

	// Epilogue of every Peach_App 
	final public function appEpilogue()
	{

		// is called everytime the App Object is operated on
		// it's the last method called AFTER all output has beeing done

	}
	

	final function badRequest()
	{
		// this function is called by Site.php when the App and module are found, but the method is unknow.
		header("HTTP/1.0 400 Bad Request");
		$this->template = 'badRequest.tpl';
		$output = new StdClass;
        $output->modname = $this->modname;
        $output->method = $this->method;
		$this->output($output);

	} 

	final function setTemplate($template)
	{
		$this->template = $template;
	}

	final function setModuleFile($file)
	{
		$this->moduleFile = $file;
	}
	final function setModname($modname)
	{
		$this->modname = $modname;
	}
	final function setMethod($method)
	{
		$this->method = $method;
	}
	final function setIsCurrent($isCurrent)
	{
		$this->isCurrent = $isCurrent;
	}

	final function getTemplate()
	{
		return $this->template;
	}

	final function getModuleFile()
	{
		return $this->moduleFile;
	}
	final function getModname()
	{
		return $this->modname;
	}
	final function getMethod()
	{
		return $this->method;
	}

	final function IsCurrent()
	{
		return $this->isCurrent;
	}

	final function setUseAuth($bool)
	{
		$this->useAuth = $bool;
	}

}

?>
