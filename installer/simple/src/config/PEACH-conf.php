<?php
return array (
  'log' => 
  array (
    'ident' => 'PEACH',
    'level' => '7',
    'name' => '@INSTALL_DIR@/logs/propel.log',
  ),
  'propel' => 
  array (
    'database' => 
    array (
      'default' => 'PEACH',
      'PEACH' => 
      array (
        'adapter' => '@DB_TYPE@',
      ),
    ),
    'dsfactory' => 
    array (
      'PEACH' => 
      array (
        'connection' => 
        array (
          'phptype' => '@DB_TYPE@',
          'database' => '@DB_NAME@',
          'hostspec' => '@DB_HOST@',
          'username' => '@DB_USER@',
          'password' => '@DB_PASS@',
        ),
      ),
    ),
  ),
);
