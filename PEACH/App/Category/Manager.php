<?php
require_once 'PEACH/App.php';
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';
include_once 'PEACH/Propel/Category.php';

/**
 * PEACH_App_Category
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Manager.php,v 1.1 2004/06/08 16:32:30 rhalff Exp $
 * @access public
 **/

class PEACH_App_Category extends PEACH_App {

	private $id;

    /**
     * PEACH_App_Category::public_editForm()
     * 
     * 
     * @return 
     */

    function public_editForm()
    {
	    $Category= CategoryPeer::retrieveByPK($this->id);
            $form = CategoryPeer::doForm($Category);
	    if ($form->validate()) {
		    $this->setTemplate('saved.tpl');
		    $form->freeze();
		    $form->process(array($Category, 'saveForm'));
		    $this->output->msg = "Category saved."; 
		    $this->output->back  = PEACH_Site::instance()->Link('Back', 'Category', 'list');
	    } else {
		    $this->setTemplate('dynform.tpl');
		    $renderer = new HTML_QuickForm_Renderer_Object(true); 
		    $form->accept($renderer); 
		    $this->output->form = $renderer->toObject();
	    } 

    }

    /**
     * PEACH_App_Category::public_addForm()
     * 
     * @return 
     */
    function public_addForm()
    {
	    $Category = new Category();
            $form = CategoryPeer::doForm();
	    if ($form->validate()) {
		    $this->setTemplate('saved.tpl');
		    // Form is validated, then freezes the data
		    $form->freeze();
		    $form->process(array($Category, 'saveForm'));
		    $this->output->msg = "Category saved."; 
		    $this->output->back  = PEACH_Site::instance()->Link('Back', 'Category', 'list');

	    } else {
		    $this->setTemplate('dynform.tpl');
		    $renderer = new HTML_QuickForm_Renderer_Object(true); 
		    $form->accept($renderer); 
		    $this->output->form = $renderer->toObject();
	    } 

    }
    
    function public_delete()
    {
        $this->setTemplate('deleted.tpl');

        $crit = new Criteria();
        $crit->add(CategoryPeer::ID, $this->id);
        CategoryPeer::doDelete($crit);

        $this->output->msg = "Deleted";
        $this->output->back  = PEACH_Site::instance()->Link('Back', 'Category', 'list');
    }

    function public_view()
    {
            $this->setTemplate('view.tpl');
	    $Category= CategoryPeer::retrieveByPK($this->id);
	    $this->output = $Category;

    }

    function public_list()
    {

        $this->setTemplate("list.tpl");

        // "peer" class is a static class that handles things like queries
        $c = new Criteria();
        $c->add(CategoryPeer::TITLE, "%", Criteria::LIKE);
        $c->setLimit(10); // just in case we keep running this script :)
        $this->output->items = CategoryPeer::doSelect($c);
        $this->output->addLink = PEACH_Site::instance()->Link('Add', 'Category', 'addForm');
    }

    public function setId($id)
    {
        $this->id = $id;
    }


} 

?>
