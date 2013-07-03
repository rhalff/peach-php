<?php

require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ITStatic.php';
require_once 'HTML/Template/ITX.php';
require_once 'PEACH/Page.php';
require_once 'PEACH/Config.php';
require_once 'PEACH/Template.php';
require_once 'PEACH/Image/Processor.php';
require_once 'PEACH/Image.php';

/**
* PEACH_Image_Editor
* 
* @package PEACH
* @author Rob Halff <info@rhalff.com>
* @copyright Copyright (c) 2004 Rob Halff
* @version $Id: Editor.php,v 1.4 2004/06/07 21:49:42 rhalff Exp $
* @access public 
*/

class PEACH_Image_Editor {
    /*
     * Object of the current Image
	 * 
	 * @var object
     */
    private $currentImage;

    /*
     * Maximum file size for uploaded images
	 * 
	 * @var string
     */
    private $maxFileSize = 150000;

    /*
     * Whether to enable saving images as a copy
	 * in the edit form.
	 * 
	 * @var bool 
     */
    private $enableSaveAsCopy;

    /*
     * Scale to this width
	 * 
	 * @var string
     */
    private $scaleToWidth = '450';

    /*
     * size of the thumbnail
	 * 
	 * Must be a string
	 * 
	 * @var string
     */
    private $thumbX = '150';

    /*
     * Height of the thumbnail
	 * 
	 * @var string
     */
    private $thumbY = '150';

    /*
     * Enable Filters 
	 * 
	 * @var bool 
     */
    private $enableFilters;
    /*
     * Filters to use 
     * 
     * @var array 
     */
    private $filters = array();

    /**
    * PEACH_Image_Editor::PEACH_Image_Editor()
    * 
    * @param object $currentImage 
    */
    function __construct($currentImage = null)
    {
        if (is_object($currentImage)) {
            $this->currentImage = $currentImage;
        } 

        $config = new PEACH_Config();
        $config->load($this);

    } 

    /**
    * PEACH_Image_Editor::removeImage()
    * 
    * remove the image.
    * 
    * @return string 
    */
    public function removeImage()
    {
        if (is_object($this->currentImage)) {
            
            if (!$this->currentImage->remove()) {
                return false;
            } else {
                return true;
            } 

        } else {
            // not an object
            return false;
        } 
    } 

    /**
    * PEACH_Image_Editor::imageForm()
    * 
    * Form to upload and modify the image
    * 
    * @return string 
    */

