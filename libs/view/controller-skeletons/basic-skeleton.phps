<?

class {name}Controller extends Controller
{

	public function {method}() {
		foreach($_POST as $key => $value) {
			if(preg_match("#^_#", $key)) {
				unset($_POST[$key]);
			}
		}
		extract($_POST);
		?>
		{code}
		<?
		exit();
	}

}