<?php

require_once 'PEACH/Image/Filter.php';

/**
 * PEACH_Image_Filter_Sephia
 * 
 * Logic taken from some class at phpclasses.org
 * 
 * It basicly turn the image grayscale and add some defined
 * tint on it: R += 30, G += 43, B += -23, so it will appear a very old picture.
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Sephia.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public 
 */
class PEACH_Image_Filter_Sephia extends PEACH_Image_Filter
{
    /**
     * how much Red to add
     * 
     * @var int $tintR
     */
    var $tintR = '30';
    /**
     * Sepia - make image pretends to be older than it really is
     * 
     * It basicly turn the image grayscale and add some defined
     * tint on it: R += 30, G += 43, B += -23, so it will appear a very old picture.
     * 
     * @param int $tintR how much Red to add
     * @param int $tintG how much Green to add
     * @param int $tintB how much Blue to add
     * @param float $rateR the rate of Red in the new color ($rateR/1000)
     * @param float $rateG the rate of Green in the new color ($rateG/1000)
     * @param float $rateB the rate of Blue in the new color ($rateB/1000)
     * @param int $whiteness add more some int on the new color ($newcolor += $whiteness)
     */
    /**
     * how much Green to add
     * 
     * @var int $tintG
     */
    var $tintG = '43';
    /**
     * how much Blue to add
     * 
     * @var int $tintB
     */
    var $tintB = '-23';
    /**
     * the rate of Red in the new color
     * 
     * @var int $rateR
     */
    var $rateR = '.229';
    /**
     * the rate of Gree in the new color
     * 
     * @var int $rateG
     */
    var $rateG = '.587';
    /**
     * the rate of Blue in the new color
     * 
     * @var int $tintB
     */
    var $rateB = '.114';
    /**
     * add some whiteness to the new color ($newcolor += $whiteness)
     * 
     * @var int $whiteness
     */
    var $whiteness = '3 0';

    function apply()
    {
        for ($x = 0; $x < imagecolorstotal($this->inputHandle); $x++)
        {
            $src_colors = imagecolorsforindex($this->inputHandle, $x);
            $new_color = min(255, abs($src_colors["red"] * $this->rateR + $src_colors["green"] * $this->rateG + $src_colors["blue"] * $this->rateB) + $this->whiteness);
            $r = min(255, $new_color + $this->tintR);
            $g = min(255, $new_color + $this->tintG);
            $b = min(255, $new_color + $this->tintB);
            imagecolorset($this->outputHandle, $x, $r, $g, $b);
        } 
    } 

    function setRotation($rotation)
    {
        $this->rotation = $rotation;
    } 

    function setBgcolor($color)
    {
        $this->bgcolor = $color;
    } 
} 

?>
