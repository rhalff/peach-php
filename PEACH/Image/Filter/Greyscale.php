<?php

require_once 'PEACH/Image/Filter.php';

/**
 * PEACH_Image_Filter_Greyscale
 * 
 * Greyscales an image.
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Greyscale.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public 
 */
class PEACH_Image_Filter_Greyscale extends PEACH_Image_Filter
{


    var $r = '38';
    var $g = '36';
    var $b = '26';

    /**
     * PEACH_Image_Filter_Greyscale::apply()
     * 
     * @return 
     */

    function apply()
    {
        $rt = $this->r+$this->b+$this->g;
        $rr = ($this->r == 0) ? 0 : 1/($rt/$this->r);
        $br = ($this->b == 0) ? 0 : 1/($rt/$this->b);
        $gr = ($this->g == 0) ? 0 : 1/($rt/$this->g);
        $height = imagesy($this->outputHandle);
        $width  = imagesx($this->outputHandle);
        for( $i = 0; $i <= $height; $i++ )
        {
            for( $x = 0; $x <= $width; $x++ )
            {
                $pxrgb = imagecolorat($this->outputHandle, $x, $i);
                $rgb = ImageColorsforIndex( $this->outputHandle, $pxrgb );
                $newcol = ($rr*$rgb['red'])+($br*$rgb['blue'])+($gr*$rgb['green']);
                $setcol = ImageColorAllocate( $this->outputHandle, $newcol, $newcol, $newcol );
                imagesetpixel( $this->outputHandle, $x, $i, $setcol );
            }
        }
    }


    function setR($r)
    {
        $this->r = $r;
    }
    function setG($g)
    {
        $this->g = $g;

    }
    function setB($b)
    {
        $this->b = $b;
    }

} 

?>
