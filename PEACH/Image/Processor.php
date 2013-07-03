<?php

require_once 'PEACH/Image/Filter.php';
require_once 'PEACH/Image/Filter/Doughnut.php';
require_once 'PEACH/Image/Filter/Gamma.php';
require_once 'PEACH/Image/Filter/Greyscale.php';
require_once 'PEACH/Image/Filter/Rotate.php';
require_once 'PEACH/Image/Filter/Shadowedbevel.php';
require_once 'PEACH/Image/Filter/Spidersweb.php';
require_once 'PEACH/Image/Filter/Sephia.php';
require_once 'PEACH/Image/Filter/Dashedline.php';
require_once 'PEACH/Image/Filter/Dropshadow.php';
require_once 'PEACH/Image/Filter/Roundedges.php';

define('DEFAULT_OUTPUT_TYPE', 'png');

/**
 * PEACH_Image_Processor
 * Only receives a file location and modifies the image
 * on success it returns the new file name, on failure false
 * (if it's a valid image the old file is not unlinked).
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Processor.php,v 1.4 2004/06/07 21:49:42 rhalff Exp $
 * @access public 
 */
class PEACH_Image_Processor
{
    var $outputHeight;
    var $outputWidth;
    var $maxWidth;
    var $outputType = 'png';
    var $maxHeight;
    var $filters = array();
    var $imgObject;
    var $tempNamePrefix = 'PEACH_Image';
    var $tempDir = '/tmp';

    /**
     * PEACH_Image_Processor::PEACH_Image_Processor()
     * 
     * @param object $imgObject 
     */
    function PEACH_Image_Processor(&$imgObject)
    {
        if (!is_object($imgObject))
        {
            PEAR::RaiseError("PEACH_Image_Processor::PEACH_Image_Processor() $imgObject is not an Object", null, PEAR_ERROR_TRIGGER, E_USER_ERROR);
        } 

        if (!file_exists($imgObject->getFile()))
        {
            PEAR::RaiseError("PEACH_Image_Processor::PEACH_Image_Processor() File doesn't exist", null, PEAR_ERROR_TRIGGER, E_USER_ERROR);
        } 
        // default ouput dimensions are the same as
        // the input image.
        $this->outputHeight = $imgObject->getHeight();
        $this->outputWidth = $imgObject->getWidth(); 
        // load default settings from config file
        $config = &new PEACH_Config;
        $config->load($this);

        /* the references are very imporant because the file is changed
         * if we are working with a copy and a commit is done outside this code on the
         * imgObject, the old data will be saved, with the old file location
         */
        $this->imgObject = $imgObject;
    } 

    /**
     * PEACH_Image_Processor::process()
     * 
     * Process the image.
     * 
     * @return string 
     */
    function process()
    {
        $cmd = 'imageCreateFrom' . $this->imgObject->getExtension(); 
        // Read the input image
        $oldFile = $this->imgObject->getFile();
        $inputHandle = $cmd($oldFile); 
        // Create an output image handle
        // (check if this is really necessary...)
        $outputHandle = imageCreateTrueColor($this->outputWidth, $this->outputHeight); 
        // fill the new image

        imageCopyResampled($outputHandle,
            $inputHandle,
            0, 0, 0, 0,
            ImageSX($outputHandle),
            ImageSY($outputHandle),
            ImageSX($inputHandle),
            ImageSY($inputHandle)
            ); 

        // loop through the filters for this image
        for($j = 0; $j < count($this->filters); $j++)
        {
            $this->filters[$j]->setInput($inputHandle);
            $this->filters[$j]->setOutput($outputHandle);
            $this->filters[$j]->setWidth($this->outputWidth);
            $this->filters[$j]->setHeight($this->outputWidth);
            $this->filters[$j]->apply();
/*
        $outputHandle = imageCreateTrueColor($this->outputWidth, $this->outputHeight); 

        imageCopyResampled($outputHandle,
            $inputHandle,
            0, 0, 0, 0,
            ImageSX($outputHandle),
            ImageSY($outputHandle),
            ImageSX($inputHandle),
            ImageSY($inputHandle)
            ); 
*/


        } 

        // tell the imageObject the new image dimensions
        $this->imgObject->setWidth($this->outputWidth);
        $this->imgObject->setHeight($this->outputHeight); 
        // determine output command.
        // (relies on extension and gd function type to be the same.)
        $cmd = "image" . $this->outputType;

        if (function_exists($cmd))
        { 
            // write the new image to a temporary location
            $tmp_file = tempnam($this->tempDir, $this->tempNamePrefix); 
            // Creates a file with a unique filename in the specified directory.
            // If the directory does not exist, tempnam() may generate a file
            // in the system's temporary directory, and return the name of that.
            $cmd($outputHandle, $tmp_file); 
            // will automatically import the new file into the
            // object and delete the temporary file
            if ($this->imgObject->importFile($tmp_file))
            { 
                // write the update image info to the database
                $this->imgObject->commit(); 
                // also unlink the old file, tmp_file is deleted by importfile
                unlink($oldFile);

                return "Image succesfully processed";
            } 
            else
            {
                return PEAR::RaiseError("PEACH_Image_Processor::process() Failed to update the image.", null, PEAR_ERROR_RETURN);
            } 
        } 
        else
        {
            return PEAR::RaiseError("PEACH_Image_Processor::process() writing of the image failed, $cmd is not a valid gd image function", null, PEAR_ERROR_RETURN);
        } 
        // reset the filter array.
        // makes it possible to call process several times..
        $this->filters = array();
    } 

