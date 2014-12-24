<?php namespace NestedPages\Form\Handlers;
/**
* Redirect to Listing with Specified Sorting Options Applied
*/
class ListingSortHandler {

	/**
	* URL to redirect to
	* @var string
	*/
	private $url;


	public function __construct()
	{
		$this->setURL();
		$this->redirect();
	}


	/**
	* Build the URL to Redirect to
	*/
	private function setURL()
	{
		$this->url = sanitize_text_field($_POST['page']);
		$this->setOrderBy();
		$this->setOrder();
	}


	/**
	* Set Order by parameters
	*/
	private function setOrderBy()
	{
		$allowed = array('menu_order', 'date', 'title'); // prevent tomfoolery
		if ( !in_array($_POST['np_orderby'], $allowed) ) $this->url .= '&orderby=menu_order';
		$this->url .= '&orderby=' . sanitize_text_field($_POST['np_orderby']);
	}


	/**
	* Set Order parameters
	*/
	private function setOrder()
	{
		$allowed = array('ASC', 'DESC'); // prevent tomfoolery
		if ( !in_array($_POST['np_order'], $allowed) ) $this->url .= '&order=DESC';
		$this->url .= '&order=' . sanitize_text_field($_POST['np_order']);
	}


	/**
	* Redirect to new URL
	*/
	private function redirect()
	{
		header('Location:' . $this->url);
	}

}