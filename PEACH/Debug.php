<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * PEACH_Debug
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Debug.php,v 1.3 2004/06/07 18:50:45 rhalff Exp $
 * @access public
 **/

class PEACH_Debug {


    // USAGE: Peach_Debug::instance()->mesg(__MODULE__, 'Message');
    private $debug = false;
    /**
     * The Debug singleton instance is stored here
     */
    static private $instance = false;

    /**
     * Make the constructor private to prevent the class being
     * instantiated directly
     */
    private function __construct() {
        $config = new PEACH_Config();
        $config->load($this);
    }

    /**
     * Use this static method to get an Debug singleton instance
     */
    static function instance() {
        if(!PEACH_Debug::$instance) {
            PEACH_Debug::$instance = new PEACH_Debug();
        }
        return PEACH_Debug::$instance;
    }

    function msg($method, $mesg, $last = false) 
    { 

        if($this->debug == false) { return; }
        global $start_time;
        static $toggle;
        if($toggle == 0) {
            $toggle = 1; 
            $class = "even";
        } else {
            $toggle = 0;
            $class = "odd";
        };
        $time = round(microtime(), 5);
        $mesg = "<tr class=\"$class\"><td>$time</td><td>$method</td><td>$mesg</td></tr>";

        // This won't work in konqueror appearently
        if($last == true) {
            $mesg .= "</table>";
        }	
        $this->open(); 
        print "<script language='JavaScript'>\n"; 
        print "debugger.document.writeln('".trim(nl2br($mesg))."');\n"; 
        print "self.focus();\n"; 
        print "</script>\n"; 
    } 

    function open() 
    { 
        static $opened = FALSE; 
        global $start_time;

        if(!$opened) 
        { 
            $start_time = microtime();
            ?> 
                <script language="JavaScript"> 
                debugger = window.open("","debugger","toolbar=no,scrollbars,width=600,height=400"); 
            debugger.document.writeln('<html>'); 
            debugger.document.writeln('<head>'); 
            debugger.document.writeln('<title>PEACH Remote Debug Window</title>'); 
            debugger.document.writeln('</head>'); 
            debugger.document.writeln('<style>'); 
            debugger.document.writeln('td { font-size: smaller; border: 1px solid silver;}'); 
            debugger.document.writeln('.odd { background-color: #efe; }'); 
            debugger.document.writeln('.even { background-color: #eee; }'); 
            debugger.document.writeln('</style>'); 
            debugger.document.writeln('<body>'); 
            debugger.document.writeln('<hr size=1 width="100%">'); 
            debugger.document.writeln('<h2><?php echo $_SERVER['REQUEST_URI']; ?></h2>'); 
            debugger.document.writeln('<table><th>Time</th><th>Class/Method</th><th>Message</th>'); 
            </script> 
                <? 
                $opened = TRUE; 
        } 
    } 

    public function setDebug($bool)
    {

        $this->debug = $bool;

    }

}
?>
