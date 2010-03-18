<?php

/**
 * Defines the base for a filter. The Filter-class extends the Controller -class so we have access to methods
 * such as ->set() and ->redirect()
 */
abstract class Filter extends Controller {
	
	/**
	 * The filter function is called on classes extending Filter. If the filter should
	 * stop the processing of this request, it needs to return false (note, the
	 * comparison is done with ===).
	 */
	public abstract function processFilter($script, $function, $parameters, $input);
	
}

?>
