<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

require_once 'PEAR.php';
require_once 'PEACH/Item.php';

/**
 * PEACH_File
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: File.php,v 1.4 2004/06/07 19:42:38 rhalff Exp $
 * @access public
 **/

class PEACH_File extends PEACH_Item
{
    /*
	*  The filesize in bytes
    */
    protected $size;
	
    /*
	* Absolute path to the file
    */
    protected $absolutepath;

    /*
	* The weblocation to this file
    */
    protected $webpath; 
	
    /*
	* Name of the current file without the extension.
    */
    protected $name;
	
    /*
	* File extension
    */
    protected $extension;
	
    /*
	* mime type of this file
    */
    protected $mime;

    /**
     * PEACH_File::PEACH_File()
	 * 
	 * Constructor, init the parent constructor and set the id and table.
	 * 
	 * PHPWS Item will load the image properties from the database,
	 * it also takes care of saving and removing the file.
     * 
     * @param $id
     * @return 
     **/
    function __construct($id = null)
    {
	// must be called !
	parent::__construct($id);
    } 

    /**
     * PEACH_File::getFile()
     * 
	 * Compiled absolute filename, not a property of this class.
	 * 
     * @return 
     **/
    function getFile()
    {
        return sprintf("%s/%s.%s",
            $this->getAbsolutePath(),
            $this->getName(),
            $this->getExtension()
            );
    } 

    /**
     * PEACH_File::setSize()
	 * 
	 * set the filesize.
     * 
     * @return 
     **/
    function setSize()
    {

	// filesize can come from db or xml etc..
        $this->size = filesize($this->getFile());
        //$this->size = $size;
    } 

    /**
     * PEACH_File::setAbsolutePath()
	 * 
	 * Absolute file location.
     * 
     * @param $path
     * @return 
     **/
    function setAbsolutePath($path)
    {
        $this->absolutepath = $path;
    } 

    /**
     * PEACH_File::setWebPath()
	 * 
	 * Relative file location
     * 
     * @param $path
     * @return 
     **/
    function setWebPath($path)
    {
        $this->webpath = $path;
    } 

    /**
     * PEACH_File::setName()
	 * 
	 * set the file name without the extension
     * 
     * @param $name
     * @return 
     **/
    function setName($name)
    {
        $this->name = $name;
    } 

    /**
     * PEACH_File::setExtension()
	 * 
	 * set the file extension
     * 
     * @param $extension
     * @return 
     **/
    function setExtension($extension)
    {
        $this->extension = $extension;
    } 

    /**
     * PEACH_File::setMime()
	 * 
	 * set the mime type
     * 
     * @param $mime
     * @return 
     **/
    function setMime($mime)
    {
        $this->mime = $mime;
    } 

    /**
     * PEACH_File::importFile()
	 * 
	 * import the file, should be extended to handle the file type 
	 * you want to import. 
     * 
     * @param $file
     * @param boolean $delTemp
     * @return 
     **/
    function importFile($file, $delTemp = true)
    { 
    } 

    /**
     * PEACH_File::getSize()
     * 
     * return the file size
     * 
     * @return string
     **/
    function getSize()
    {
	    return $this->size;
    } 

    /**
     * PEACH_File::getAbsolutePath()
	 * 
	 * return the absolute path
     * 
     * @return string
     **/
    function getAbsolutePath()
    {
        return $this->absolutepath;
    } 

    /**
     * PEACH_File::getWebPath()
	 * 
	 * Return the webPath of this file
     * 
     * @return string
     **/
    function getWebPath()
    {
        return $this->webpath;
    } 

    /**
     * PEACH_File::getName()
	 * 
	 * Return the filename of this file
	 * this is the name without the extension..
     * 
     * @return string
     **/
    function getName()
    {
        return $this->name;
    } 

    /**
     * PEACH_File::getExtension()
	 * 
	 * return the file extension
     * 
     * @return string
     **/
    function getExtension()
    {
        return $this->extension;
    } 

    /**
     * PEACH_File::getMime()
	 * 
	 * return the mimeType of this file
     * 
     * @return string
     **/
    function getMime()
    {
        return $this->mime;
    } 

} 

?>
