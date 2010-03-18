<?class ModelCache_mvc1 extends ModelCache {
public function ModelCache_mvc1() {
	$this->modelSource = 'mvc1';
$this->modelStructure['id'] = array();
$this->modelStructure['id']['is_id'] = true;
$this->modelStructure['id']['type'] = 'int';
$this->modelStructure['id']['null'] = false;
$this->modelStructure['id']['maxlength'] = 20;
$this->modelStructure['id']['default'] = null;
$this->modelStructure['name'] = array();
$this->modelStructure['name']['is_id'] = false;
$this->modelStructure['name']['type'] = 'varchar';
$this->modelStructure['name']['null'] = true;
$this->modelStructure['name']['maxlength'] = 255;
$this->modelStructure['name']['default'] = null;
$this->modelStructure['email'] = array();
$this->modelStructure['email']['is_id'] = false;
$this->modelStructure['email']['type'] = 'varchar';
$this->modelStructure['email']['null'] = true;
$this->modelStructure['email']['maxlength'] = 255;
$this->modelStructure['email']['default'] = null;
$this->modelStructure['description'] = array();
$this->modelStructure['description']['is_id'] = false;
$this->modelStructure['description']['type'] = 'text';
$this->modelStructure['description']['null'] = true;
$this->modelStructure['description']['maxlength'] = -1;
$this->modelStructure['description']['default'] = null;
}

}?>
