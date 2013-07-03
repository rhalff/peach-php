<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

ini_set('display_errors', 'On');
error_reporting(E_ALL);

// debugging
#dl('xdebug.so');
#xdebug_enable();

require_once 'PEACH/Debug.php';
require_once 'PEACH/Config.php';
require_once 'PEACH/PEACH.php';
require_once 'PEACH/Auth.php';
require_once 'PEACH/Log.php';
require_once 'PEACH/Page.php';

if(!defined('PEACH_METHOD_PREFIX')) {
    die(PEACH_METHOD_PREFIX.' not defined');
}
if(!defined('PEACH_MODULE_KEY')) {
    die(PEACH_MODULE_KEY.' not defined');
}

if(!defined('PEACH_DIR')) {
    die("PEACH_Site: PEACH_DIR not defined !");
}

if(!defined('PEACH_APP_DIR')) {
    die("PEACH_APP_DIR not defined");
}

if(!defined('PEACH_APP_PREFIX')) {
    define("PEACH_APP_PREFIX", 'PEACH_App_');
}

if(!defined('PEACH_REWRITE')) {
    die("PEACH_Site: PEACH_REWRITE not defined !");
}

/**
 * PEACH_Site
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff 
 * @version $Id: Site.php,v 1.12 2004/06/10 17:23:00 rhalff Exp $
 * @access public
 **/

class PEACH_Site {

    /* WHat map to use as default */
    private $defaultSiteMap;
    private $currentSiteMap;
    private $connectionMap;
    private $rewrite;
    private $reqModule;
    private $reqMethod;
    private $reqArgs;
    private $reqAppDir;
    private $configDir;

    /* Site is a singleton */
    static private $instance = false;

    // bypass authentication for the entire site..
    private $useAuth = true;

    /* variable that keeps all applications */
    private $appContainer;

    private function __construct()
    {

        $config = new PEACH_Config();
        $config->load($this);

        // Initialize Propel using path to the converted
        // property file that was created with the convert-props phing target
        // Only load propel if the config file is present
        if(file_exists(PEACH_CORE_CONFIG_DIR.'/PEACH-conf.php')) {
            require_once 'propel/Propel.php';
            Propel::init(PEACH_CORE_CONFIG_DIR.'/PEACH-conf.php');
        } else {
            PEACH_Debug::instance()->msg(__METHOD__, PEACH_CORE_CONFIG_DIR.'/PEACH-conf.php not found !');
        }

        Peach_Debug::instance()->msg(__METHOD__, 'Started');
        $this->parseRequest();
    }

    /**
     * Use this static method to get a PEACH_Site singleton instance
     */
    static function instance() {
        if(!PEACH_Site::$instance) {
            PEACH_Site::$instance = new PEACH_Site();
        }
        return PEACH_Site::$instance;
    }

    function load()
    {

        if(empty($this->currentSiteMap)) {
            if(isset($this->defaultSiteMap)) {
                $this->currentSiteMap = $this->defaultSiteMap;
            } else {
                PEAR::RaiseError("PEACH_Site::load() unable to determine sitemap, no current map available and no default set.", NULL, PEAR_ERROR_TRIGGER, E_USER_ERROR);
            }
        }

        $this->site = new DomDocument();
        $this->site->substituteEntities = true;
        $this->site->load(PEACH_SITEMAP_DIR."/".$this->currentSiteMap);

    }

    function execute()
    {
        /*
           Start at body, everything above layout is just html
         */
        $layout = $this->site->getElementsByTagname('sitemap')->item(0);

        // read the body 
        $this->_traverse($layout);
        /*
        // if in editmode register a new element to edit the layout. 
        // this sucks.. layout should allready be a module. but how ?
        $placeholder = $this->site->createElement('placeholder');
        $layout->appendChild($placeholder);
        $this->register($placeholder, 'Core', 'Admin', 'Manager', 'layoutLink', 'Edit', array('file'=>$this->currentSiteMap), false);
         */
        $this->executeApps();

        /* Validate the document.. this really should be done only when needed. */
        /*
           $schemaLocation = PEACH_DIR.'/PEACH/Schema/RELAXNG/xhtml/xhtml-strict.rng';
           if(!file_exists($schemaLocation)) { echo "file doesn't exists"; }
           $xhtml->relaxNGValidate($schemaLocation);
         */

        /* not sure where to place it, layout should be a core module */

        // First get the empty HTML_Page, dont use ->setBody()
        $siteOutput = new domDocument();
        $siteOutput->loadXML(PEACH_Page::instance()->toHtml());
        $body = $siteOutput->getElementsByTagname('body')->item(0);

        // Import our body content (so load the body through dom)
        foreach($this->site->documentElement->childNodes as $child) {
            $import = $siteOutput->importNode($child, true);
            $imported = $body->appendChild($import);
        }

        // Display the document
        echo $siteOutput->saveXml();

        // loop through the modules again and execute the epiloque
        // Epilogue is done AFTER all output has been done.

        foreach($this->modules as $k=>$v) {
            $_SESSION[$v['Modname']]->epilogue();
            $_SESSION[$v['Modname']]->appEpilogue();
        }

        Peach_Debug::instance()->msg(__METHOD__, 'Finished', true);

    }

