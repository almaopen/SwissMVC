<?

class Temp82d0f0fa8551de8b7eb5ecb65eae0261_1Controller extends Controller
{

	public function func() {
	
		$model = Model::getModel("User");
		if($model->save($this->input)) {
			$this->redirect("/foo/login");
			return;
		} else {
			list($foo, $control, $method) = explode("/", "/foo/bar");
			MVC::executeController
				(
					$control,
					$method,
					array(),
					"/foo/bar",
					array(),
					"",
					array("MVC_CURRENT_MODEL" => $model)
				);
			exit();			
		}
	
	}

}