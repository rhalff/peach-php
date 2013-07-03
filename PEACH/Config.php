<?php

if(!defined('PEACH_CORE_CONFIG_DIR')) {
    die("PEACH_CORE_CONFIG_DIR not defined!");
}

require_once("PEACH/Template.php");
require_once 'HTML/QuickForm/Renderer/Object.php'; 

/**
 * PEACH_Config
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com> 
 * @copyright Copyright (c) 2004 Rob Halff
 * @license LGPL (http://www.gnu.org/copyleft/lesser.html)
 * @version $Id: Config.php,v 1.4 2004/06/08 13:29:43 rhalff Exp $
 * @access public
 *
 * This class makes it possible to define the properties
 * of any class inside a xml configuration file.
 * 
 * Example:
 * 
 * Class file:
 * 
 * class myClass {
 * 
 * var $myValue = '100';
 * 
 * function myClass() {
 * $config = new PEACH_Config;
 * $config->load($this);
 * }
 * function setMyValue($value)
 * {
 * $this->myValue = $value;
 * }
 * }
 * 
 * PEACH_CORE_CONFIG_DIR/myclass.xml file:
 * &lt;config>
 * %lt;preferences>
 * &lt;pref id="myValue" title="Set your value" type="numeric"&gt;10&lt;/pref&gt;
 * &lt;/preferences&gt;
 * &lt;/config&gt;
 * 
 * This will try to find the properties inside PEACH_CORE_CONFIG_DIR/myclass.xml and
 * update the properties with the values found.
 * 
 * The title and type attributes are used to generate the edit form.
 * 
 **/

class PEACH_Config
{
	/**
	* 
	* Config file of the current class.
	* 
    * @var string 
    */
    public $configFile;
	
	/**
	* 
	* The classname of the class to handle the config for
	* 
    * @var string
    */
    public $className;

	/**
	* 
	* The config directory 
	* 
    * @var string
    */
    public $configDir;

	/**
	* 
	* Current object to handle the config for
	* 
    * @var object
    */
    public $object;

	/**
	* 
	* The root element 
	* 
    * @var object
    */
    public $root = 'config';

	/**
	* 
	* Path to config file 
	* 
    * @var object
    */
    public $path;
    
    /**
     * PEACH_Config::__construct()
	 * 
     * @return 
     **/
    function __construct($file = null)
    {
        if($file == null) {

        } else {
            $this->configFile = $file; 
        }
    } 

    /**
     * PEACH_Config::load()
	 * 
	 * Powerfull method, takes the object passed to this method, then tries
	 * to read the config file according to the object's classname.
	 * 
	 * Configfile is the same name as the classname in lowercase, e.g:
	 * class = PEACH_Config_Editor
	 * configfile = rhalff_image_editor.xml
	 * 
	 * Location of the config files is PEACH_CORE_CONFIG_DIR.
     * 
     * @param $object
     * @return bool
     **/
    function load($object)
    {
        if (!is_object($object))
        {
            return PEAR::RaiseError("PEACH_Config:load must be passed a valid object", NULL, PEAR_ERROR_TRIGGER, E_USER_ERROR);
        } 

        /* hybrid working.. can specify a config file on construction or use the class name of the object */

        if(!isset($this->configFile)) {
            $this->configFile = PEACH_CORE_CONFIG_DIR."/".get_class($object).".xml";
        }

        if ($this->parseConfig($this->configFile))
        {
            $this->object = $object;
            $this->_set();
            return true;
        } 
        else
        {
            $error_msg = sprintf('PEACH_Config::parseConfig() Failed to load "%s" for %s', $this->configFile, get_class($object));
            PEACH_Debug::instance()->msg(__METHOD__, "$error_msg");
            return false;
        } 
    } 

    /**
     * PEACH_Config::parseConfig()
	 * 
	 * This function can be called directly to read a config file.
	 * It is also used by PEACH_Config::load()
     * 
     * @param string $name
     * @param string $dir
     * @return bool 
     **/
    function parseConfig()
    {

        if (!isset($this->configFile))
        {
           PEAR::RaiseError('PEACH_Config::parseConfig() $this->config_file is not set', NULL, PEAR_ERROR_TRIGGER, E_USER_NOTICE);
        } 

        if (file_exists($this->configFile))
        {
		    $this->config = new DomDocument;
		    // this die thing is misplaced.. probable should use throw.. or rethink the design.
	        if(!$this->config->load($this->configFile)) {
                return false;
            }

            /* set the root element */
            $this->root = $this->config->documentElement;
            return true;
        } 
        else
        {
            return false;
        }

    }

