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
	
	/**
	 * Find appropriate listeners
	 */
	public static function findListeners($model, $event) {
		
		$model = strtolower($model);
		
		if(!empty(AppConfiguration::$MODEL_LISTENERS[$model])) {
			
			$model_listeners = array();
			foreach(AppConfiguration::$MODEL_LISTENERS[$model] as $listener) {
				if(in_array($event, $listener["events"])) {
					$model_listeners[] = $listener;
				}
			}
			return $model_listeners;
		}
		
		// No listeners found
		return null;
		
	}
	
	/**
	 * Invoke listeners
	 */
	public static function invokeListeners($listeners, $data, $event) {
		// Fire off events to the listeners
		foreach($listeners as $listener) {
			$listener_class = $listener["listener"];
			$listener_file = WEBAPP_ROOT . "/models/listeners/" . Inflector::decamelize($listener["listener"]) . ".php";
			if(!class_exists($listener_class)) {
				require_once($listener_file);
			}
			$listener_impl = new $listener_class();
			foreach($data as $item) {
				// Invoke the listener
				$listener_impl->{$event}($item);
			}
			
		}
	}
	
}
?>
