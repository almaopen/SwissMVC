<?

class Temp82d0f0fa8551de8b7eb5ecb65eae0261_2Controller extends Controller
{

	public function func() {
		foreach($_POST as $key => $value) {
			if(preg_match("#^_#", $key)) {
				unset($_POST[$key]);
			}
		}
		extract($_POST);
		?>
		
 <?
 if(count($this->User->load(array("username" => $username))) != 0) {
 	echo "Username ($username) already in use";
 } else {
 	echo "Username ($username) available, rock on!";
 } 
 ?>	 
 
		<?
		exit();
	}

}