    /**
     * PEACH_Image_Processor::addFilter()
     * 
     * Register a filter to the image processor
     * 
     * @param  $filterName 
     * @param array $settings 
     */
    function addFilter($filterName, $settings = array())
    {
        $filter = 'PEACH_Image_Filter_' . $filterName;
        if(class_exists($filter)) {
        $this->filters[] = new $filter($settings);
        } else {
            PEAR::RaiseError("PEACH_Image_Processor::addFilter() unKnown Class $filter.", PEAR_ERROR_TRIGGER, E_USER_NOTICE);

        }
    } 

    /**
     * PEACH_Image_Processor::setDimensions()
     * 
     * Shorthand to set dimensions.
     * 
     * @param  $width 
     * @param  $height 
     */
    function setDimensions($width, $height)
    {
        $this->setOutputWidth($width);
        $this->setOutputHeight($height);
    } 

    /**
     * PEACH_Image_Processor::setMaxWidth()
     * 
     * set the maxium width of the output image.
     * 
     * @param  $width 
     */
    function setMaxWidth($width)
    {
        $this->maxWidth = $width;

        if ($this->outputWidth > $this->maxWidth)
        {
            $perc = $this->maxWidth / $this->outputWidth;
            $this->outputWidth = $this->maxWidth;
            $this->outputHeight = round($this->outputHeight * $perc);
        } 
    } 

    /**
     * PEACH_Image_Processor::setMaxHeight()
     * 
     * set the maximum height of the output image.
     * 
     * @param  $height 
     */
    function setMaxHeight($height)
    {
        $this->maxHeight = $height;
    } 

    /**
     * PEACH_Image_Processor::setOutputHeight()
     * 
     * Set the height of the output image.
     * 
     * @param  $height 
     */
    function setOutputHeight($height)
    {
        $this->outputHeight = $height;
    } 

    /**
     * PEACH_Image_Processor::setOutputWidth()
     * 
     * set the width of the output image.
     * 
     * @param  $width 
     */
    function setOutputWidth($width)
    {
        $this->outputWidth = $width;
    } 

    /**
     * PEACH_Image_Processor::scaleToWidth()
     * 
     * Calculate the new image Dimension and set
     * the values.
     * 
     * The actual resizing is done by ImageCopyResampled in
     * PEACH_Image_Processor::process()
     * 
     * @param  $width 
     * @see PEACH_Image_Processor::scaleToWidth()
     */
    function scaleToWidth($width)
    {
        $perc = $width / $this->outputWidth;
        $this->outputWidth = $width;
        $this->outputHeight = round($this->outputHeight * $perc);
    } 

    /**
     * PEACH_Image_Processor::setOutputType()
     * 
     * set the output type must be a valid gd output type.
     * 
     * @param  $type 
     * @return string 
     */
    function setOutputType($type)
    {
        if ($this->_isSupportedOutputType($type))
        {
            $this->outputType = $type;
        } 
        else
        {
            if ($this->_isSupportedOutputType(DEFAULT_OUTPUT_TYPE))
            {
                $this->outputType = DEFAULT_OUTPUT_TYPE;
                PEAR::RaiseError("PEACH_Image_Processor::setOutputType() $type is not supported defaulting to $this->defaultOutputType", null, PEAR_ERROR_TRIGGER, E_USER_NOTICE);
            } 
            else
            {
                PEAR::RaiseError("PEACH_Image_Processor::setOutputType() unable to set default output type $this->defaultOutputType . Is GD Installed?", null, PEAR_ERROR_TRIGGER, E_USER_ERROR);
            } 
        } 
    } 

    /**
     * PEACH_Image_Processor::setTempDir()
     * 
     * @param  $dir
     */

    function setTempDir($dir) 
    {
        $this->tempDir = $dir;

    }
    /**
     * PEACH_Image_Processor::setTempNamePrefix()
     * 
     * @param  $prefix
     */

    function setTempNamePrefix($prefix)
    {
        $this->prefix = $prefix;

    }

    /**
     * PEACH_Image_Processor::setOutputType()
     * 
     * set the output type must be a valid gd output type.
     * 
     * @param  $type 
     * @return string 
     */

    /**
     * PEACH_Image_Processor::_getSupportedGdOutputTypes()
     * 
     * Detect supported output types of this gd library
     * 
     * gd_info() returns:
     * 
     * array(9) {
     * ["GD Version"]=>
     * string(24) "bundled (2.0 compatible)"
     * ["FreeType Support"]=>
     * bool(false)
     * ["T1Lib Support"]=>
     * bool(false)
     * ["GIF Read Support"]=>
     * bool(true)
     * ["GIF Create Support"]=>
     * bool(false)
     * ["JPG Support"]=>
     * bool(false)
     * ["PNG Support"]=>
     * bool(true)
     * ["WBMP Support"]=>
     * bool(true)
     * ["XBM Support"]=>
     * bool(false)
     * }
     * 
     * @return string 
     */
    function _isSupportedOutputType($type)
    {
        $gdinfo = gd_info();

        switch ($type)
        {
            case "gif":
                if ($gdinfo["GIF Create Support"] === true)
                {
                    return true;
                } 
                else
                {
                    return false;
                } 
                break;
            case "jpeg":
                if ($gdinfo["JPG Support"] === true)
                {
                    return true;
                } 
                else
                {
                    return false;
                } 
                break;
            case "png":
                if ($gdinfo["PNG Support"] === true)
                {
                    return true;
                } 
                else
                {
                    return false;
                } 
                break;
            default:
                return false;
                break;
        } // switch		
    } 
} 

?>
