<?class ModelCache_mvc2 extends ModelCache {
public function ModelCache_mvc2() {
	$this->modelSource = 'mvc2';
$this->modelStructure['id'] = array();
$this->modelStructure['id']['is_id'] = true;
$this->modelStructure['id']['type'] = 'int';
$this->modelStructure['id']['null'] = false;
$this->modelStructure['id']['maxlength'] = 20;
$this->modelStructure['id']['default'] = null;
$this->modelStructure['message'] = array();
$this->modelStructure['message']['is_id'] = false;
$this->modelStructure['message']['type'] = 'varchar';
$this->modelStructure['message']['null'] = true;
$this->modelStructure['message']['maxlength'] = 255;
$this->modelStructure['message']['default'] = null;
$this->modelStructure['mvc1_id'] = array();
$this->modelStructure['mvc1_id']['is_id'] = false;
$this->modelStructure['mvc1_id']['type'] = 'int';
$this->modelStructure['mvc1_id']['null'] = true;
$this->modelStructure['mvc1_id']['maxlength'] = 20;
$this->modelStructure['mvc1_id']['default'] = null;
}

}?>
