<?php

/**
 * Menu 
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Menu.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public
 */

class Menu extends DomDocument {

	function __construct() {
		//has to be called!
		parent::__construct();
	}

	function getDescription() {
		return $this->documentElement->getAttribute('desc');
	}

	function getTitle()
	{
		return $this->documentElement->getAttribute('title');
	}

	function getLinks()
	{
		return $this->documentElement->getElementsByTagname('link');
	}

	function removeLink($id)
	{
		$links = $this->getElementsByTagname('link');
		$link =  $links->item($id);
		$this->documentElement->removeChild($link);
	}
} 

?>
