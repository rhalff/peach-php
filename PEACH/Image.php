<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

require_once "PEAR.php";
require_once "DB.php";
require_once "PEACH/File.php";

/**
* PEACH_Image
* 
* Usage:
* 
* $image = new Image();
* $image->setFile($file);
* 
* Optional:
* $image->setLabel("My Image");
* 
* @package PEACH
* @author Rob Halff <info@rhalff.com>
* @copyright Copyright (c) 2004 Rob Halff
* @version $Id: Image.php,v 1.3 2004/06/07 18:50:45 rhalff Exp $
* @access public 
*/
class PEACH_Image extends PEACH_File {
    /*
     * Title of this image
     * 
     * @var string
     */
    protected $label = "New Image";

    /*
     * Image width in pixels
     * 
     * @var integer
     */
    protected $width;

    /*
     * Image height in pixels
     * 
     * @var integer
     */
    protected $height;

    /*
     * NULL, thumb, medium, inverted etc.
     * 
     * Mainly to distinct images, to enable selection by thumbs etc.
     * 
     * @var string
     */
    protected $flavour = array();

    /*
     * Image id of the parent
     * 
     * Will make it possible to keep a relation between thumbs and original image,
     * or for greyscaled images be related to a source image etc.
     * 
     * @var integer
     */
    protected $parent_id;

    /*
     * This is an array of this image´s children.
     * 
     * @var array
     */
    protected $children = array();
    protected $fqdn;

    /**
    * PEACH_Image::PEACH_Image()
    * 
    * Constructor, set the image id if any and the table name.
    * 
    * call the init function from PHWS Item
    * 
    * @param integer $id 
    */
    function __construct($id = null)
    {
        // must be called !
        parent::__construct($id);
        // set default fqdn
       $this->setFqdn($_SERVER['SERVER_NAME']);
    }


    /**
    * PEACH_Image::remove()
    * 
    * Remove the image from the filesystem and the database.
    * 
    * @return string 
    */
    function remove()
    {
        $returnMsgs = array(); 
        // recursive function
        $returnMsgs = $this->removeChildren();

        /* unlink the image from the file system */
        $file = $this->getFile(); 
        // sometimes $file is empty
        if (file_exists($file) AND !is_dir($file)) {
            unlink($file);
        } else {
            // too bad just continue
            // return "Unable to unlink the image from the filesystem";
        } 

        /* remove entry from the database */
        if (!$this->kill()) {
            return PEAR::RaiseError("Unable to remove image from the database", null, PEAR_ERROR_RETURN);
        } 

        return "Image removed.";
    } 

    /**
    * PEACH_Image::removeParents()
    * 
    * Remove all the parents of this image.
    * 
    * origImage -> greyscaled -> resized thumb.
    * 
    * if called on the resized thumb image, will destroy the
    * greyscaled and original image. Keeps the current image.
    * 
    * @return string 
    */
    function removeParents()
    {
        if ($parent = &$this->getParent()) {
            /* not catching any output have to fix this
             * probably remove must return true or false instead of a string.
             */
            $parent->remove();
        } else {
            return "Parents Removed";
        } 
    } 

    /**
    * PEACH_Image::removeChildren()
    * 
    * Remove the children of this image
    * 
    * origImage -> greyscaled -> resized thumb.
    * 
    * If called on the original image, greyscaled and the thumb will be removed.
    * 
    * @return string array bool ?
    */
    function removeChildren()
    {
        $children = $this->getChildren();
        $msg = array();

        foreach ($children as $key => $imgObj) {
            $msg[] = $imgObj->remove();
        } 
        /*
           } else {
        // should return what ?
        $msg[] = 'no children';
        }
        return $msg;
         */

    } 

    /**
    * PEACH_Image::removeChildByFlavour()
    * 
    * Remove the child the child by Flavour
    * 
    * @return boolean 
    */
    function removeChildByFlavour($flavour)
    {
        $children = $this->getChildren();

        foreach ($children as $key => $imgObj) {
            if ($imgObj->getFlavour() == $flavour) {
                // TODO: decide what remove() should return.
                $imgObj->remove();
                return true;
            } 
        } 
    } 

    /**
    * PEACH_Image::setWidth()
    * 
    * set the width of the image
    * 
    * @param  $width 
    */
    function setWidth($width)
    {
        $this->width = $width;
    } 

    /**
    * PEACH_Image::setHeight()
    * 
    * set the height of the image
    * 
    * @param  $height 
    */
    function setHeight($height)
    {
        $this->height = $height;
    } 

