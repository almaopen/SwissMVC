<?

class {name}Controller extends Controller
{

	public function {method}() {
	
		$model = Model::getModel("{model}");
		if($model->save($this->input)) {
			$this->redirect("{target}");
			return;
		} else {
			list($foo, $control, $method) = explode("/", "{source}");
			MVC::executeController
				(
					$control,
					$method,
					array(),
					"{source}",
					array(),
					"",
					array("MVC_CURRENT_MODEL" => $model)
				);
			exit();			
		}
	
	}

}