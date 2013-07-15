<?php
//图片合并类
class Image_Copy {
    
    //背景图片的宽度
    private $bg_w = 0;
    
    //背景图片的高度
    private $bg_h = 0;
    
    //背景色
    private $bg_color = array(
        'red'=>255,
        'green'=>255,
        'blue'=>255
    );
    
    //每行3个
    private $line_num = 3;
    
    //输出到目录或者浏览器
    private $output;
    
    //图片类型
    private $type = 'png';
    
    //目标图标识
    private $dst_img;
    
    //保存图片数据
    private $img_data = array();
    
    //保存单例在此属性中
    private static $instance;
    
    private function __construct($config) {
        //设置每行显示几个
        if($config['line_num']) $this->line_num = intval($config['line_num']);
        if($config['output']) $this->output = $config['output'];
        if($config['type']) $this->type = $config['type'];
    }
    
    private function __clone() {}
    
    //建立背景图
    private function background() {
        //新建一个真彩色图像，返回一个图像标识符
        $bg_img = imagecreatetruecolor($this->bg_w, $this->bg_h);
        //分配颜色
        $bg_color = imagecolorallocate($bg_img, $this->bg_color['red'], $this->bg_color['green'], $this->bg_color['blue']);
        //画一个矩形并填充
        imagefilledrectangle($bg_img, 0, 0, $this->bg_w, $this->bg_h, $bg_color);
        //设置透明
        imagecolortransparent($bg_img, $bg_color);
        $this->dst_img = $bg_img;
    }
    
    //获取每个图片的大小
    protected function set_img_data($img_list) {
        $img_count = 1;
        $first_h = 0;
        $other_h = 0;
        $other_w = 0;
        //计算其他图片有多少行
        $other_lines = ceil((count($img_list)-1)/$this->line_num);
        //x，y坐标
        $x = $y = 0;
        
        //其他图片每行指针，默认指向第一张图
        $other_line_pointer = 1;
        foreach($img_list as $img) {
            //获取图片信息
            $img_info = getimagesize($img);
            $real_w = $img_info[0];
            $real_h = $img_info[1];
            //凑整数
            $w = $real_w+10-$real_w%10;
            $h = $real_h+10-$real_h%10;
            
            //计算总的宽度和高度
            if($img_count === 1) {
                //第一张图
                $first_h = $h;
            } elseif($img_count === 2) {
                //其他的图都相同
                $other_w = $w; 
                $other_h = $h;
            }
            
            //计算坐标
            if($img_count === 1) {
                $this->img_data[] = array(
                    'w'=>$real_w,
                    'h'=>$real_h,
                    'x'=>$x,
                    'y'=>$y,
                    'img'=>$img
                );
                $y += $h;
            } else {
                if($other_line_pointer === 1) {
                    $x = 0;
                    $this->img_data[] = array(
                        'w'=>$real_w,
                        'h'=>$real_h,
                        'x'=>$x,
                        'y'=>$y,
                        'img'=>$img
                    );
                    $other_line_pointer++;
                } elseif($other_line_pointer == $this->line_num) {
                    $x += $w;
                    $this->img_data[] = array(
                        'w'=>$real_w,
                        'h'=>$real_h,
                        'x'=>$x,
                        'y'=>$y,
                        'img'=>$img
                    );
                    //重置指针执行下一行第一张图
                    $other_line_pointer = 1;
                    $y += $h;
                } else {
                    $x += $w;
                    $this->img_data[] = array(
                        'w'=>$real_w,
                        'h'=>$real_h,
                        'x'=>$x,
                        'y'=>$y,
                        'img'=>$img
                    );
                    $other_line_pointer++;
                }
            }
            $img_count++;
        }
        $this->bg_w = intval($other_w * $this->line_num);
        $this->bg_h = intval($other_lines * $other_h + $first_h);
    }
    
    //单例
    public function get_instance($config=array()) {
        $cls = __CLASS__;
        if(self::$instance instanceof $cls) {
            return new stdClass;
        }
        self::$instance = new $cls($config);
        return self::$instance;
    }
    
    public function generate($img_list=array()) {
        $this->set_img_data($img_list);
        $this->background();
        //合并图片
        foreach($this->img_data as $item) {
            $img = imagecreatefrompng($item['img']);
            imagecopy($this->dst_img, $img, $item['x'], $item['y'], 0, 0, $item['w'], $item['h']);
        }
        if($this->output) {
            switch($this->type) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($this->dst_img, $this->output);
                    break;
                case 'gif':
                    imagegif($this->dst_img, $this->output);
                    break;
                default:
                    imagepng($this->dst_img, $this->output);
            }
            
        } else {
            switch($this->type) {
                case 'jpg':
                case 'jpeg':
                    header('Content-Type:image/jpeg');
                    imagejpeg($this->dst_img);
                    break;
                case 'gif':
                    header('Content-Type:image/gif');
                    imagegif($this->dst_img);
                    break;
                default:
                    header('Content-Type:image/png');
                    imagepng($this->dst_img);
            }   
        }
    }
    
}


/*********************** 使用方法 ***************************/
/*
$config = array(
    //输出文件需要定义，输出到浏览器不需要定义
    //'output'=>'./file.png',
    'type'=>'png',
);

//小图列表
$img_list = array(
    './logo/1.png',
    './logo/2.png',
    './logo/3.png',
    './logo/4.png',
    './logo/5.png',
    './logo/6.png',
    './logo/7.png',
    './logo/8.png',
    './logo/9.png',
    './logo/10.png',
    './logo/11.png',
);

Image_Copy::get_instance($config)->generate($img_list);
*/

