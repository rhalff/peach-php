<?php

require_once 'PEACH/Image/Filter.php';

/**
 * PEACH_Image_Filter_Dashedline
 * 
 * Applies Dashedline to an image
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Dashedline.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public 
 */
class PEACH_Image_Filter_Dashedline extends PEACH_Image_Filter
{
    /**
     * line color 
     * 
     * @var float 
     */
    var $color = '#000000';

    /**
     * Space between lines
     * 
     * @var integer 
     */
    var $step = 1;

    /**
     * PEACH_Image_Filter_Dashedline::apply()
     * 
     * Apply this filter to the image
     */
    function apply()
    {

        $w  = imagecolorallocate($this->outputHandle, 255, 255, 255);
        $red = imagecolorallocate($this->outputHandle, 255, 0, 0);

        /* Draw a dashed line, 5 red pixels, 5 white pixels */
        $style = array($red, $red, $red, $red, $red, $w, $w, $w, $w, $w);
        imagesetstyle($this->outputHandle, $style);

        $width = imagesx($this->outputHandle); 
        $height = imagesy($this->outputHandle); 

        for($i=0; $i<$height; $i++) {
            imageline($this->outputHandle, 0, $i, $width, $i, IMG_COLOR_STYLED);
            $i += $this->step;
        }
    } 

    /**
     * PEACH_Image_Filter_Dashedline::setInputDashedline()
     * 
     * @param  $gamma 
     */
    function setInputDashedline($gamma)
    {
        $this->inputDashedline = $gamma;
    } 

    /**
     * PEACH_Image_Filter_Dashedline::setOutputDashedline()
     * 
     * @param  $gamma 
     */
    function setOutputDashedline($gamma)
    {
        $this->inputDashedline = $gamma;
    } 
} 
