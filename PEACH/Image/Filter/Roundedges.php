<?php

require_once 'PEACH/Image/Filter.php';

/**
 * PEACH_Image_Filter_Roundedges
 * 
 * Applies Roundedges to an image
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Roundedges.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public 
 */
class PEACH_Image_Filter_Roundedges extends PEACH_Image_Filter
{
    /**
     * Radial 
     * 
     * @var float 
     */
    var $edge_rad = '3';

    /**
     * Anti Alias 
     * 
     * must be in hex format 
     * 
     * @var string 
     */
    var $anti_alias = 1;

    /**
     * Background colour 
     * 
     * must be in hex format 
     * 
     * @var string 
     */
    var $background_color = 'FFFFFF';

    /**
     * PEACH_Image_Filter_Roundedges::apply()
     * 
     * Apply this filter to the image
     */

    function apply()
    {

        $width = imagesx($this->outputHandle);
        $height= imagesy($this->outputHandle);
        $aa = min(3,$this->anti_alias);
        $br = hexdec(substr($this->background_color,0,2));
        $bg = hexdec(substr($this->background_color,2,2));
        $bb = hexdec(substr($this->background_color,4,2));
        $dot = ImageCreateTrueColor(1,1);
        $dot_base = ImageColorAllocate($dot, $br, $bg, $bb);
        $this->zenitha = ImageColorClosest($this->outputHandle, $br, $bg, $bb);
        for($i = 0-$this->edge_rad; $i <= $this->edge_rad; $i++)
        {
            $ypos = ($i < 0) ? $i+$this->edge_rad-1 : $height-($this->edge_rad-$i);
            for($j = 0-$this->edge_rad; $j <= $this->edge_rad; $j++)
            {
                $xpos = ($j < 0) ? $j+$this->edge_rad-1 : $width-($this->edge_rad-$j);
                if($i !== 0 || $j !== 0)
                {
                    $d_dist = round(sqrt(($j*$j)+($i*$i)));
                    $opaci = ($d_dist < $this->edge_rad-$aa) ? 0 : max(0, 100-(($this->edge_rad-$d_dist)*33));
                    $opaci = ($d_dist > $this->edge_rad) ? 100 : $opaci;
                    ImageCopyMerge($this->outputHandle,$dot,$xpos,$ypos,0,0,1,1,$opaci);
            echo done;
                }
            }
        }
        imagedestroy($dot);
    }

/**
 * PEACH_Image_Filter_Roundedges::setEdgeRad()
 * 
 * @param  integer $rad
 */
function setEdgeRad($rad)
{
    $this->edge_rad = $rad;
} 

/**
 * PEACH_Image_Filter_Roundedges::setBackground()
 * 
 * @param string $background_color
 */
function setBackground($background_color)
{
    $this->background_color = $background_color;
} 
/**
 * PEACH_Image_Filter_Roundedges::AntiAlias()
 * 
 * @param string $anti_alias
 */
function setAntiAlias($anti_alias)
{
    $this->anti_alias = $anti_alias;
} 
} 
