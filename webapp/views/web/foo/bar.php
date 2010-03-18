<h1>Sign up</h1>

<mvc:form autocreate-controller="true" model="User" redirect="/foo/login">

 <mvc:input type="text" name="username" onblur="javascript:$('#myAjaxContent').html('');foobar(this.value)">Select an username</mvc:input>
 <div id="myAjaxContent">
 </div>
 <mvc:ajax execute-on="foobar" parameters="username" target="#myAjaxContent">
 <?
 if(count($this->User->load(array("username" => $username))) != 0) {
 	echo "Username ($username) already in use";
 } else {
 	echo "Username ($username) available, rock on!";
 } 
 ?>	 
 </mvc:ajax>
  
 <mvc:password name="password">
  <mvc:label for="password">Select a password</mvc:label>
  <mvc:label for="password_verify">Repeat password</mvc:label>
 </mvc:password>
 
 <mvc:input type="text" name="email">E-mail address</mvc:input>
 
 <input type="submit" name="submit" value="Register!"/>

</mvc:form>

