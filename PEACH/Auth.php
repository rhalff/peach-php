<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/* Simple authentication, but solid */

require_once 'LiveUser.php';
require_once "PEACH/Config.php";
require_once 'HTML/QuickForm.php';

/**
 * PEACH_Aith
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Auth.php,v 1.4 2004/06/10 22:19:58 rhalff Exp $
 * @access public
 **/

class PEACH_Auth {

    private $expire;

    /**
     * The Auth singleton instance is stored here
     */
    static private $instance = false;
    private $dsn;
    private $table;
    private $usernamecol;
    private $passwordcol;
    private $db_fields;
    private $cryptType;
    private $auth;

    /**
     * Make the constructor private to prevent the class being
     * instantiated directly
     */
    private function __construct() {

        $config = new PEACH_Config();
        $config->load($this);

    } 

    /**
     * Use this static method to get an Auth singleton instance
     */
    static function instance() {
        if(!PEACH_Auth::$instance) {
            PEACH_Auth::$instance = new PEACH_Auth();
        }
        return PEACH_Auth::$instance;
    } 

    public function start()
    {

        $params = array(
                "dsn"=>$this->dsn,
                "table"=>$this->table,
                "usernamecol"=>$this->usernamecol,
                "passwordcol"=>$this->passwordcol,
                "db_fields"=>$this->db_fields,
                "cryptType"=>$this->cryptType
                );

        /* use the propel instance to get a valid db connection ? */
        /* or use propel itself as a driver ? */

        $params = array(
                'autoInit'       => true,
                'login'          => array(
                    'function' => 'showLoginForm',
                    'force'    => true),
                'logout' => array(
                    'trigger'  => 'logout',
                    'redirect' => '',
                    'destroy'  => true,
                    'method'   => 'get',
                    'function' => ''
                    ),
                'authContainers' => array(
                    array(
                        'type'          => 'DB',
                        'connection'    => $this->dsn,
                        'loginTimeout'  => 0,
                        'expireTime'    => 3600,
                        'idleTime'      => 1800,
                        'allowDuplicateHandles' => 0,
                        #'authTable'     => 'liveuser_users',
                        'authTable'     => $this->table,
                        'authTableCols' => array(
                            'user_id'   => 'auth_user_id',
                            'handle'     => 'handle',
                            'passwd'     => 'passwd',
                            'lastlogin'  => 'lastlogin',
                            'is_active'  => 'is_active'
                            )
                        )
                    ),
                'permContainer'  => array(
                        'type'     => 'DB_Complex',
                        'connection' => $this->dsn,
                        'prefix'     => 'liveuser_')
                    );
                $this->auth = LiveUser::factory($params);
/*
                $this->auth = new Auth("DB", $params);
                $this->auth->setShowLogin(false);
                $this->auth->setExpire($this->expire);
                $this->auth->start();
*/


    }

    public function check()
    {
        return $this->auth->checkAuth();
    }

    public function hasPerm($module, $method)
    {

        // hmm some kind of permission system this is :p
        return true;

    }

    public function loginForm()
    {
        // Get template instance
        $status = $this->auth->getStatus();

        // Content Container Object
        $class = new stdClass;
        $class->formaction = $_SERVER['REQUEST_URI'];
        $class->login = gettext("Login");
        $class->password = gettext("Password");

        if (!empty($status) && $status == AUTH_EXPIRED) {
            $class->logoutMessage = gettext("Your session expired. Please login again!");
        } else if (!empty($status) && $status == AUTH_IDLED) {
            $class->logoutMessage = gettext("You have been idle for too long. Please login again!");
        } else if (!empty ($status) && $status == AUTH_WRONG_LOGIN) {
            $class->logoutMessage = gettext("Wrong login data!");
        }

        return $class;
    }


    public function logout()
    {
        // logout
        $this->auth->logout();

    }
    public function setDsn($dsn)
    {
        $this->dsn = $dsn;
    }
    public function setTable($table)
    {
        $this->table= $table;
    }
    public function setUsernamecol($column)
    {
        $this->usernamecol = $column;
    }
    public function setPasswordcol($column)
    {
        $this->passwordcol = $column;
    }
    public function setDb_fields($fields)
    {
        $this->db_fields = $fields;
    }
    public function setCryptType($type)
    {
        $this->cryptType = $type;
    }
    public function setExpire($seconds)
    {
        $this->expire = $seconds;
    }

}


?>
