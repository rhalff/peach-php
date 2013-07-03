<?php

require_once 'PEACH/App.php';
require_once 'PEACH/Config.php';
require_once 'PEACH/Image/Editor.php';
require_once 'PEACH/Image/List.php';

/**
 * PEACH_App_Images
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Manager.php,v 1.2 2004/06/10 18:07:46 rhalff Exp $
 * @access public
 */

class PEACH_App_Images extends PEACH_App {
    private $imageEditor;
    private $currentImage;
    private $imageId;

    /**
     * PEACH_App_Images::public_list()
     * 
     */
    function public_list()
    {
        $this->template = 'imageList.tpl';
        $imageList = new PEACH_Image_List(); 

        // Only get the thumbs.. In the image form we defined to also make a thumb for
        // every uploaded image. In current design it must be an array..
        //$rows = $imageList->getMasters();
        $rows = $imageList->getByFlavour(array('thumb'));
        $images = '';
        $output = new StdClass;

        foreach($rows as $key => $imageObj) {

            $tags['label'] = $imageObj->getLabel();
            $tags['image'] = PEACH_Site::instance()->Link($imageObj->getHtml(),
                    'Images',
                    'view',
                    array("imageId" => $imageObj->getParentId()),
                    $imageObj->getLabel()
                    );
            $tags['edit'] = PEACH_Site::instance()->Link('Edit Image',
                    'Images', 
                    'editImage',
                    array("imageId" => $imageObj->getParentId())
                    );
            $output->images[] = $tags;
        } 
        if (empty($output->images)) {
            $output->noImages = true;
        } 
        $output->addLink = PEACH_Site::instance()->Link('Add Image',
                'Images', 
                'upload'
                );

        $this->output($output);
    } 

    public function public_upload()
    {
        $this->imageEditor = new PEACH_Image_Editor();
        $output = $this->imageEditor->imageForm();
        if (!isset($output->form)) {
            $output->back = PEACH_Site::instance()->Link('Back', 'Images', "list");
            $this->template = 'imageSaved.tpl';
        } else {
            $this->template = 'dynform.tpl';
        } 
        $this->output($output);
    } 

    /**
     * PEACH_App_Images::public_view()
     * 
     * @return 
     */
    public function public_view()
    {
echo $this->imageId;
        if (!empty($this->imageId)) {
            $this->template = "viewImage.tpl";
            $output = new StdClass;
            $this->currentImage = new PEACH_Image($this->imageId);
            $output->image = $this->currentImage->getHtml();
            $output->label = $this->currentImage->getLabel();
            $output->back = PEACH_Site::instance()->Link('Back', 'Images', 'list');
            $output->edit = PEACH_Site::instance()->Link('Edit', 'Images', 'edit');
            $output->remove = PEACH_Site::instance()->Link('Remove', 'Images', 'remove');
            $this->output($output);
        } else {
            $output = PEAR::RaiseError('PEACH_Image_Manager::public_viewImage() $this->image_id is not set', null, PEAR_ERROR_RETURN, E_USER_ERROR);
            $this->output($output);
        } 
    } 

    /**
     * PEACH_App_Images::public_remove()
     * 
     * remove the current image object
     * 
     */
    public function public_remove()
    {
        $this->imageEditor = new PEACH_Image_Editor($this->currentImage);

        $this->template = "removeImage.tpl";
        $output = new StdClass;
        $output->back = PEACH_Site::instance()->Link('Back', 'Images', "list");
        if (!$this->imageEditor->removeImage()) {
            $output->message = "Failed to remove this image";
        } else {
            $output->message = "Image removed from the database.";
        } 

        $this->output($output);
    } 

    /**
     * PEACH_App_Images::public_edit()
     * 
     * edit the current image object
     * 
     */
    public function public_edit()
    {
        if (!empty($this->imageId))
        {
            $imageToEdit =& new PEACH_Image($this->imageId);
            $this->imageEditor =& new PEACH_Image_Editor($imageToEdit);
            $obj = $this->imageEditor->imageForm();
            if (!isset($obj->form)) {
                $obj->saved = "Image saved.";
                $obj->back = PEACH_Site::instance()->Link('Back', 'Images', "list");
                $this->template = 'imageSaved.tpl';
            } else {
                $this->template = 'dynform.tpl';
            } 
            $this->output($obj);
        }
        else
        {
            $this->output =	PEAR::RaiseError('PEACH_Image_Manager::public_editImage() $this->image_id is not set', NULL, PEAR_ERROR_RETURN, E_USER_ERROR);
        }
    }

    /**
     * PEACH_App_Images::setImageId()
     * 
     * set the current Image Id 
     * 
     */

    public function setImageId($id)
    {
        $this->imageId = $id;
    } 
} 

?>
