<?php

require_once 'PEACH/Image/Filter.php';

/**
 * PEACH_Image_Filter_Rotate
 * 
 * Rotates the image using a given angle in degree.
 * bgcolor specifies the color of the uncovered zone after the rotation.
 * 
 * Usage:
 *     $rotateFilter = new PEACH_Filter_Rotate($settings);
 *     $rotateFilter->setInput($inputHandle);
 *     $rotateFilter->setOutput($outputHandle);
 *     $rotateFilter->apply();
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Rotate.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public 
 */
class PEACH_Image_Filter_Rotate extends PEACH_Image_Filter
{
    var $rotation = '90';
    var $bgcolor = 0;

    /**
     * PEACH_Image_Filter_Rotate::apply()
     */
    function apply()
    {
        $this->outputHandle = imagerotate ($this->inputHandle, $this->rotation, $this->bgcolor) ;
    } 

    /**
     * PEACH_Image_Filter_Rotate::setRotation()
     * 
     * @param  $rotation 
     */
    function setRotation($rotation)
    {
        $this->rotation = $rotation;
    } 

    /**
     * PEACH_Image_Filter_Rotate::setBgcolor()
     * 
     * @param  $color 
     */
    function setBgcolor($color)
    {
        $this->bgcolor = $color;
    } 
} 

?>
