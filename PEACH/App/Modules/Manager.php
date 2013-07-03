<?php

require_once 'PEACH/App.php';

/**
 * PEACH_App_Modules
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Manager.php,v 1.1 2004/06/10 18:07:46 rhalff Exp $
 * @access public
 **/

class PEACH_App_Modules extends PEACH_App {


	public $item;

	/**
	 * PEACH_App_Modules::list()
	 * 
	 * Shows a list of installed modules.
	 * 
	 * @return 
	 */
	function public_list()
	{

		if($this->checkAuth(true)) {

			$this->template = 'list.tpl';

			/* parse info about the modules, show some icons and links

			   - guestbook
			   - documents
			   - stats
			   - shopping cart
			   - user manager // not important yet.
			   - forum // not important yet

			 */

			$output = new StdClass;

			if(isset($this->module)) {
				/* Switch to the module options */
				$linkFile = PEACH_DATA_DIR."/".$this->module."/adminLinks.xml";
			} else {
				/* Main admin panel */
				$linkFile = PEACH_DATA_DIR."/Modules/adminLinks.xml";
			}   

			$dom = new DomDocument;
			$dom->load($linkFile);

			$links = $dom->getElementsByTagname('adminLinks')->item(0);

			foreach($links->childNodes as $link) {

				if($link->nodeType == '1') {
					$module = $link->getAttribute('module');
					$method = $link->getAttribute('method');

					$modConfig = PEACH_APP_DIR."/$module/Conf/module.xml";

					if(file_exists($modConfig)) {
						$didom = new DomDocument;
						$didom->load($modConfig);
						$logo = $didom->getElementsByTagname('icon')->item(0)->nodeValue;
						$title = $didom->getElementsByTagname('title')->item(0)->nodeValue;
						$description = $didom->getElementsByTagname('description')->item(0)->nodeValue;
						$link = PEACH_Site::instance()->Link("<img src='$logo' title='$title' />", $module, $method);
						$output->modules[] = array(
								'title'=>$title,
								'description'=>$description,
								'link'=>$link
								);

					}

				}

			}

			$this->output($output);

		}


	} 

	public function setModule($module)
	{
		// list items of a specific module
		$this->module = $item;

	}

} 

?>
