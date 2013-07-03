<?php

require_once 'PEACH/Image/Filter.php';

/**
 * PEACH_Image_Filter_Spidersweb
 * 
 * Logic taken from http://www.sitepoint.com/print/937
 * 
 * Creates a spidersweb on top of the image.
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Spidersweb.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public 
 */
class PEACH_Image_Filter_Spidersweb extends PEACH_Image_Filter
{
    /**
     * PEACH_Image_Filter_Spidersweb::apply()
     * 
     * @return 
     */
    function apply()
    {
        $dim = array();
        $dim[0] = $this->width;
        $dim[1] = $this->height;
        $maxdim = ($this->width > $this->height) ? $this->width : $this->height;

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

        $zenith = ImageColorAllocate($this->outputHandle, 255, 255, 255);
        for($draw = 0;$draw < $to_h;$draw += 12)
        {
            ImageLine($this->outputHandle, $to_x, ($to_h + $to_y), ($to_w + $to_x),
                (($to_h - $draw) + $to_y), $zenith);
        } 
        for($draw = 0;$draw < $to_w;$draw += 12)
        {
            ImageLine($this->outputHandle, $to_x, ($to_h + $to_y), ($draw + $to_x),
                $to_y, $zenith);
        } 
        for($draw = 1;$draw < 14;$draw++)
        {
            ImageArc($this->outputHandle, $to_x, ($to_h + $to_y), $draw * ($to_w / 4), $draw *
                ($to_h / 4), 270, 0, $zenith);
        } 
    } 
} 

?>