    function _traverse($node) 
    {
        // start at the document root
        foreach ($node->childNodes as $nodes) { 

            /* Type 1 is element
               Type 3 is textnode
               Type 8 is comment
               , we don't want to display text nodes */
            if ($nodes->nodeType == 1) {

                /* App is special case, it's the App instruction */
                if($nodes->nodeName == 'App' || $nodes->nodeName == 'CurrentApp') {
                    //  There is a slight problem, we can't just execute the app sequentialy
                    // because first all applications that needs to be loaded need to be known.
                    // because the classFiles need to be known before the session is started,
                    // a very irritating feature of php. We will accomplish this by remembering
                    // the current element for the app. So we can attach the output (appendChild)
                    // later to this element.

                    /*
                       <App module="Document" method="view" title="Documents">
                           <param name="pageId" value="2"/>
                       </App>
                     */

                    $module  = $nodes->getAttribute('module');
                    //$file    = $nodes->getAttribute('file');
                    $method  = $nodes->getAttribute('method');
                    $title   = $nodes->getAttribute('title');
                    $params  = $nodes->getElementsByTagname('param');

                    /* conver to array, later do it better */
                    /* init and reset the args array */
                    $args = array();
                    foreach ($params as $node) {
                        $key = $node->getAttribute('name');
                        $args[$key] = $node->getAttribute('value');
                    } 

                    // if this is the currentApp, tag it as current so execute knows about it.
                    // placeholder for the current requested Application
                    // can't be fixed because the params/args change
                    // The current app is controlled through the request made to the application,
                    // not the arguments set inside the xml file. Offcourse All applications could listen
                    // for requests and there isn't really a current app, but assuming that becomes too
                    // complex at the moment.

                    // note that xml params for the current App ARE registered,
                    // so they should be overriden in the request string.
                    if($nodes->tagName == 'CurrentApp') {
                        $current = true;
                    } else {
                        $current = false;
                    }

                    // register this app
                    $this->register($nodes, $module, $method, $title, $args, $current);

                } else {
                    /*
                    // convert to div
                    $id = $nodes->nodeName;
                    $div = $this->xhtml->createElement('div');
                    $div->setAttribute('id',$id);
                     */

                    if($nodes->hasChildNodes()) {
                        $this->_traverse($nodes);
                    }

                }
            }
        }

    }

    /*
     * Require all needed class files first.. irritating ? yes :)
     **/
    function register($domelement, $module, $method, $title, $args, $current)
    {
        // module = Config
        // create a unique identifier based on the module method and args/params
        //$argstring = implode('',$args);
        //$identifier = md5("$module$method$argstring");

        // If this is the current module, grep the reguest args and
        // merge them with the values in the config file.
        // hhmm the method should really be a param maybe ?
        // Override all values written in currentApp
        if($current) {

            if(!empty($this->reqModule)) {

                if(!empty($this->reqMethod)) {
                    $module = $this->getModule();
                    $method = $this->getMethod();
                    $args = $this->getArgs();
                }

            }

        }
        $classFile = PEACH_APP_DIR."/$module/Manager.php";
        if(file_exists($classFile)) {

            require_once $classFile;
            // array of modules to execute
            $this->modules[] = array(
                    'Modname'=>$module,
                    'Method'=>$method,
                    'ParentElement'=>$domelement,
                    'isCurrent'=>$current,
                    'Args'=>$args);

        } else {
            // if current should throw a file not found
            PEACH_Log::instance()->log("PEACH_Site::register() unable to require $classFile for module $module");
            if($current) {

                $args = array(
                        'Modname'=>$module,
                        'Method'=>$method,
                        'Args'=>$args);

                // if this is the current module, change the requested page to notFound module
                $classFile = PEACH_APP_DIR."/HttpError/Manager.php";
                require_once $classFile;
                $this->modules[] = array(
                        'Modname'=>'HttpError',
                        'Method'=>'404',
                        'ParentElement'=>$domelement,
                        'isCurrent'=>$current
                        );


            }
        }

    }