    public function imageForm()
    { 
        // Create an HTML_QuickForm instance, and return to the current request uri
        $upload = new HTML_QuickForm('form', 'POST', $_SERVER['REQUEST_URI']); 
        // Main form Header
        $upload->addElement('header', 'imageedit', 'Image Editor.'); 
        // Label for this image
        $upload->addElement('text', 'label', 'Label:'); 
        // If this current image is set, this means we are editting an image
        // otherwise we are creating a new Image
        if (is_object($this->currentImage)) {
            $defaultValues['label'] = $this->currentImage->getLabel(); 
            // set the current values of this image
            $upload->setDefaults($defaultValues); 
            // Save as copy
            if ($this->enableSaveAsCopy === true) {
                $upload->addElement('checkbox', 'save_as_copy', 'Save as copy.');
            } 
            // Add an option to delete this image
            $tags['remove_image'] = PEACH_Site::instance()->Link('Remove Image',
                'Images', 'Manager',
                'removeImage'
                );
        } else {
            // File element
            $upload->addElement('file', 'image', 'Upload your image'); 
            // File rules
            // $upload->addRule('upload', 'Upload is required', 'uploadedfile');
            $upload->addRule('image', 'You must pick an image to upload', 'required');
            $upload->addRule('image', 'File size should be less than ' . round($this->maxFileSize / 1024) . 'Kb', 'maxfilesize', $this->maxFileSize);
            $upload->addRule('image', 'Must be *.jpg, *.gif or *.png', '/\.(jpe?g|gif|png)$/i'); 
            // register isImage rule
            $upload->registerRule('isImage', 'callback', 'ruleIsImage', get_class($this));
            $upload->addRule('image', 'The file must be an image', 'isImage');
        } 

        if ($this->enableFilters == true) {

	    if(is_array($this->filters) AND !empty($this->filters)) {
            // TODO: what to do if we try to set a filter in the configfile that doesn't exists ? 
	/* Gamma Greyscale Rotate Sephia Shadowbevel Spidersweb Dashedline Dropshadow Roundedges */ 
		    foreach($this->filters as $filter) {
			    $upload->addElement('checkbox', "filters[$filter]", "$filter");
		    }
	    }

        } 
        // File rules
        // Submit button
        $upload->addElement('submit', 'submit', 'Continue'); 
        // Label rule
        $upload->addRule('label', 'You must provide a title for this image', 'required'); 
        // . Tries to validate the form
        if ($upload->validate()) {

		$result = '';
            // extremly ugly coding now
            $output = new StdClass; 
            // Form is validated, then freezes the data
            $upload->freeze();

            $values = $upload->getSubmitValues(true);

            if (is_object($this->currentImage)) {
                // update the image object
                if (isset($values['save_as_copy']) && $values['save_as_copy'] == '1') {
                    // create a new object, copy the file
                    // $newImage = clone $this->currentImage;
                    $newImage = $this->currentImage->_fakeClone();
                    $newImage->setLabel($values['label']);
                    $this->currentImage = $newImage;
                    $result = $this->currentImage->commit();
                } else {
                    // just update the titel
                    $this->currentImage->setLabel($values['label']); 
                    // update the these titles also
                    $this->currentImage->updateChildren('setLabel', $values['label']); 
                    // return if there are no filters selected.
                    if (!isset($values['filters'])) {
                        $result = $this->currentImage->commit();
                        return $output;
                    } else {
                        // remove the current thumb of this image, a new one will be created below,
                        // with the filters applied..
                        $this->currentImage->removeChildByFlavour(array('thumb'));
                    } 
                } 
            } else {
                // create the new image object
                $this->currentImage = new PEACH_Image();
                $this->currentImage->setLabel($values['label']);
                $this->currentImage->setName($values['image']['name']);
                $this->currentImage->importFile($values['image']['tmp_name']);
                $result = $this->currentImage->commit();
            } 
            // save the image to the db.
            if (PEAR::isError($result)) {
                die ($result->getMessage());
            } 
            // no matter what happends we have uploaded a valid image.
            $output->saved = "Image saved."; 
            // process the image.
            // if the processing failes, the old image object is kept
            // otherwise the image will be processed
            $imageProcessor = &new PEACH_Image_Processor($this->currentImage);
            $imageProcessor->scaleToWidth($this->scaleToWidth); 
            // apply the filters
            if (isset($values['filters']) AND is_array($values['filters'])) {
                foreach($values['filters'] as $filter => $value) {
                    $imageProcessor->addFilter($filter);
                } 
            } 

            $output->processed = $imageProcessor->process(); 
            // create the thumbnail as a child of the  master file
            // $thumb = &$this->currentImage->__clone();
            // $thumb = clone $this->currentImage;
            $thumb = $this->currentImage->_fakeClone();
            $thumb->setLabel($values['label']);
            $thumb->setFlavour(array('thumb')); 
            // make it a thumbnail
            // because the thumb knows it parent we can do a select on all thumbs
            // and when viewing.. we can select all parents of the thumb if they exists.
            $imageProcessor = new PEACH_Image_Processor($thumb);
            $imageProcessor->setDimensions($this->thumbX, $this->thumbY);
            $imageProcessor->process();

            return $output;
        } else {
            // very cool dynamic form template, tha power of oop, logic inside templates is good!
            // Only one template for all forms.
            $tpl = PEACH_Template::instance();

            $renderer = new HTML_QuickForm_Renderer_Object(true); 
            // give some elements aditional style informations
            $renderer->setElementStyle(array('ipwdTest' => 'green',
                    'iradYesNo' => 'fancygroup',
                    'name' => 'fancygroup'
                    ));
            $upload->accept($renderer); 
            // assign array with form data
            $view = new StdClass;
            $view->form = $renderer->toObject();
            return $view;
        } 
    } 

    /**
    * PEACH_Image_Editor::ruleIsImage()
    * 
    * @param  $data 
    * @return 
    */
    public function ruleIsImage($data)
    {
        if (
            ((isset($data['error']) && 0 == $data['error']) ||
                    (!empty($data['tmp_name']) && 'none' != $data['tmp_name'])) &&
                is_uploaded_file($data['tmp_name'])
                ) {
            $info = @getimagesize($data['tmp_name']);
            return is_array($info) && (1 == $info[2] || 2 == $info[2] || 3 == $info[2]);
        } else {
            return false;
        } 
    } 

    /**
    * PEACH_Image_Editor::enableSaveAsCopy()
    * 
    * @param  $bool 
    * @return 
    */
    public function enableSaveAsCopy($bool)
    {
        $this->enableSaveAsCopy = $bool;
    } 

    /**
    * PEACH_Image_Editor::setMaxFileSize()
    * 
    * @param  $size 
    */
    public function setMaxFileSize($size)
    {
        $this->maxFileSize = $size;
    } 

    /**
    * PEACH_Image_Editor::getMaxFileSize()
    * 
    * @return integer 
    */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    } 

    /**
    * PEACH_Image_Editor::setThumbX()
    * 
    * @param  $width 
    */
    public function setThumbX($width)
    {
        $this->thumbX = $width;
    } 

    /**
    * PEACH_Image_Editor::setThumbY()
    * 
    * @param  $height 
    */
    public function setThumbY($height)
    {
        $this->thumbY = $height;
    } 

    /**
    * PEACH_Image_Editor::setScaleToWidth()
    * 
    * @param  $width 
    */
    public function setScaleToWidth($width)
    {
        $this->scaleToWidth = $width;
    } 

    /**
    * PEACH_Image_Editor::setEnableFilters()
    * 
    * @param  $bool 
    */
    public function setEnableFilters($bool)
    {

        $this->enableFilters = $bool;
    } 

    /**
    * PEACH_Image_Editor::setEnableSaveAsCopy()
    * 
    * @param  $bool 
    */
    public function setEnableSaveAsCopy($bool)
    {
        $this->enableSaveAsCopy = $bool;
    } 

    public function setFilters($filters)
	{
		if(is_array($filters)) {
			$this->filters = $filters;
		}
	}
} 

?>