    /**
     * PEACH_Config :: _set()
	 * 
	 * Internal function used by PEACH_Config::load()
	 * This will take the preferences from the xml file and set 
	 * the properties by calling the appropriate setProperty() method of
	 * the class passed to PEACH_Config::load()
     * 
     * @return 
     **/
    private function _set()
    {
        if (!isset($this->object))
        {
            PEAR::RaiseError("PEACH_Config::_set() no current object.", null, PEAR_ERROR_TRIGGER, E_USER_ERROR);
        } 

        if (!isset($this->config))
        {
            PEAR::RaiseError("PEACH_Config::load() this->config not set.", null, PEAR_ERROR_TRIGGER, E_USER_ERROR);
        } 

        foreach ($this->prefArray() as $key => $value)
        {
            $method = 'set' . ucfirst($key); // e.g. setinputgamma()
            if (method_exists($this->object, $method))
            { 
                // set the value
                $this->object->$method($value);
            } 
            else
            {
                PEAR::RaiseError(sprintf('PEACH_Config::load() Object <em>%s</em> has no method %s()', get_class($this->object), $method), NULL, PEAR_ERROR_TRIGGER, E_USER_NOTICE);
            } 
        } 
    } 

    /**
     * PEACH_Config::prefArray()
	 * 
	 * Return all preferences inside the config file in an associative array.
     * 
     * @return array
     **/
    function prefArray()
    {
        $preferences = $this->root->getElementsByTagname('pref');
        $prefs = array();
        foreach($preferences as $item)
        {
            $id = $item->getAttribute('id');

		$value = $item->nodeValue;

		switch($item->getAttribute('type')) {
		/* a string containing 'false' is returned, so convert it to a real false */

		case "boolean":
		if($value == "false") {
			$value = false;
		} else {
			$value = true;
		}
		break; 

		case "constant":
            if(defined("$value")) {
                $value = constant($value);
            } else {
                $value = null;
            }
		break; 

		case "bool":
		if($value == "false") {
			$value = false;
		} else {
			$value = true;
		}
		break; 

		case "array":
		/* example la,li,lo */
		$value = explode(',', $value);
		break; 

		}

            $prefs[$id] = $value;


        } 
        return $prefs;
    } 

    /**
     * PEACH_Config::getPref()
	 * 
	 * Get a preference from the configuration file, based on the id of
	 * the preference.
	 * 
	 * e.g.
	 * <config>
	 * <preferences>
	 * <pref <em>id="PrefName"</em>>;
     * 
     * @param $id
     * @return string
     **/
    function getPref($id)
    {

        $preferences  = $this->config->getElementsByTagname("pref");
        foreach($preferences as $pref) {

            if($pref->getAttribute('id') == $id) {
		$value = $pref->nodeValue;

		switch($pref->getAttribute('type')) {
		/* a string containing 'false' is returned, so convert it to a real false */

		case "boolean":
		if($value == "false") {
			return false;
		} else {
			return true;
		}

		break; 

		case "bool":

		if($value == "false") {
			return false;
		} else {
			return true;
		}

		break; 

		case "array":
		/* example la,li,lo */
		$value = implode($value, ',');
		return $value;

		break; 

		}


                return $pref->nodeValue;
            }
        }
 
        return "Warning: Entry for preference '$id' not found!";
    } 

    /**
     * PEACH_Config::setPref()
	 * 
	 * Saves the preference to the xml file.
     * 
     * @param $id
     * @param $value
     * @return 
     **/
    function setPref($id, $value)
    {
        // element getElementById() doesn't seem to work
        // so I try this lenghty way.
        $preferences  = $this->config->getElementsByTagname("pref");
        foreach($preferences as $pref) {

            if($pref->getAttribute('id') == $id) {

                $type = $pref->getAttribute('type');
                switch ($type)
                {
                    case "template": 
                        // CDATA doesn't allow line breaks. (at least not in default mode
                        $value = ereg_replace("\r\n", "", $value);
                    break;
                } 
                // not the way to do it, but it works.
                $pref->nodeValue = $value;
            }

        }


    } 

