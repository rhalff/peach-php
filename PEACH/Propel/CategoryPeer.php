<?php
// The parent class
require_once 'PEACH/Propel/om/BaseCategoryPeer.php';

// The object class
include_once 'PEACH/Propel/Category.php';

/** 
 * The skeleton for this class was autogenerated by Propel on:
 *
 * [Thu Jun  3 20:29:29 2004]
 *
 *  You should add additional methods to this class to meet the
 *  application requirements.  This class will only be generated as
 *  long as it does not already exist in the output directory.
 *
 * @package PEACH.Propel 
 */
class CategoryPeer extends BaseCategoryPeer {

    public function doForm($obj = null)
    {
        // Create an HTML_QuickForm instance, and return to the current request uri
        $form = new HTML_QuickForm(__METHOD__, 'POST', $_SERVER['REQUEST_URI']);
        $form->addElement('header', 'edit', 'Category');
        $form->addElement('text', 'title', 'Title');
        $form->addElement('textarea', 'description', 'Description');

        $c = new Criteria();
        $c->add(ImagePeer::FLAVOUR, serialize(array('thumb')));
        $c->addAscendingOrderByColumn(ImagePeer::LABEL);
        $results = ImagePeer::doSelect($c);

        $images = array();
        $images['none'] = "- no image -";
        // is this really necessary ?
        foreach($results as $row) {
            $images[$row->getId()] = $row->getLabel();
        }
        $form->addElement('select', 'image_id', 'Image', $images);
        //$form->addElement('text', 'parent_id', 'Parent_id'); 
        //$form->addElement('text', 'children', 'Children');

        /*
           Just for a reminder to add this
           $form->addRule('label', 'You must provide a title for this category', 'required');
           $form->addRule('description', 'You must provide a description for this category', 'required');
         */

        if (is_object($obj)) {
            $defaultValues = array();
            $defaultValues['title'] = $obj->getTitle();
            $defaultValues['description'] = $obj->getDescription();
            $defaultValues['image_id'] = $obj->getImage_id();
            $defaultValues['parent_id'] = $obj->getParent_id();
            $defaultValues['children'] = $obj->getChildren();

            // set the defaults 		
            $form->setDefaults($defaultValues);
        }

        $form->addElement('submit', 'submit', 'Continue');

        return $form;

    }

}