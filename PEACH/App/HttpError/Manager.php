<?php

require_once 'PEACH/App.php';

/**
 * PEACH_App_HttpError
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Manager.php,v 1.4 2004/06/10 13:15:06 rhalff Exp $
 * @access public
 **/

class PEACH_App_HttpError extends PEACH_App {

    function public_404()
    {

        /*
           404 - Not Found
           Requested file does not exist. This is the most commonly seen error and typically it is due to a URL syntax error or the file being deleted.
         */

        PEACH_Page::instance()->setTitle('404 Not Found');

        header ("http/1.0 404 Not Found");
        $this->template = "404.tpl";
        $output = new StdClass();
        $output->title = "404 - Not Found";
        $output->content = "Requested file does not exist.";
        $this->output($output);

    }

    function public_401()
    {

        /*
           401 Authorization Required
           Authorization Required
           This server could not verify that you are authorized to access the document requested. Either you supplied the wrong credentials (e.g., bad password), or your browser doesn't understand how to supply the credentials required.
         */

        PEACH_Page::instance()->setTitle('401 Authorization Required');

        header ("http/1.0 401 Authorization Required");
        $this->template = "401.tpl";
        $output = new StdClass();
        $output->title = "401 - Authorization Required";
        $this->output($output);

    }

}

?>