    /**
     * PEACH_Config::edit()
	 * 
	 * Generates a form to edit the current config file.
	 * 
	 * Does some basic type checking as defined in the xml config file.
	 * 
	 * Should probably become a seperate class.
     * 
     * @return string
     **/
    function edit()
    {
        /*

		   array(
		   'required'      =>array('regex', '/(\s|\S)/'),
		   'maxlength'     =>array('regex', '/^(\s|\S){0,%data%}$/'),
		   'minlength'     =>array('regex', '/^(\s|\S){%data%,}$/'),
		   'rangelength'   =>array('regex', '/^(\s|\S){%data%}$/'),
		   'regex'         =>array('regex', '%data%'),
		   'email'         =>array('regex', '/^[a-zA-Z0-9\._-]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/'),
		   'emailorblank'  =>array('regex', '/(^$)|(^[a-zA-Z0-9\._-]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$)/'),
		   'lettersonly'   =>array('regex', '/^[a-zA-Z]+$/'),
		   'alphanumeric'  =>array('regex', '/^[a-zA-Z0-9]+$/'),
		   'numeric'       =>array('regex', '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/'),
		   'nopunctuation' =>array('regex', '/^[^().\/\*\^\?#!@$%+=,\"\'><~\[\]{}]+$/'),
		   'nonzero'       =>array('regex', '/^[1-9][0-9]+/'),
		   'uploadedfile'  =>array('function', '_ruleIsUploadedFile'),
		   'maxfilesize'   =>array('function', '_ruleCheckMaxFileSize'),
		   'mimetype'      =>array('function', '_ruleCheckMimeType'),
		   'filename'      =>array('function', '_ruleCheckFileName')
		   );
		 */

        require_once "HTML/QuickForm.php"; 

		
        $form = new HTML_QuickForm('frmTest','post');; 
        // register custom rules
		require_once "PEACH/HTML/QuickForm/Rule/gdOutputType.php";
        require_once "PEACH/HTML/QuickForm/Rule/float.php";

        $form->registerRule('float', null, 'PEACH_HTML_QuickForm_Rule_Float');
        $form->registerRule('gdOutputType', null, 'PEACH_HTML_QuickForm_Rule_gdOutputType');

        $preferences = $this->root->getElementsByTagname('pref'); 

        if (!empty($preferences))
        {
            foreach($preferences as $pref) {
                $prefName = $pref->getAttribute('id');
                $prefTitle = $pref->getAttribute('title');
                $prefType = $pref->getAttribute('type');
                $prefValue = $pref->nodeValue;

                switch ($prefType)
                {
                    case "numeric":

                        if ($r = $pref->getAttribute('rangelength'))
                        {
                            $range = explode(",", $r);

                            for($q = $range[0]; $q <= $range[1]; $q++)
                            {
                                $options[$q] = $q;
                            } 

                            $form->addElement("select", "prefs[$prefName]", $prefTitle, $options);
                        } 
                        else
                        {
                            $form->addElement("text", "prefs[$prefName]", $prefTitle);
                            $form->addRule("prefs[$prefName]", "Must be numeric", 'numeric');
                        } 

                        break;

                    case "enum":

                        $values = $pref->getAttribute('values');
                        $range = explode(",", $values);

                        for($q = 0; $q < count($range); $q++)
                        {
                            $options[$range[$q]] = $range[$q];
                        } 

                        $form->addElement("select", "prefs[$prefName]", $prefTitle, $options);

                        break;

                    case "string";
                        $form->addElement("text", "prefs[$prefName]", $prefTitle);
                        $form->applyFilter("prefs[$prefName]", 'trim');
                        break;

                    case "password";
                        $form->addElement("password", "prefs[$prefName]", $prefTitle);
                        $form->applyFilter("prefs[$prefName]", 'trim');
                        break;

                    case "float";
                        $form->addElement("text", "prefs[$prefName]", $prefTitle); 
                        // $form->addElement('text', 'float', 'Float (0 < 1):');
                        $form->addRule("prefs[$prefName]", 'Must be a float between 0 and 1', 'float');
                        break;

                    case "gdOutputType";
                        $form->addElement("text", "prefs[$prefName]", $prefTitle); 
                        // $form->addElement('text', 'float', 'Float (0 < 1):');
                        $form->addRule("prefs[$prefName]", 'Must be either jpeg or png', 'gdOutputType');
                        break;

                    case "email";
                        $form->addElement("text", "prefs[$prefName]", $prefTitle);
                        $form->addRule("prefs[$prefName]", "Must be a valid email", 'email');
                        break;

                    case "emailorblank";
                        $form->addElement("text", "prefs[$prefName]", $prefTitle);
                        $form->addRule("prefs[$prefName]", "Must be a valid email or left blank", 'emailorblank');
                        break;

                    case "path":
                        $form->addElement("text", "prefs[$prefName]", $prefTitle);
                        $form->addRule("prefs[$prefName]", "Must be a valid path", 'path');
                        break;

                    case "template": 
                        // ok, took me two hours just for this simple reg
                        // replace all closing tags with a line break for readability
                        // when saving all line breaks are removed again
                        $prefValue = preg_replace("/<\/(\w+)>/", "$0\r\n", $prefValue);
                        $form->addElement("textarea", "prefs[$prefName]", $prefTitle, 'cols="50" rows="10" wrap="virtual"');
                        break;

                    Default:
                        $form->addElement("text", "prefs[$prefName]", $prefTitle);
                        break;
                } 

                $values["prefs[$prefName]"] = $prefValue;
            } 
            $form->applyFilter('__ALL__', 'trim');
            $form->setDefaults($values);
            $form->addElement("hidden", "module", 'PEACH_App_Config_Manager');
            $form->addElement('hidden', 'method', 'EditConfig');
            $form->addElement("submit", "Submit", "Change");
            if ($form->validate())
            {
                $form->freeze(); 
                while (list($name, $value) = each($form->_submitValues['prefs']))
                { 
                    // change the preference within the config xml file
                    $this->setPref($name, $value);
                } 
                // write the config file
                $res = $this->config->save($this->config_file);

                if (PEAR::isError($res))
                {
                    return $res->getMessage();
                } 
                else
                {

                    $succes = new stdClass;
                    $succes->saved = gettext("Config file written.");
                    $succes->linkback = PEACH_Page::Link('Back', 'PEACH_App_Config_Manager', "fileList");
                    return $succes;
                } 
            } 
            else
            {
                // very cool dynamic form template, tha power of oop, logic inside templates is good!
                // Only one template for all forms.
                //$tpl = PEACH_Template::instance();
                //$tpl->compile("styles/green.html");
                //$tpl->compile("styles/fancygroup.html");

                $renderer = new HTML_QuickForm_Renderer_Object(true);

                // give some elements aditional style informations
                $renderer->setElementStyle(array(
                            'ipwdTest'  => 'green',
                            'iradYesNo' => 'fancygroup',
                            'name'      => 'fancygroup'
                            ));

                $form->accept($renderer);
                // return the object 
                return $renderer->toObject();

            } 
        } 
        else
        {
            return "<em>This module has no preferences to be set.</em>";
        } 
    } 

    /**
     * PEACH_Config::fileList()
     * 
     * Creates a filelist of all .xml files in the PEACH_CORE_CONFIG_DIR directory
     * 
     * Should be templates based later.
     * 
     * @return string
     */
    function fileList()
    {
        require_once 'File/Find.php';
        $reg = sprintf("/.*\.%s/", 'xml');
        $items = &File_Find::glob($reg, $this->configDir, 'perl'); 

        if(PEAR::IsError($items)) {
            return $items();
        }

        // should be template based..
        $content = new stdClass;
        for($i = 0; $i < count($items); $i++)
        {
            $stripped = explode('.', $items[$i]);
            array_pop($stripped);
            $className = implode('.', $stripped);
            $content->links[] = PEACH_Page::Link($items[$i], 'PEACH_App_Config_Manager', "editConfig", array('class'=>$className));
        } 
        return $content;
    } 

    public function setFile($file)
    {
            $this->configFile = $file;
    }


    public function setRoot($name)
    {

        $this->root = $name;

    }

	function __destruct()
	{

		//unset($this->root);

	}


}
?>
