<?php
/**
 * Sample Form Processing class for Checkfront API
 *
 * @author     Checkfront Inc <info@checkfront.com>
 * @category   HTML
 * @package    Form
 * @copyright  2008-2012 Checkfront Inc
*/
class Form {

	public $fields = array();
	private $msg = array('txt'=>'','type'=>'');

	private $types = array(
		'text'=>1,
		'p'=>1,
		'select'=>1,
		'textarea'=>1,
		'radio'=>1,
	);

	function __construct($data=array(),$values=array()) {
		$this->fields = $data;

		// set any values
		if(count($values)) {
			foreach($values as $field_id => $value) {
				if(isset($this->fields[$field_id])) {
					$this->fields[$field_id]['value'] = $value;
				}
			}
		}
	}

	public function msg($msg='',$type=''){
		if($msg) {
			$this->msg = array('txt'=>$msg,'type'=>$type);
		} elseif ($this->msg['txt']) {
			return "<p class='msg {$this->msg['type']}'>{$this->msg['txt']}</p>";
		}
	}



	public function render($field_id) {

		if(!empty($this->fields[$field_id])) {
			$field = $this->fields[$field_id];
		} else {
			return false;
		}

		if(!isset($field['define']) or !($type = $field['define']['layout']['type']) or !$this->types[$type]) {
			return false;
		}

		$try = "build_{$type}";
		if(method_exists($this,$try)) {
			return $this->{$try}($field_id,$field);
		}

	}

	private function build_text($id,$data) {
	   $html = "<input name='{$id}' id='{$id}' type='text' " . $this->build_val($data['value']) ;
	   if($this->fields[$id]['define']['required']) $html .= ' required="required"';
	   $html .= " />";
	   return $html;
	}

	private function build_p($id,$data) {
		$html = '<p>' . $data['value'] . "</p>";
		return $html;
	}

	private function build_val($val,$group='') {
		if($val) return " value='" . $this->escape($val) . "'";
	}

	private function build_select($id,$data) {
		$html ="<select name='{$id}' id='{$id}'>" . $this->build_select_options($data['define']['layout']['options'],$data['value'],!empty($data['define']['layout']['single'])) . "</select></li>";
		return $html;
	}

	private function build_radio($id,$data) {
		$html = $this->build_radio_group($data['define']['layout']['options'],$id,$data['value']);
		return $html;
	}

	private function build_checkbox($id,$data) {
		$html = "<input type='checkbox' name='{$name}' id='{$id}' value='1'";
		if($data['value']) $html .= ' checked="checked"';
		$html .= "/>";
		return $html;
	}

	private function build_textarea($id,$data) {
		$html = "<textarea name='{$id}' id='{$id}'>" . $this->escape($data['value'])  . "</textarea>";
		return $html;
	}

	private function build_radio_group($data,$id,$sel) {
		if(!is_array($data)) return false;
		$html = '';
		foreach($data as $key => $val) {
			$html .= "<input type='radio' name='{$id}' value='" . $this->escape($val) . "'";
			if($sel === $key) {
				$html .= " selected='selected'";
			}

			$html .= '/>' . $val . ' ';
		}
		return $html;
	}

	private function build_select_options($data,$selected=null,$single) {
		if(!is_array($data)) return false;
		$html = '';
		foreach($data as $key => $val) {
			$html .= '<option';
			if($single) {
				$key = $val;
			} else {
				$html .= ' value=\'' . $key . '\'';
			}
			if($selected == $key) {
				$html .= ' selected=\'selected\'';
			}
			$html .= '>' . $this->escape($val) . '</option>';
		}
		return $html;
	}

	public function escape($val,$strip_tags=0) {
		if($strip_tags) {
			$val = strip_tags($val);
		}
		return htmlentities($val,ENT_COMPAT,'UTF-8');
	}

	public function val($id) {
		return $this->escape($this->{$id}->value);
	}
}
?>
