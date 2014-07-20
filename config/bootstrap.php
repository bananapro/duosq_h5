<?php

/**
 * 解析execl返回数组
 * @param  string $file         待解析的excel文件
 * @param  array  $valide_title 待校验的excel列标题，如果不严格匹配将返回空
 * @return array                转换成数组返回
 */
function excel2array($file, $valide_title=array()){

	I('excel_reader');
	$excel = new PhpExcelReader;
	$excel->setOutputEncoding('UTF8');
	$excel->read($file);
	$array = $excel->sheets[0];

	if($valide_title){
		$titles = $array['cells'][1];
		foreach($titles as $col => $title){
			if($title != $valide_title[$col-1]){
				return false;
			}
		}
	}

	if(!$valide_title){
		$valide_title = array_values($array['cells'][1]);
	}

	unset($array['cells'][1]);
	if(count($array['cells'])==0)return;

	$ret = array();
	$i = 0;
	foreach ($array['cells'] as $cell) {
		if($valide_title){
			if(count($cell) != count($valide_title)){
				return;
			}
		}
		$j = 0;
		foreach($cell as &$value){
			$ret[$i][$valide_title[$j]] = $value;
			$j++;
		}
		$i++;
	}

	return $ret;
}
?>