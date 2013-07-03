<?php

require_once 'PEACH/Image/Filter.php';

/**
 * PEACH_Image_Filter_Shadowedbevel
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Shadowedbevel.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public
 **/
class PEACH_Image_Filter_Shadowedbevel extends PEACH_Image_Filter
{
    var $edgewidth = '10';

    /**
     * PEACH_Image_Filter_Shadowedbevel::apply()
     * 
     **/
    function apply()
    {
        $dim = array();
        $dim[0] = $this->width;
        $dim[1] = $this->height;
        $this->width > $this->height ? $maxdim = $this->width : $maxdim = $this->height;

        if ($dim[0] > $dim[1])
        {
            $to_w = $maxdim;
            $to_h = round($dim[1] * ($maxdim / $dim[0]));
            $to_x = 0;
            $to_y = round($maxdim - $to_h) / 2;
        } 
        else
        {
            $to_h = $maxdim;
            $to_w = round($dim[0] * ($maxdim / $dim[1]));
            $to_y = 0;
            $to_x = round($maxdim - $to_w) / 2;
        } 
        // create a dark image and a light image
        $dark_shadey = ImageCreate($this->width, $this->height);
        $nadir = ImageColorAllocate($dark_shadey, 0, 0, 0);
        $light_shadey = ImageCreate($this->width, $this->height);
        $nadir = ImageColorAllocate($light_shadey, 255, 255, 255);

        for($edge_pixel = 0; $edge_pixel < $this->edgewidth; $edge_pixel++)
        { 
            // work out the opacity relative to how far from the edge we are
            $opacity = 100 - (($edge_pixel + 1) * (100 / $this->edgewidth)); 
            // merge a bit of the light image along the top and left side
            // merge a bit of the dark image along the base and right side
            ImageCopyMerge($this->outputHandle, $light_shadey, $to_x + ($edge_pixel-1),
                $to_y + ($edge_pixel-1), 0, 0, 1, $to_h - (2 * $edge_pixel), $opacity);
            ImageCopyMerge($this->outputHandle, $light_shadey, $to_x + ($edge_pixel-1),
                $to_y + ($edge_pixel-1), 0, 0, $to_w - (2 * $edge_pixel), 1, $opacity);
            ImageCopyMerge($this->outputHandle, $dark_shadey, $to_x + ($to_w - ($edge_pixel + 1)),
                $to_y + $edge_pixel, 0, 0, 1, $to_h - (2 * $edge_pixel), $opacity-20);
            ImageCopyMerge($this->outputHandle, $dark_shadey, $to_x + $edge_pixel, $to_y +
                ($to_h - ($edge_pixel + 1)), 0, 0, $to_w - (2 * $edge_pixel), 1, $opacity-20);
        } 
        // destroy the two new images that we used
        ImageDestroy($dark_shadey);
        ImageDestroy($light_shadey);
    } 

    /**
     * PEACH_Image_Filter_Shadowedbevel::setEdgeWidth()
     * 
     * @param $width
     **/
    function setEdgeWidth($width)
    {
        $this->edgewidth = $width;
    } 
} 
