<?class ModelCache_User extends ModelCache {
public function ModelCache_User() {
	$this->modelSource = 'User';
$this->modelStructure['id'] = array();
$this->modelStructure['id']['is_id'] = true;
$this->modelStructure['id']['type'] = 'int';
$this->modelStructure['id']['null'] = false;
$this->modelStructure['id']['maxlength'] = 20;
$this->modelStructure['id']['default'] = null;
$this->modelStructure['username'] = array();
$this->modelStructure['username']['is_id'] = false;
$this->modelStructure['username']['type'] = 'varchar';
$this->modelStructure['username']['null'] = false;
$this->modelStructure['username']['maxlength'] = 255;
$this->modelStructure['username']['default'] = null;
$this->modelStructure['password'] = array();
$this->modelStructure['password']['is_id'] = false;
$this->modelStructure['password']['type'] = 'varchar';
$this->modelStructure['password']['null'] = false;
$this->modelStructure['password']['maxlength'] = 255;
$this->modelStructure['password']['default'] = null;
$this->modelStructure['email'] = array();
$this->modelStructure['email']['is_id'] = false;
$this->modelStructure['email']['type'] = 'varchar';
$this->modelStructure['email']['null'] = true;
$this->modelStructure['email']['maxlength'] = 255;
$this->modelStructure['email']['default'] = null;
}

}?>
