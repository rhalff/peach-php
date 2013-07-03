<?php

require_once 'PEACH/Image/Filter.php';

/**
 * PEACH_Image_Filter_Gamma
 * 
 * Applies Gamma to an image
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Gamma.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public 
 */
class PEACH_Image_Filter_Gamma extends PEACH_Image_Filter
{
    /**
     * Input gamma
     * 
     * must be a value between 0 and 1
     * 
     * @var float 
     */
    var $inputGamma = 0.1;

    /**
     * Output gamma
     * 
     * must be a value between 0 and 1
     * 
     * @var float 
     */
    var $outputGamma = 0.3;

    /**
     * PEACH_Image_Filter_Gamma::apply()
     * 
     * Apply this filter to the image
     */
    function apply()
    {
        imagegammacorrect ($this->outputHandle, $this->inputGamma, $this->outputGamma) ;
    } 

    /**
     * PEACH_Image_Filter_Gamma::setInputGamma()
     * 
     * @param  $gamma 
     */
    function setInputGamma($gamma)
    {
        $this->inputGamma = $gamma;
    } 

    /**
     * PEACH_Image_Filter_Gamma::setOutputGamma()
     * 
     * @param  $gamma 
     */
    function setOutputGamma($gamma)
    {
        $this->outputGamma = $gamma;
    } 
} 
