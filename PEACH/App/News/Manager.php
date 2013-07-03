<?php
require_once 'PEACH/App.php';
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ObjectFlexy.php';
require_once 'PEACH/Propel/news.php';

/**
 * PEACH_App_News
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Manager.php,v 1.1 2004/06/10 22:19:59 rhalff Exp $
 * @access public
 **/

class PEACH_App_News extends PEACH_App {

	private $id;

    /**
     * PEACH_App_News::public_editForm()
     * 
     * 
     * @return 
     */

    function public_editForm()
    {
	    $news= newsPeer::retrieveByPK($this->id);
            $form = newsPeer::doForm($news);
	    if ($form->validate()) {
		    $this->setTemplate('saved.tpl');
		    $form->freeze();
		    $form->process(array($news, 'saveForm'));
		    $this->output->msg = "News saved."; 
		    $this->output->back  = PEACH_Site::instance()->Link('Back', 'News', 'list');
	    } else {
		    $this->setTemplate('dynform.tpl');
		    $renderer = new HTML_QuickForm_Renderer_ObjectFlexy($this->tpl);
            PEACH_Page::instance()->addScript($form->getValidationScript());
		    $form->accept($renderer); 
		    $this->output->form = $renderer->toObject();
	    } 

    }

    /**
     * PEACH_App_News::public_addForm()
     * 
     * @return 
     */
    function public_addForm()
    {
	    $news = new news();
            $form = newsPeer::doForm();
	    if ($form->validate()) {
		    $this->setTemplate('saved.tpl');
		    // Form is validated, then freezes the data
		    $form->freeze();
		    $form->process(array($news, 'saveForm'));
		    $this->output->msg = "news saved."; 
		    $this->output->back  = PEACH_Site::instance()->Link('Back', 'News', 'list');

	    } else {
		    $this->setTemplate('dynform.tpl');
		    $renderer = new HTML_QuickForm_Renderer_ObjectFlexy($this->tpl);
		    $form->accept($renderer); 
		    $this->output->form = $renderer->toObject();
	    } 

    }
    
    function public_delete()
    {
        $this->setTemplate('deleted.tpl');

        $crit = new Criteria();
        $crit->add(newsPeer::ID, $this->id);
        newsPeer::doDelete($crit);

        $this->output->msg = "Deleted";
        $this->output->back  = PEACH_Site::instance()->Link('Back', 'News', 'list');
    }

    function public_view()
    {
            $this->setTemplate('view.tpl');
	    $news= newsPeer::retrieveByPK($this->id);
	    $this->output = $news;

    }

    function public_list()
    {

        $this->setTemplate("list.tpl");

        // "peer" class is a static class that handles things like queries
        $c = new Criteria();
        $c->add(newsPeer::SUBJECT, "%", Criteria::LIKE);
        $c->setLimit(10); // just in case we keep running this script :)
        $this->output->items = newsPeer::doSelect($c);
        $this->output->addLink = PEACH_Site::instance()->Link('Add', 'News', 'addForm');
    }

    public function setId($id)
    {
        $this->id = $id;
    }


} 

?>
