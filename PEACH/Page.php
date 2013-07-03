<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

require_once "PEACH/Config.php";
require_once "HTML/Page.php";

/**
 * PEACH_Page
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Page.php,v 1.3 2004/06/07 18:50:45 rhalff Exp $
 * @access public
 **/

class PEACH_Page {

    private $styleSheets = array();

	/**
	 * The Page singleton instance is stored here
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
	 * Use this static method to get an Page singleton instance
	 */
	static function instance() {
		if(!PEACH_Page::$instance) {
            $page = new PEACH_Page;
			PEACH_Page::$instance = $page->factory();
		}
		return PEACH_Page::$instance;
	}

    function factory()
    {
            $page = new HTML_Page();
            $config = new PEACH_Config();
            $config->load($page);
            foreach($this->styleSheets as $styleSheet) {
                $page->addStyleSheet($styleSheet);
            }
            return $page;
    }

    public function setStyleSheets($styles)
    {
        $this->styleSheets = $styles;
    }

}

?>