    /**
    * PEACH_Image::importFile()
    * 
    * Import a file into this image.
    * Will automatically set the all image information exept the image label.
    * 
    * @param  $file 
    * @param boolean $delTemp whether to remove the input file
    * @return boolean 
    */
    function importFile($file, $delTemp = true)
    {
        if (file_exists($file)) {
            if (is_dir($file)) {
                return PEAR::raiseError("PEACH_Image::importFile $file is a directory.", null, PEAR_ERROR_RETURN);
            } 

            $imginfo = getimagesize($file);
            $translation_table = array();
            $translation_table[1] = 'gif';
            $translation_table[2] = 'jpeg';
            $translation_table[3] = 'png';
            $translation_table[4] = 'swf';
            $translation_table[5] = 'psd';
            $translation_table[6] = 'bmp';
            $translation_table[7] = 'tiff'; //(intel byte order)
            $translation_table[8] = 'tiff'; //(motorola byte order)
            $translation_table[9] = 'jpc';
            $translation_table[10] = 'jp2';
            $translation_table[11] = 'jpx';
            $translation_table[12] = 'jb2';
            $translation_table[13] = 'swc';
            $translation_table[14] = 'iff';
            $translation_table[15] = 'wbmp';
            $translation_table[16] = 'xbm';

            if ($imginfo[2] < 1) {
                // not an image
                return false;
            } else {
                $this->setExtension($translation_table[$imginfo[2]]);
                $this->setWidth($imginfo[0]);
                $this->setHeight($imginfo[1]);
                $this->setMime($imginfo['mime']); 
                // create a uniquename
                $this->setName(md5(uniqid(rand(), 1))); 
                // copy the the file to the new filename will use the directory where it's at.
                copy($file, $this->getFile());

                $this->setSize();

                if ($delTemp === true) {
                    // remove the old file
                    unlink($file);
                } 

                return true;
            } 
        } else {
            return false;
        } 
    } 

    /**
    * PEACH_Image::getFlavour()
    * 
    * Get the flavour/category of this image
    * 
    * origImage -> greyscaled -> resized thumb
    * 
    * resized thumb would have a flavour something like
    * array('greyscale','thumb')
    * 
    * @return array 
    */
    function getFlavour()
    {
        return $this->flavour;
    } 

    /**
    * PEACH_Image::setFlavour()
    * 
    * set the image flavour
    * 
    * @param  $flavour 
    */
    function setFlavour($flavour)
    {
        $this->flavour = $flavour;
    } 

    /**
    * PEACH_Image::setParentId()
    * 
    * set the parent id
    * 
    * @param  $id 
    */
    function setParentId($id)
    {
        $this->parent_id = $id;
    } 

    /**
    * PEACH_Image::getParentId()
    * 
    * get the parent id
    * 
    * @return integer .
    */
    function getParentId()
    {
        return $this->parent_id;
    } 

    /**
    * PEACH_Image::getParent()
    * 
    * get the parent image object of this image object.
    * 
    * @return object 
    */
    function getParent()
    {
        if (isset($this->parent_id)) {
            $class = get_class($this);
            $parentObj = &new $class($this->parent_id);
            return $parentObj;
        } else {
            return null;
        } 
    } 

    /**
    * PEACH_Image::getWidth()
    * 
    * get the image width
    * 
    * @return integer 
    */
    function getWidth()
    {
        return $this->width;
    } 

    /**
    * PEACH_Image::getHeight()
    * 
    * get the image height
    * 
    * @return integer 
    */
    function getHeight()
    {
        return $this->height;
    } 

    /**
    * PEACH_Image::getHtml()
    * 
    * get the image html
    * 
    * @return string 
    */
    function getHtml()
    {
        return sprintf('<img src="%s/%s.%s" width="%s" height="%s" alt="%s" />',
            $this->webpath,
            $this->name,
            $this->extension,
            $this->width,
            $this->height,
            $this->label
            );
    } 

    function toHtml()
    {

        return $this->getHtml();

    }

    /**
    * PEACH_Image::getChildren()
    * 
    * get all the children of this image
    * 
    * @return object 
    */
    function getChildren()
    {

        if (!is_array($this->children)) {
            return array();
        } else {
            $imgObjects = array();

            foreach ($this->children as $key => $imgId) {
                $imgObjects[] = &new PEACH_Image($imgId);
            } 

            return $imgObjects;
        } 
    } 

    /**
    * PEACH_Image::updateChildren()
    * 
    * Update a property for all the children of this image.
    * 
    * @param  $setMethod 
    * @param  $value 
    * @return string 
    */
    function updateChildren($setMethod, $value)
    {
        $children = &$this->getChildren();

        for($i = 0; $i < count($children); $i++) {
            if (method_exists($this, $setMethod)) {
                $children[$i]->$setMethod($value);
                $children[$i]->commit();
            } else {
                return PEAR::raiseError("PEACH_Image::updateChildren child method $setMethod doesn't exist.", null, PEAR_ERROR_RETURN);
            } 
        } 
    } 

