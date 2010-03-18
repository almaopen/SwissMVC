<?php

/**
 * A ModelListener listens to changes in the data in the datasource. Implementations of ModelListener should subclass
 * ModelListener and overlload any necessary functions.
 */
abstract class ModelListener {
	
	/**
	 * Listener function that's called when data is updated
	 */
	public function update($data) { }
	
	/**
	 * Listener function that's called when data is deleted
	 */
	public function delete($data) { }
	
	/**
	 * Listener function that' called when data is inserted
	 */
	public function insert($data) { }
	
}
?>