    function executeApps()
    {
        session_name('PEACH');
        session_start();

        // start the initialization plugins. These plugins are site wide.
        // things like authentication, stats and logging are loaded here.
        PEACH_Auth::instance()->start();
        PEACH_Page::instance();

        // execute all applications
        // Ok notice.. We excute template flexy, template flexy
        // returns valid xhtml. We load this valid xhtml and append it
        // to the document tree, this means if any template doesn't return
        // valid xml, the application failes :-) Meaning it's impossible
        // to serve not wellformed xhtml
        // $output contains xhtml.

        foreach($this->modules as $k=>$v) {

            /*
               'Appname'=>$name,
               'Modname'=>$module,
               'Method'=>$method,
               'ParentElement'=>$domelement,
               'isCurrent'=>$current,
               'Args'=>$args);

             */

            // session where this module lives
            // what to do with the identifier ?
            // orinally created to make it the key of the session, but this would make
            // multiple instances of a module which is not needed. Now we use the Mod/Appname

            $sessionName = $v['Modname'];
            if (!isset($_SESSION["$sessionName"])) { 
                // start the session
                PEACH_Log::instance()->log("Starting new $sessionName Session\n", PEAR_LOG_DEBUG);
                $class = PEACH_APP_PREFIX.$v['Modname'];
                $_SESSION["$sessionName"] = new $class;
                $_SESSION["$sessionName"]->setModname($v['Modname']);
                // lazy initialisation instead of __construct, because we need to set the above
                // also.
                $_SESSION["$sessionName"]->init();
            } 
            $_SESSION["$sessionName"]->setMethod($v['Method']);

            // Always call this ones per module call, module can be called multiple times 

            // start for internal maintainance for an object extended by PEACH_App
            $_SESSION["$sessionName"]->appStart();

            // User method to be able to do custom maintainance
            $_SESSION["$sessionName"]->start();

            // whether this module is the current one
            $_SESSION["$sessionName"]->setIsCurrent($v['isCurrent']);

            // first set arguments passed through the request string
            // and call the setMethod() of the class 

            if(isset($v['Args']) AND is_array($v['Args'])) {

                foreach($v['Args'] as $key=>$value) {

                    $setMethod = "set".ucfirst(strtolower($key)); 
                    if(method_exists($_SESSION["$sessionName"], $setMethod)) {

                        $_SESSION["$sessionName"]->$setMethod($value);

                    } else {
                        PEACH_Log::instance()->log("PEACH_Site::executeApps() Method $setMethod not found inside ". $v['Modname'], PEAR_LOG_INFO);
                    }

                }
            }

            if(isset($v['Method'])) {
                $method = PEACH_METHOD_PREFIX.$v['Method'];
                if(method_exists($_SESSION["$sessionName"], $method)) {

                    // see if this module want to make use of Authentication
                    // if not just skip the checking. The checking is done on method level.
                    // if there is extra authentication checking needed within the method itself use....TODO
                    if($_SESSION["$sessionName"]->useAuth === true) {
                        PEACH_Debug::instance()->msg(__METHOD__," checking {$v['Modname']}::$method");
                        if(PEACH_Auth::instance()->hasPerm($v['Modname'], $method)) {
                            // rething this right now i just need a yes or no
                            $_SESSION["$sessionName"]->$method();
                        } else {

                            echo "TODO: what to do if there is no permission ? Silent or a warning ! configurable, default no warning, just no output.";

                        }
                    } else {
                        $_SESSION["$sessionName"]->$method();

                    }
                    // return the parsed template can contain a pear error message or the parsed object
                    $modOutput = $_SESSION["$sessionName"]->display();

                } else {

                    PEACH_Log::instance()->log("PEACH_Site::executeApps() Method $method not found inside {$v['Modname']}", PEAR_LOG_INFO);
                    // method not found, defined in PEACH/App/Model.php and extended by each module.
                    $_SESSION["$sessionName"]->badRequest();
                    $modOutput = $_SESSION["$sessionName"]->display();

                }

                // do not create a div if the output is empty.
                if($modOutput != "") {
                    $output = sprintf("<div class='%s'>%s</div>", $v['Modname'],$modOutput);

                    /* Load the parsed xhtml output */
                    $app = new DomDocument();
                    if($app->loadXml($output)) {

                        /* true seems to be to also include children, well, without it it just doesn't work */
                        $importApp = $this->site->importNode($app->documentElement, true);

                        // replace the App location with the contents of the App :)
                        $parent = $v['ParentElement']->parentNode;
                        $parent->replaceChild($importApp, $v['ParentElement']);
                    } else {
                        PEAR::RaiseError("PEACH_Site::run() Failed to load xhtml.\n"
                                ."<br/>Method:".$v['Method']
                                ."<br/>Occuring HTML:<pre>".htmlspecialchars($output)."</pre>"
                                , NULL, PEAR_ERROR_TRIGGER, E_USER_NOTICE);
                    }
                }

                // Always call this ones per module call, module can be called multiple times 
                $_SESSION["$sessionName"]->end();
                $_SESSION["$sessionName"]->appEnd();


            } else {
                PEACH_Log::instance()->log("PEACH_Site::run() No method requested for ".$v['module'], PEAR_LOG_INFO);

            }

        }


    }

