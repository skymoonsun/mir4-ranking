<?php 

require_once(__DIR__ . '/Db.class.php');
class Crud {

	private $db;

	public $variables;

	public function __construct($data = array()) {
		$this->db =  new DB();	
		$this->variables  = $data;
	}

	public function __set($name,$value){
		if(strtolower($name) === $this->pk) {
			$this->variables[$this->pk] = $value;
		}
		else {
			$this->variables[$name] = $value;
		}
	}

	public function __get($name)
	{	
		if(is_array($this->variables)) {
			if(array_key_exists($name,$this->variables)) {
				return $this->variables[$name];
			}
		}

		return null;
	}


	public function save($id = "0") {
		$this->variables[$this->pk] = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];

		$fieldsvals = '';
		$columns = array_keys($this->variables);

		foreach($columns as $column)
		{
			if($column !== $this->pk)
			$fieldsvals .= $column . " = :". $column . ",";
		}

		$fieldsvals = substr_replace($fieldsvals , '', -1);

		if(count($columns) > 1 ) {

			$sql = "UPDATE " . $this->table .  " SET " . $fieldsvals . " WHERE " . $this->pk . "= :" . $this->pk;
			if($id === "0" && $this->variables[$this->pk] === "0") { 
				unset($this->variables[$this->pk]);
				$sql = "UPDATE " . $this->table .  " SET " . $fieldsvals;
			}

			return $this->exec($sql);
		}

		return null;
	}

	public function create() { 
		$bindings   	= $this->variables;

		if(!empty($bindings)) {
			$fields     =  array_keys($bindings);
			$fieldsvals =  array(implode(",",$fields),":" . implode(",:",$fields));
			$sql 		= "INSERT INTO ".$this->table." (".$fieldsvals[0].") VALUES (".$fieldsvals[1].")";
		}
		else {
			$sql 		= "INSERT INTO ".$this->table." () VALUES ()";
		}
        
        return $this->exec($sql);
        
	}

	public function delete($id = "") {
		$id = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];

		if(!empty($id)) {
			$sql = "DELETE FROM " . $this->table . " WHERE " . $this->pk . "= :" . $this->pk. " LIMIT 1" ;
		}

		return $this->exec($sql, array($this->pk=>$id));
	}

	public function find($id = "") {
		$id = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];

		if(!empty($id)) {
			$sql = "SELECT * FROM " . $this->table ." WHERE " . $this->pk . "= :" . $this->pk . " LIMIT 1";	
			
			$result = $this->db->row($sql, array($this->pk=>$id));
			$this->variables = ($result != false) ? $result : null;
		}
	}

	public function search($fields = array(), $sort = array(), $limit = array(), $likeand = array(), $likeor = array(), $comp = array(), $likeand2 = array()) {

		$bindings = empty($fields) ? $this->variables : $fields;

		$sql = "SELECT * FROM " . $this->table;

		if (!empty($bindings)) {
			$fieldsvals = array();
			$columns = array_keys($bindings);
			foreach($columns as $column) {
				if(gettype($fields[$column]) == "string"){
					$fieldsvals [] = $column . " = ". "'" . $fields[$column] . "'";
				}else{
					$fieldsvals [] = $column . " = " . $fields[$column];
				}


			}

			$sql .= " WHERE " . implode(" AND ", $fieldsvals);
		}

		if (!empty($comp)) {
			$compvals = array();
			$columns = array_keys($comp);
			foreach ($columns as $column) {
				$compvals [] = $column . " " . "'" . $comp[$column] . "'";

			}

			if(!empty($fields)){
				$sql .= " AND " . implode(" AND ", $compvals);
			}else{
				$sql .= " WHERE " . implode(" AND ", $compvals);
			}

		}

		if (!empty($likeand)) {
			$likeandvals = array();
			$columns = array_keys($likeand);
			foreach ($columns as $column) {
				$likeandvals [] = $column . " LIKE " . "'" . $likeand[$column] . "'";

			}

			if(!empty($fields)){
				$sql .= " AND " . implode(" AND ", $likeandvals);
			}else{
				$sql .= " WHERE " . implode(" AND ", $likeandvals);
			}

		}

		if (!empty($likeand2)) {
			$likeand2vals = array();

			$main_key = array_keys($likeand2);

			foreach ($main_key as $mkey){
				$columns = array_keys($likeand2[$mkey]);
				foreach ($columns as $column) {
					$likeand2vals [] = $mkey . " LIKE " . "'" . $likeand2[$mkey][$column] . "'";

				}

				if(!empty($fields)){
					$sql .= " AND " . implode(" AND ", $likeand2vals);
				}else{
					$sql .= " WHERE " . implode(" AND ", $likeand2vals);
				}
			}

		}

		if (!empty($likeor)) {
			$likeorvals = array();
			$columns = array_keys($likeor);
			foreach ($columns as $column) {
				$likeorvals [] = $column . " LIKE " . "'" . $likeor[$column] . "'";

			}

			if(!empty($fields)){
				$sql .= " AND " . implode(" OR ", $likeorvals);
			}else{
				$sql .= " WHERE " . implode(" OR ", $likeorvals);
			}

		}

		if (!empty($sort)) {
			$sortvals = array();
			foreach ($sort as $key => $value) {
				$sortvals[] = $key . " " . $value;
			}
			$sql .= " ORDER BY " . implode(", ", $sortvals);
		}

		if (!empty($limit)) {
			$limitvals = array();
			foreach ($limit as $key => $value) {
				$limitvals[] = $key . ", " . $value;
			}
			$sql .= " LIMIT " . implode(", ", $limitvals);
		}

		//return $sql;

		return $this->exec($sql);
	}
    

	public function all(){
		return $this->db->query("SELECT * FROM " . $this->table);
	}
	

	private function exec($sql, $array = null) {
		
		if($array !== null) {
	
			$result =  $this->db->query($sql, $array);	
		}
		else {
	
			$result =  $this->db->query($sql, $this->variables);	
		}
		
	
		$this->variables = array();

		return $result;
	}

}
?>