    /**
    * PEACH_Image::addChild()
    * 
    * register an image Object as a child of this image.
    * 
    * @param  $id 
    * @return 
    */
    function addChild($id)
    {
        $this->children[] = $id;
    } 

    /**
    * PEACH_Image::__clone()
    * 
    * Creates a copy of this image Broken!?
    * 
    * @return object 
    */
    function __clone()
    {
        $this->importFile($that->getFile(), false); // false is important since we don't want to delete the imagefile from the image we are cloneing
        $this->setParentId($that->id); // remember the parent
        $this->commit(); 
        // tell this object there is a new child
        $that->addChild($this->id);
        $that->commit();
    } 

    /**
    * RHALFF_Image::__clone()
    * 
    * Creates a copy of this image
    * 
    * @return object 
    */
    public function _fakeClone()
    {
        $thisclass = get_class($this);
        $newImage = &new $thisclass;
        $newImage->label = $this->label;
        $newImage->importFile($this->getFile(), false); // false is important since we don't want to delete the imagefile from the image we are cloneing
        $newImage->setParentId($this->id); // remember the parent
        $newImage->commit();

        /*
     * tell this object there is a new child
     */

        $this->addChild($newImage->id);
        $this->commit();

        return $newImage;
    } 

    public function commit()
    {
        if (!isset($this->dsn)) {
            return PEAR::raiseError("PEACH_Image::commit dsn not set for database..", null, PEAR_ERROR_RETURN);
        } 

        $db = DB::connect($this->dsn);
        if (DB::isError($db)) {
            return $db;
        } 

        if (empty($this->name)) {
            return PEAR::raiseError("PEACH_Image::commit name is empty.", null, PEAR_ERROR_RETURN);
        } 

        if (empty($this->absolutepath)) {
            return PEAR::raiseError("PEACH_Image::commit absolutepath is empty.", null, PEAR_ERROR_RETURN);
        } 

        if (empty($this->extension)) {
            return PEAR::raiseError("PEACH_Image::commit extension is empty.", null, PEAR_ERROR_RETURN);
        } 

        $fields_values = array("label" => $this->label,
            "width" => $this->width,
            "height" => $this->height,
            "flavour" => serialize($this->flavour),
            "parent_id" => $this->parent_id,
            "children" => serialize($this->children),
            "size" => $this->size,
            "absolutepath" => $this->absolutepath,
            "webpath" => $this->webpath,
            "fqdn" => $this->fqdn,
            "name" => $this->name,
            "extension" => $this->extension,
            "mime" => $this->mime
            );

        if (!isset($this->id)) {
            $newId = $db->nextId($this->table);

            if (DB::isError($newId)) {
                die($newId->getMessage());
            } 

            $this->id = $newId;
            $fields_values['id'] = $this->id;
            $res = $db->autoExecute($this->table, $fields_values, DB_AUTOQUERY_INSERT);
        } else {
            $res = $db->autoExecute($this->table, $fields_values, DB_AUTOQUERY_UPDATE, "id = '{$this->id}'");
        } 

        if (DB::isError($res)) {
            die($res->getMessage());
        } else {
            return true;
        } 
    } 

    /**
     * PEACH_File::setFqdn()
         *
         * set the Fully Qualified Domain name
     *
     * @param unknown $fqdn
     * @return
     **/
    function setFqdn($fqdn)
    {
        $this->fqdn = $fqdn;
    }

    /**
     * PEACH_File::getFqdn()
         *
         * return the Fully Qualified domain name
         * Not reallly used yet, but should make it possible to save
         * the location of a remote file to the database, returning something
         * in getWebPath like http www.example.com example.gif
         *
     *
     * @return string
     **/
    function getFqdn()
    {
        return $this->fqdn;
    }


    function kill()
    {
        if (!isset($this->dsn)) {
            //return PEAR::raiseError("PEACH_Image::kill dsn not set for database..", null, PEAR_ERROR_RETURN);
			return false;
        } 

        $db = DB::connect($this->dsn);
        if (DB::isError($db)) {
            //return $db;
			return false;
        } 

        if (!isset($this->id)) {
            //return PEAR::raiseError("PEACH_Image::kill id is not set for this image, can't delete..", null, PEAR_ERROR_RETURN);
			return false;
        }
		
		$SQL = sprintf("DELETE FROM %s where id = '%s'", $this->table, $this->id);
		$res = $db->query($SQL);
		
        if (DB::isError($res)) {
            //return $res;
			return false;
        }
		
		return true;
    } 

} 

?>