    function setDefaultSiteMap($map)
    {
        $this->defaultSiteMap = $map;
    }

    function setConnectionMap($map)
    {
        $this->connectionMap = $map;
    }

    function parseRequest()
    {

        if(PEACH_REWRITE AND !eregi('index.php', $_SERVER['REQUEST_URI'])) {

            /* remove trailing / */
            $request = preg_replace('/.*\/$/', '', $_SERVER['REQUEST_URI']);
            $request = explode('/', $request);
            /*
               /PEACH_App_Image_Manager/upload
               $request[0] = '';
               $request[1] = 'Image'; //Module
               $request[2] = 'view'; //Method

             */
            if(!empty($request) AND count($request) > 1) {

                $count = count($request);
                if($count > 2) {
                    $this->reqModule  = $request[1];
                    $this->reqMethod  = $request[2];

                    $this->reqAppDir  = sprintf('%s/%s',
                            PEACH_APP_DIR,
                            $this->reqModule);
                    for($i=3;$i<$count;$i++) {
                        // fix the trailing /
                        if(!empty($request[$i])) {
                            $this->reqArgs[$request[$i++]] = $request[$i];
                        }
                    }

                } else {
                    // throw a file not found error
                    $this->reqModule  = 'HttpError';
                    $this->reqMethod  = '404';
                }

            }
        } else {
            // no rewrite, probably not working anymore
            if(isset($_REQUEST['Method'])) {
                $this->reqModule = $_REQUEST['Module'];
                $this->reqMethod = $_REQUEST['Method'];
                if(isset($_REQUEST['Args'])) {
                    $this->reqArgs = $_REQUEST['Args'];
                }
            }
        }
    }

    function getModule()
    {
        return $this->reqModule;
    }

    function getMethod()
    {
        return $this->reqMethod;
    }

    function getArgs()
    {
        return $this->reqArgs;
    }

    function setUseAuth($bool)
    {
        $this->useAuth = $bool;
    }

    function setConfigDir($string)
    {
        $this->configDir = $string;
    }

    /**
     * PEACH_Site::link()
     * 
     * Create the Link
     * 
     * @param  $title 
     * @param unknown $module example PEACH_Config_Manager
     * @param unknown $method
     * @param unknown $args
     * @param unknown $target
     * @return string
     */
    function link($title, $module, $method, $args = null,  $target = null)
    { 

        if(PEACH_REWRITE) {

            $link = "<a href=\"";

            if($module != null) {
                $link .= "/$module/$method";
            }

            if (is_array($args))
            {
                foreach ($args as $var_name => $value) {
                    $link .= "/$var_name/$value";
                } 
            } 


        } else {

            // not working anymore (ancient)
            if(!isset($file)) {
                $file = $_SERVER['SCRIPT_NAME'];
            }

            $link = "<a href=\"$file";
            if($module != null) {
                $link .= "?".PEACH_MODULE_KEY ."=$module";
                $link .= "&amp;" . PEACH_METHOD_KEY. "=$method";
            }

            if (is_array($args))
            {
                foreach ($args as $var_name => $value) {
                    $link .= "&amp;";
                    $link .= PEACH_ARGS_KEY. "[$var_name]=$value";
                } 
            } 


        }

        if ($target == "blank" || $target === true) {
            $linkTarget = " target=\"_blank\" ";
        } elseif ($target == "index") {
            $linkTarget = " target=\"index\" ";
        } else {
            $linkTarget = null;
        } 

        return $link . "\"" . $linkTarget . ">" . strip_tags($title, "<img>") . "</a>";
    } // END FUNC indexLink()


}
?>
