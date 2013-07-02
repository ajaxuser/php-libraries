<?php
/**
 * 验证码(基于PHP GD库)，可通过传参数的方式定制验证码。
 * 支持干扰线，干扰点，每个字符都支持旋转
 * 
 * @author ajaxuser
 * @email  666zhen@163.com
 * @version 1.0 
 *
 */
class ImageCode {
    
    //字符字典
    private $letter_dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    //图片宽
    private $width = 150;
    
    //图片高
    private $height = 50;
    
    //字体大小
    private $font_size = 20;
    
    //文本个数
    private $num = 6;
    
    //干扰线个数
    private $line_num = 20;
    
    //干扰点个数
    private $point_num = 500;
    
    //字体文件路径
    private $font_style = 'arial.ttf';
    
    //背景色
    private $back_true_color = array(
        'red'=>255,
        'green'=>255,
        'blue'=>255
    );
    
    //字符Y坐标，设定了字体基线的位置
    private $letter_y = 30;
    
    //验证码session名称
    private $sess_code = 'sess_code';
    
    /**
     * params包含的参数如下
     * @param width
     * @param height
     * @param font_size
     * @param num
     * @param line_num
     * @param point_num
     * @param font_style
     * @param back_true_color
     * @param letter_y
     * @param sess_code
     */
    public function __construct($params=array()) {
        if($params['width']) $this->width = intval($params['width']);
        if($params['height']) $this->height = intval($params['height']);
        if($params['font_size']) $this->font_size = intval($params['font_size']);
        if($params['num']) $this->num = intval($params['num']);
        if($params['line_num']) $this->line_num = intval($params['line_num']);
        if($params['point_num']) $this->point_num = intval($params['point_num']);
        if($params['font_style']) $this->font_style = $params['font_style'];
        if($params['back_true_color'] && is_array($params['back_true_color'])) {
            $this->back_true_color = $params['back_true_color'];
        }
        if($params['letter_y']) $this->letter_y = intval($params['letter_y']);
        
        //打开session
        $sess_id = session_id();
        if(empty($sess_id)) session_start();
    }
    
    //生成验证码
    public function show() {
        //返回一个图像标识符,代表一个黑色图像
        $im = imagecreatetruecolor($this->width, $this->height);
        //画背景图
        $back_color = imagecolorallocate($im, $this->back_true_color['red'], $this->back_true_color['green'], $this->back_true_color['blue']);
        imagefilledrectangle($im, 0, 0, $this->width, $this->height, $back_color);
        
        //加入干扰线
        for($i=0; $i<$this->line_num; $i++) {
            $arc_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagearc($im, mt_rand(- $this->width, $this->width), mt_rand(- $this->height, $this->height), mt_rand(30, $this->width * 2), mt_rand(20, $this->height * 2), mt_rand(0, 360), mt_rand(0, 360), $arc_color);
        }
        //加入干扰点
        for($i=0; $i<$this->point_num; $i++) {
            $pixel_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            //画一个单一像素
            imagesetpixel($im, mt_rand(0, $this->width), mt_rand(0, $this->height), $pixel_color);
        }
        
        //生成验证码
        header('Content-Type:image/png');
        //生成字符
        $text = $this->text();
        $_SESSION[$this->sess_code] = $text; 
        //字体颜色
        $text_color = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        //将字符逐个写到图片上
        for($i=0; $i<mb_strlen($text, 'utf-8'); $i++) {
            $letter = mb_substr($text, $i, 1, 'utf-8');
            $arr = array(-1,0,1);
            $angle = $arr[array_rand($arr)] * mt_rand(10, 25);
            $letter_x = 10+$i*$this->font_size;
            imagettftext($im, $this->font_size, $angle, $letter_x, $this->letter_y, $text_color, $this->font_style, $letter);
        }
        imagepng($im);
        imagedestroy($im);
    }
    
    //生成验证码文本
    private function text() {
        $min = 0;
        $max = mb_strlen($this->letter_dict, 'utf-8')-1;
        $text = '';
        for($i=0; $i<$this->num; $i++) {
            $text .= $this->letter_dict[mt_rand($min, $max)];
        }
        return $text;
    }
}

/** 使用方法
参数为空则使用默认参数
$params = array();
$code = new ImageCode($params);
$code->show();
*/
