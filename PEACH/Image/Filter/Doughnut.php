<?php

require_once 'PEACH/Image/Filter.php';


/**
 * PEACH_Image_Filter_Doughnut
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Doughnut.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public
 */


class PEACH_Image_Filter_Doughnut extends PEACH_Image_Filter
{
    /**
     * PEACH_Image_Filter_Doughnut::apply()
     * 
     * Logic taken from http://www.sitepoint.com/print/937
     * 
     * Creates a rounded frame inside the image.
     */
    function apply()
    {
        $dim = array();
        $dim[0] = ImageSX($this->outputHandle);
        $dim[1] = ImageSY($this->outputHandle);
        $maxdim = ImageSX($this->outputHandle);

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
        $dot = ImageCreate(1, 1);
        $zenith = ImageColorAllocate($dot, 255, 255, 255);

        for($ypos = 0;$ypos < $to_h;$ypos++)
        {
            for($xpos = 0;$xpos < $to_w;$xpos++)
            {
                $xdist = abs(($to_w / 2) - $xpos);
                if ($xdist == 0)
                {
                    $xdist = 0.01;
                } 
                $ydist = abs(($to_h / 2) - $ypos);
                $dist = sqrt(pow($xdist, 2) + pow($ydist, 2));
                $angl = atan($ydist / $xdist);
                $el_dist =
                sqrt(pow(abs(cos($angl) * $to_w / 2), 2) + pow(abs(sin($angl) * $to_h / 2), 2));
                if ($dist > $el_dist || $dist < $el_dist / 6)
                {
                    ImageCopyMerge($this->outputHandle, $dot, $xpos + $to_x, $ypos + $to_y, 0, 0, 1, 1, 100);
                } 
                else
                {
                    $dnut_dist = ($el_dist / 12) * 5;
                    $offset_dist = abs((($el_dist / 12) * 7) - $dist);
                    $uppy = sin(acos($offset_dist / $dnut_dist)) * $dnut_dist;
                    $opac = 100 - ((100 / $dnut_dist) * $uppy);
                    ImageCopyMerge($this->outputHandle, $dot, $xpos + $to_x, $ypos + $to_y, 0, 0, 1, 1, $opac);
                } 
            } 
        } 
        ImageDestroy($dot);
    } 
} 
