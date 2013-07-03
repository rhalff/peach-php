<?php

require_once 'PEACH/Image/Filter.php';

/**
 * PEACH_Image_Filter_Dropshadow
 * 
 * Applies Dropshadow to an image
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Dropshadow.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public 
 */
class PEACH_Image_Filter_Dropshadow extends PEACH_Image_Filter
{
    /**
     * Shadow width 
     * 
     * @var float 
     */
    var $shadow_width = '3';

    /**
     * Shadow colour 
     * 
     * must be in hex format 
     * 
     * @var string 
     */
    var $shadow_colour = '000000';

    /**
     * Background colour 
     * 
     * must be in hex format 
     * 
     * @var string 
     */
    var $background_color = 'FFFFFF';

    /**
     * PEACH_Image_Filter_Dropshadow::apply()
     * 
     * Apply this filter to the image
     */

    function apply()
    {
        /*
           $this->sw = $shadow_width;
           $this->sc = $shadow_colour;
           $this->sbr = $background_colour;
         */

        $width = imagesx($this->outputHandle);
        $height= imagesy($this->outputHandle);

        $sr = hexdec(substr($this->shadow_color,0,2));
        $sg = hexdec(substr($this->shadow_color,2,2));
        $sb = hexdec(substr($this->shadow_color,4,2));
        $sbrr = hexdec(substr($this->background_color,0,2));
        $sbrg = hexdec(substr($this->background_color,2,2));
        $sbrb = hexdec(substr($this->background_color,4,2));

        $dot = ImageCreate(1,1);
        $dotc = ImageColorAllocate($dot, $sr, $sg, $sb);
        $v = imagecreatetruecolor($width, $height);
        $sbc = imagecolorallocate($v, $sbrr, $sbrg, $sbrb);
        $rsw = $width-$this->shadow_width;
        $rsh = $height-$this->shadow_width;
        imagefill($v, 0, 0, $sbc);

        for($i = 0; $i < $this->shadow_width; $i++)
        {
            $s_opac = max(0, 90-($i*(100 / $this->shadow_width)));
            for($f = $this->shadow_width; $f < $rsh+$i+1; $f++)
            {
                imagecopymerge($v, $dot, $rsw+$i, $f, 0, 0, 1, 1, $s_opac);
            }
            for($g = $this->shadow_width; $g < $rsw+$i; $g++)
            {
                imagecopymerge($v, $dot, $g, $rsh+$i, 0, 0, 1, 1, $s_opac);
            }
        }
        imagecopyresampled($v, $this->outputHandle, 0, 0, 0, 0, $rsw, $rsh, $width, $height);
        imagecopyresampled($this->outputHandle, $v, 0, 0, 0, 0, $width, $height, $width, $height);
        imagedestroy($v);
        imagedestroy($dot);
    }

/**
 * PEACH_Image_Filter_Dropshadow::setShadowWidth()
 * 
 * @param  integer $width
 */
function setShadowWidth($width)
{
    $this->shadow_width = $width;
} 

/**
 * PEACH_Image_Filter_Dropshadow::setBackground()
 * 
 * @param string $color
 */
function setBackground($color)
{
    $this->shadow_background = $color;
} 
/**
 * PEACH_Image_Filter_Dropshadow::setColor()
 * 
 * @param string $color
 */
function setColor($color)
{
    $this->shadow_color = $color;
} 
} 
