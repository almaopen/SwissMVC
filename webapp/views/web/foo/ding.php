<form method="post" action="/foo/ding">
 <input type="text" name="username" value="" onblur="javascript:foobar(this.value);"/>
</form>

<div id="myAjaxContent">
 <mvc:ajax execute-on="foobar" parameters="username">
 <?
  if(count($this->User->load(array("username" => $username))) != 0) {
  	echo "Username ($username) already in use";
  } else {
  	echo "Username ($username) available, rock on!";
  } 
 ?>	 
 </mvc:ajax>
</div>
