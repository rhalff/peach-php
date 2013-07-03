<?php

require_once 'PEACH/App.php';

/**
 * PEACH_App_HelloWorld
 *
 * @package PEACH
 * @author Rob Halff <info@rhalff.com>
 * @copyright Copyright (c) 2004 Rob Halff
 * @version $Id: Manager.php,v 1.4 2004/06/07 18:50:45 rhalff Exp $
 * @access public
 **/

class PEACH_App_HelloWorld extends PEACH_App {

	private $counter = 0;

	/*
         * Class constuctor, PEACH_App has no constuctor
         * So no need to call parent:__construct() (Yet)
         */


	function __construct()
	{


	}

	/*
         * The start method is called everytime the object is accessed
         * you don't need to use this method but It could be usefull. 
         */
	public function start()
	{

		PEACH_Debug::instance()->msg(__METHOD__, "Start: Hello World Is being Accessed !");

	}

	/*
         * This is the actual method being accessed, it is prefixed with public_,
         * to destinct it from none public methods. Every method in a PEACH_App
         * prefixed with public_ can be accessed directly from an URI. 
         * To access this method though the browser you would use /HelloWorld/Greeting
         */
	public function public_Greeting()
	{
		$this->setTemplate('message.tpl');
		$this->output->title   = "Hello World !";
		$this->output->content = "This is the HelloWorld App!";

	}


	public function public_noOutput()
	{
		// will count how many times this method is accessed,
		// during the lifetime of this object inside the session.
		$this->counter++;

		// set output to false if this method will return no output.
		// usefull for counters, statistics logging etc.
		$this->output(false);

	}

	/*
         * The End method is called everytime the object is accessed
	 * And is the last method called.
         * you don't need to use this method but It could be usefull. 
         */
	public function end()
	{
		PEACH_Debug::instance()->msg(__METHOD__, "End: Leaving Hello World !");
	}

	/*
         * The End method is called everytime the object is accessed
	 * And is the last method called.
         * you don't need to use this method but It could be usefull. 
         */
	public function epilogue()
	{
		PEACH_Debug::instance()->msg(__METHOD__, "Epilogue: The page is displayed, any last wishes ?");
	}

	/*
         * Normal destructor, use it if you need to
         */

	function __desctruct()
	{


	}



}

?>
