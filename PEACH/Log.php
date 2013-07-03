<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

require_once 'Log.php';
require_once 'PEACH/Config.php';

/**
 * PEACH_Log
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Log.php,v 1.3 2004/06/07 18:50:45 rhalff Exp $
 * @access public
 **/

class PEACH_Log {

    static private $instance;
    private $handler;
    private $name;
    private $ident;
    private $conf = array();
    private $level;

    private function __construct()
    {
            $config = new PEACH_Config();
            $config->load($this);
    }

    /**
     * Use this static method to get a PEAR Log singleton instance
     */
    static function instance() {
        if(!PEACH_Log::$instance) {
            $log = new PEACH_Log();
            PEACH_Log::$instance = $log->factory();
        }
        return PEACH_Log::$instance;
    }

    private function factory()
    {
            if(!file_exists(dirname($this->name))) {
                die("File: '{$this->name}' doesn't exist!");
            }

            return Log::factory(
                        $this->handler,
                        $this->name,
                        $this->ident,   
                        $this->conf,
                        $this->level
                        );

    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
    }
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setIdent($ident)
    {
        $this->ident = $ident;
    }

    public function setConf($conf)
    {
        $this->conf = $conf;
    }
    public function setLevel($level)
    {
        $this->level = $level;
    }
}


?>
