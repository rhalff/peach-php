<?php

require_once 'PEACH/Config.php';

/**
 * PEACH_Image_Filter
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Filter.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public 
 */
class PEACH_Image_Filter
{
    /**
     * gd input handle
     * 
     * @var resource 
     */
    var $inputHandle;

    /**
     * gd output handle
     * 
     * @var resource 
     */
    var $outputHandle;
    /**
     * Settings passed to this filter
     * 
     * @var array 
     */
    var $settings;

    /**
     * Image width of the gd resource
     * 
     * @var string 
     */
    var $width;
    /**
     * Image height of the gd resource
     * 
     * @var string 
     */
    var $height;

    /**
     * PEACH_Image_Filter::PEACH_Image_Filter()
     * 
     * Base class for an image filter, must be extended.
     * 
     * @param array $settings 
     */
    function PEACH_Image_Filter($settings = array())
    {
        if (empty($settings))
        { 
            // try to load the config file, else default to hardcoded properties
            $config = new PEACH_Config;
            $config->load($this);
        } 
        else
        {
            foreach ($settings as $key => $value)
            {
                $method = 'set' . $key; // e.g. setinputgamma()
                if (method_exists($this, $method))
                { 
                    // set the value
                    $this->$method($value);
                } 
                else
                {
                    PEAR::RaiseError('PEACH_Image_Filter::PEACH_Image_Filter() $method doesn\'t exists', NULL, PEAR_ERROR_TRIGGER, E_USER_NOTICE);
                } 
            } 
        } 
    } 

    /**
     * PEACH_Image_Filter::setInput()
     * 
     * import the gd input resource
     * 
     * @param  $gdInput 
     */
    function setInput(&$gdInput)
    {
        $this->inputHandle = &$gdInput;
    } 

    /**
     * PEACH_Image_Filter::setOutput()
     * 
     * import the gd output resource
     * 
     * @param  $gdOutput 
     */
    function setOutput(&$gdOutput)
    {
        $this->outputHandle = &$gdOutput;
    } 

    /**
     * PEACH_Image_Filter::setWidth()
     * 
     * set the width of the gd image resource
     * 
     * @param  $width 
     */
    function setWidth($width)
    {
        $this->width = $width;
    } 

    /**
     * PEACH_Image_Filter::setHeight()
     * 
     * set the height of the gd image resource
     * 
     * @param  $height 
     */
    function setHeight($height)
    {
        $this->height = $height;
    } 
} 

?>
