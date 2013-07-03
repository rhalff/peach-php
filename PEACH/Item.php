<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

require_once 'PEAR.php';


/**
 * PEACH_Item
 *
 * Is used By File which is extended By Image
 * Also is used by categories.
 *
 * Common things the above have in common:
 * - dsn
 * - table
 * - id 
 * - label
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Item.php,v 1.3 2004/06/07 18:50:45 rhalff Exp $
 * @access public
 **/

class PEACH_Item
{
    /*
     *	 unique id of the file 
    */
    protected $id;
	 
    /*
     *  The file label/title
    */
    protected $label;
	
    /*
     * DSN for database storage 
    */
    protected $dsn;
    /*
     * Table for database storage 
    */
    protected $table;

    /**
     * PEACH_Item::PEACH_Item()
	 * 
	 * Constructor, init the parent constructor and set the id and table.
	 * 
     * 
     * @param $id
     * @return 
     **/
    function __construct($id = null)
    {
	$config = new PEACH_Config;
        $config->load($this);

        if($id != null) { 

            $db = DB::connect($this->dsn);
            if (DB::isError($db)) {
                die($db->getMessage());
            }       

            $db->setFetchMode(DB_FETCHMODE_ASSOC);
            $query = sprintf("select * from %s where id = '%s'", $this->table, $id);
            $res = $db->query($query);
            if (DB::isError($res)) {
                die($res->getMessage());
            }       

            if($res->numRows() > 0 ) {         
            foreach($res->fetchRow() as $key=>$value) {
		// gracefully stolen from phpwebsite Database.php
		 if (preg_match("/^[aO]:\d+:/", $value)) {
                    $value = unserialize($value);
                }       

                $this->{$key} = $value; 
            }       

            }

        }   

    } 

    /**
     * PEACH_Item::setId()
     * 
     * Set the id of this Item/File/Image or whatever is extended
     * 
     * @param $path
     * @return 
     **/
    final public function setId($id)
    {
        $this->id = $id;
    } 

    final public function getId($id)
    {
        $this->id = $id;
    } 

    /**
     * PEACH_Item::setLabel()
	 * 
	 * set the label 
     * 
     * @param $label
     * @return 
     **/
    final public function setLabel($label)
    {
        $this->label= $label;
    } 

    /**
     * PEACH_File::getLabel()
     * 
     * return the label of this file 
     * 
     * @return string
     **/
    final public function getLabel()
    {
	    return $this->label;
    } 

    /**
     * PEACH_File::getTitle()
	 * 
	 * return the title of this file.
     * 
     * @return string
     **/
    final public function getTitle()
    {
        return $this->title;
    } 

    final public function setDsn($dsn)
    {
        $this->dsn = $dsn;
    } 

    final public function getDsn()
    {
        return $this->dsn;
    } 

    final public function setTable($table)
    {
        $this->table = $table;
    } 

    final public function getTable()
    {
        return $this->table;
    } 

} 

?>
