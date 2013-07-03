<?php

require_once "PEACH/Image.php";

/**
 * PEACH_Image_List
 * 
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: List.php,v 1.3 2004/06/07 18:50:46 rhalff Exp $
 * @access public 
 */
class PEACH_Image_List
{
    /**
     * Sql from value
     * 
     * @var string 
     */
    private $from = '0';
    /**
     * Sql limit value
     * 
     * @var string 
     */
    private $limit;
    /**
     * An array of image Objects
     * 
     * @var array 
     */
    private $items = array();

    /**
     * Conditions column = value 
     * 
     * @var array 
     */
    private $conditions = array();

    private $dsn;
    private $table;

    /**
     * Constructor
     * 
     * Tries to read the configuration file
     * 
     * PEACH_Image_List::PEACH_Image_List()
     */
    function __construct()
    {
        $config = new PEACH_Config;
        $config->load($this);
    } 

    /**
     * PEACH_Image_List::getByFlavour()
	 * 
     * Expects an array of flavours
     * e.g. array('thumb') or array('thumb','greyscale','shadowed'); etc.
     * not a very good design yet.. flavour is set in PEACH_Image
     * 
     * @param  $flavour 
     * @return 
     */
    function getByFlavour($flavour)
    {
        if (!is_array($flavour))
        {
            $flav = array("$flavour");
        } 
        else
        {
            $flav = $flavour;
        } 

        //$this->_whereis = sprintf("where flavour = '%s'", serialize($flav));
	$this->setCondition('flavour', serialize($flav));
        $this->_query();
        return $this->items;
    } 

    /**
     * PEACH_Image_List::getMasters()
	 * 
	 * Select all non flavours.
     * 
     * @return array
     **/
    function getMasters()
    {
        //$this->_whereis = sprintf("where flavour = '%s'", serialize(array()));
	$this->setCondition('flavour', serialize(array()));	
        $this->_query();
        return $this->items;
    } 

    /**
     * PEACH_Image_List::getAll()
	 * 
	 * get all images
     * 
     * @return array
     **/
    function getAll()
    {
        $this->_query();
        return $this->items;
    } 
	
    /**
     * PEACH_Image_List::_query()
	 * 
	 * execute the query
     * 
     **/
    function _query()
    {
        if ($this->from != '' AND $this->limit != '')
        {
            $limit = sprintf("limit %s, %s", $this->from, $this->limit);
        } 
        else
        {
            $limit = '';
        } 

        $query = sprintf('select id from %s %s %s', $this->table, $this->getWhereIs(), $limit);
            $db = DB::connect($this->dsn);
            if (DB::isError($db)) {
                die($db->getMessage());
            }  

            $db->setFetchMode(DB_FETCHMODE_ASSOC);
            $res = $db->query($query);

            if (PEAR::isError($res))
            {
                die ($res->getMessage());
            } 
            while ($row = $res->fetchrow())
            {
                $this->items[] = &new PEACH_Image($row['id']);
            } 

            return $this->items;

    } 

    /**
     * PEACH_Image_List::getNext()
	 * 
	 * Not implemented yet.
     * 
     * @return 
     **/
    function getNext()
    {
    } 

    /**
     * PEACH_Image_List::setLimit()
	 * 
	 * set the sql limit
     * 
     * @param $limit
     **/
    function setLimit($limit)
    {
        $this->limit = $limit;
    } 

    function setCondition($column, $value)
    {
	    $this->conditions[] = "$column = '$value'";  
    }

    function getWhereIs()
    {
	    return "WHERE ".implode($this->conditions, ' AND ');
    }

    function setDsn($dsn)
    {
        $this->dsn = $dsn;
    }

    function setTable($table)
    {
        $this->table = $table;
    }


} 

