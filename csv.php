<?php 
//目前支持从UTF8转换到UCS-2LE
class CSV {

    private static $fp;

	public static function init($filename='') {
		if (!$filename) $filename = date('Ymd');
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if(preg_match("/MSIE/", $user_agent)) {
			$filename = urlencode($filename);
		}
		//设置 csv header 
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename.".csv");
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');

		//打开标准输出
		self::$fp = fopen('php://output', 'w');
		//add BOM header(unicode)
		fwrite(self::$fp,"\xFF\xFE", 2);
	}

	public static function export_header($header=array()) {
		//add header line
		fwrite(self::$fp, self::encoding_convert(implode("\t",$header)));
	}

	public static function export_data($data=array()) {
		fwrite(self::$fp, self::encoding_convert("\r\n"));
		fwrite(self::$fp, self::encoding_convert(implode("\t",$data)));
	}

	public static function del() {
		fclose(self::$fp);
	}

	private function encoding_convert($str=''){
		return iconv('UTF-8','UCS-2LE',$str);
	}
}
