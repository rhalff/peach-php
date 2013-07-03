<?

require_once 'PEACH/Page.php';
require_once 'PEACH/App/Menu/Classes/Menu.php';
require_once 'PEACH/App.php';

/**
 * PEACH_App_Menu
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Manager.php,v 1.7 2004/06/10 16:26:16 rhalff Exp $
 * @access public
 */

class PEACH_App_Menu extends PEACH_App {

	private $menuFile;
	private $editFile;
	private $menuClass = 'menu';

	public function public_menu()
	{
		$this->setTemplate("HTML_Menu.html");

		if(!file_exists($this->menuFile.".php")) {
			$this->compile($this->menuFile);
		}
		require $this->menuFile.".php";

		$output = new StdClass();
		require_once 'HTML/Menu.php';
		require_once 'HTML/Menu/DirectTreeRenderer.php';

        // menu Array is imported from the required menuFile
		$menu =& new HTML_Menu($menuArray);
		$menu->forceCurrentUrl($_SERVER['REQUEST_URI']);

		$renderer =& new HTML_Menu_DirectTreeRenderer();
        $renderer->setEntryTemplate(HTML_MENU_ENTRY_INACTIVE, '<a href="{url}" title="{desc}">{title}</a>');
        $renderer->setEntryTemplate(HTML_MENU_ENTRY_ACTIVE, '<strong>{title}</strong>');
        $renderer->setEntryTemplate(HTML_MENU_ENTRY_ACTIVEPATH, '<a href="{url}" title="{desc}"><em>{title}</em></a>');
		$renderer->setLevelTemplate("<ul class='{$this->menuClass}'>",'</ul>');
		$menu->render($renderer);
        $output->title = $title;
        $output->description = $description;
		$output->menu = $renderer->toHtml();

		$this->output($output);

	}

    function public_list()
    {
        $this->template = 'list.tpl';
        $dir = PEACH_DATA_DIR."/Menu";

        if(file_exists($dir)) {
            $d = dir($dir);

            while($file = $d->read()) {
                if(substr($file, -3) == 'xml' ) {

                    $menu = new Menu();
                    $menu->load(PEACH_DATA_DIR."/Menu/$file");

                    $this->output->menus[] = array(
                            'link'=> PEACH_Site::instance()->Link($menu->getTitle(), 'Menu', 'editMenu', array('editFile'=>$file)),
                            'description'=>$menu->getDescription()
                            );
                }
            }
            $d->close();
        } else {

            $this->output = PEAR::RaiseError('PEACH_Image_Filter::PEACH_Image_Filter() $method doesn\'t exists', NULL, PEAR_ERROR_RETURN, E_USER_NOTICE);

        }
    }

	function compile($file)
	{
		// compile the current menu to a php array
		$dom = new DomDocument;
		PEACH_Debug::instance()->msg(__METHOD__, "Compiling {$this->editFile}");
		$dom->load($file);

		$menuArray = $this->parseLinks($dom->documentElement->childNodes);
        $title = $dom->documentElement->getAttribute('title');
        $desc = $dom->documentElement->getAttribute('desc');

		$fp = fopen($file.".php", 'w');
		$menuString = sprintf('<?php $menuArray = unserialize(\'%s\'); $title="%s"; $description="%s"?>', serialize($menuArray), $title, $desc);
		fwrite($fp, $menuString);
		fclose($fp);

	}

	function parseLinks($childNodes, $stage = null)
	{

		$i = 1;
		$container = array();
		foreach($childNodes as $link) {
			if($stage != null) {
				$level = "$stage$i";
			} else {
				$level = $i;
			}
			if ($link->nodeType == 1 && $link->nodeName == "link") { 

				$container[$level]['title'] = $link->getAttribute('title');
				$container[$level]['url']   = $link->getAttribute('url');
				$container[$level]['desc']  = $link->getAttribute('desc');

				if($link->hasChildNodes()) {
					$container[$level]['sub'] = $this->parseLinks($link->childNodes, $level);
				}
				$i++;
			}

		}

		return $container;

	}

	function menuLink($link)
	{
		$title  = $link->getAttribute('title');
		$url    = $link->getAttribute('url');
		$desc   = $link->getAttribute('desc');

		$args = array();
		$params = $link->getElementsByTagname('param');
		foreach($params as $param) {
			$key = $param->getAttribute('name');
			$value = $param->getAttribute('value');
			$args[$key] = $value;
		}
		return sprintf('<a href="%s" title="%s">%s</a>', $url, $desc, $title);
	}

	public function end()
	{
		// important remove the menu from the session
		$this->menu = null;
	}

	public function setMenuFile($file)
	{
		$this->menuFile = PEACH_DATA_DIR."/Menu/".$file;
	}

	public function setEditFile($file)
	{
		$this->editFile = PEACH_DATA_DIR."/Menu/".$file;
		$this->editFileCompiled = $this->menuFile.".php";
	}

}

?>
