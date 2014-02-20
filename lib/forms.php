<?php
	require_once(dirname(dirname(__FILE__))."/header.php");
	function get_form_html($id,$fields,$sublabel){
		array_push($fields,Array(
			'type'=>'submit',
			'value'=>$sublabel
		));
		return get_form_html_advanced(Array(
			'id'=>$id
		),$fields);
	}
	function get_form_html_advanced($attributes,$fields){
		$r = "<form";
		foreach($attributes as $attribute => $value){
			$r .= " {$attribute}=\"{$value}\"";
		}
		$r.= ">\n";
		foreach($fields as $k => $field){
			$r .= get_field_html($field);
		}
		return $r."</form>\n";
	}
	function get_field_html($field){
		$a = '';
		if(isset($field['attributes'])){
			foreach($field['attributes'] as $attribute => $value){
				$a .= " {$attribute}=\"{$value}\"";
			}
		}
		$v = '';
		if(isset($field['value'])&&!is_null($field['value'])&&$field['value']!=''){
			$v = "value='{$field['value']}'";
		}
		switch($field['type']){
			case 'select':
				$r = "<div class='row'><label for='{$field['name']}'>{$field['label']}</label><span><select name='{$field['name']}'{$a}>";
				foreach($field['values'] as $k => $opt){
					$a = '';
					if(isset($opt['attributes']) && is_array($opt['attributes'])){
						foreach($opt['attributes'] as $attribute => $value){
							$a .= " {$attribute}=\"{$value}\"";
						}
					}
					if(isset($field['value'])&&$field['value']==$opt['value']){
						$a .= "selected=\"selected\"";
					}
					$r .= "<option value='{$opt['value']}'{$a}>{$opt['label']}</option>";
				}
				$r .= "</select></span></div>";
			break;
			case 'hidden':
				$r = "<input type='hidden' name='{$field['name']}'{$v}{$a}/>";
			break;
			case 'custom':
				$r = $field['html'];
			break;
			case 'section':
				$r = "<div class='form_section'{$a}>";
				if(isset($field['fields'])){
					foreach($field['fields'] as $k => $subfield){
						$r .= get_field_html($subfield);
					}
				}
				$r .= "</div>";
			break;
			case 'submit':
				$r = "<input type='submit' {$v}{$a}/>";
			break;
			case 'text':default:
				$r = "<div class='row'><label for='{$field['name']}'>{$field['label']}</label><span><input type='{$field['type']}' name='{$field['name']}'{$v}{$a}/></span></div>";
		}
		return $r."\n";
	}
?>