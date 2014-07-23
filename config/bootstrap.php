<?php

//拼接ocp图片裁剪接口
function imageUrl($path, $size='', $cut = 1, $ext = 'jpg') {
	if (!$path)
		return;
	$num = ord(md5($path))%5;
	//return MY_HOMEPAGE_URL . '/ocp?u=' . $path . '&s=' . $size . '&q=85&c=' . $cut . '&t=' . $ext;
	return 'http://c'.$num.'.duosq.com/ocp/?u=' . $path . '&s=' . $size . '&q=85&c=' . $cut . '&t=' . $ext;
}
?>