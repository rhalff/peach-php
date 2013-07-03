<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
require_once 'HTML/Template/Flexy.php';

if(!defined('PEACH_CORE_TEMPLATE_DIR')) {
    die("PEACH_CORE_TEMPLATE_DIR not defined!");
}

if(!defined('PEACH_CORE_TEMPLATE_COMPILE_DIR')) {
    die("PEACH_CORE_TEMPLATE_COMPILE_DIR not defined!");
}

/**
 * PEACH_Template
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Template.php,v 1.3 2004/06/07 18:50:45 rhalff Exp $
 * @access public
 **/

class PEACH_Template {

    static $tpl = NULL; 
    static $options = array(
        'compileDir'    =>  "tmp",  // where do you want to write to..
        'templateDir'   =>  "",     // where are your templates
        'locale'        => 'en',    // works with gettext
        'forceCompile'  =>  false,  // only suggested for debugging

        'debug'         => false,   // prints a few errors

        'nonHTML'       => false,  // dont parse HTML tags (eg. email templates)
        'allowPHP'      => false,   // allow PHP in template
        'compiler'      => 'Standard', // which compiler to use.
        'compileToString' => false,    // should the compiler return a string
        // rather than writing to a file.
        'filters'       => array(),    // used by regex compiler..
        'numberFormat'  => ",2,'.',','",  // default number format  = eg. 1,200.00 ( {xxx:n} )
        'flexyIgnore'   => 0        // turn on/off the tag to element code
        );


    function Instance()
    {
        PEACH_Template::$options['templateDir'] = PEACH_CORE_TEMPLATE_DIR;
        PEACH_Template::$options['compileDir'] = PEACH_CORE_TEMPLATE_COMPILE_DIR;

        if(PEACH_Template::$tpl == NULL) {

            PEACH_Template::$tpl = new HTML_Template_Flexy(PEACH_Template::$options);
            if (PEAR::isError(PEACH_Template::$tpl)) {
                die(PEACH_Template::$tpl->getMessage());
            }

        }
   
        return PEACH_Template::$tpl;

    }


}


?>
