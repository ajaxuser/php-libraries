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
    
    //生成规则
    private $rule = 1;
    
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
        if($config['rule']) $this->rule = $config['rule'];
        if($config['line_num']) $this->line_num = intval($config['line_num']);
        if($config['type']) $this->type = $config['type'];
        if($config['output']) $this->output = $config['output'];
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
    
    //禁止对象clone
    private function __clone() {}
    
    //规则1：所有图都相同
    protected function set_img_data_rule1($img_list) {
        //计算图片有多少行
        $lines = ceil(count($img_list)/$this->line_num);
        //每张图片的width和height
        $w = $h = 0;
        //x，y坐标
        $x = $y = 0;
        //每行的图片指针
        $line_pointer = 1;
        foreach($img_list as $img) {
            //获取图片信息
            $img_info = getimagesize($img);
            //计算宽度和高度
            $real_w = $img_info[0];
            $real_h = $img_info[1];
            //凑整数
            $w = $real_w+10-$real_w%10;
            $h = $real_h+10-$real_h%10;
            
            //计算坐标
            $this->img_data[] = array(
                'w'=>$real_w,
                'h'=>$real_h,
                'x'=>$x,
                'y'=>$y,
                'img'=>$img
            );
            if($line_pointer == $this->line_num) {
                //重置指针执行下一行第一张图
                $line_pointer = 1;
                //重置x坐标
                $x = 0;
                //换行y坐标增加
                $y += $h;
            } else {
                //x坐标增加
                $x += $w;
                $line_pointer++;
            }
        }
        $this->bg_w = intval($w * $this->line_num);
        $this->bg_h = intval($h * $lines);
    }
    
    //规则2：第一张为大图，其他图相同
    protected function set_img_data_rule2($img_list) {
        $img_count = 1;
        $first_w = 0;
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
                $first_w = $w;
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
                $this->img_data[] = array(
                    'w'=>$real_w,
                    'h'=>$real_h,
                    'x'=>$x,
                    'y'=>$y,
                    'img'=>$img
                );
                if($other_line_pointer == $this->line_num) {
                    //重置指针执行下一行第一张图
                    $other_line_pointer = 1;
                    //重置x坐标
                    $x = 0;
                    $y += $h;
                } else {
                    $x += $w;
                    $other_line_pointer++;
                }
            }
            $img_count++;
        }
        $this->bg_w = intval($other_w * $this->line_num);
        if($first_w > $this->bg_w) {
            $this->bg_w = $first_w;
        }
        $this->bg_h = intval($other_lines * $other_h + $first_h);
    }
    
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
    
    public function generate($img_list=array()) {
        if($this->rule == 1) {
            $this->set_img_data_rule1($img_list);        
        } elseif($this->rule == 2) {
            $this->set_img_data_rule2($img_list);        
        }
        $this->background();
        //合并图片
        $func = 'imagecreatefrom'.$this->type;
        if(!function_exists($func)) {
            die('function '.$func.' not exists.');
        }
        foreach($this->img_data as $item) {
            $img = $func($item['img']);
            imagecopy($this->dst_img, $img, $item['x'], $item['y'], 0, 0, $item['w'], $item['h']);
        }
        //输出
        $func = 'image'.$this->type;
        if(!function_exists($func)) {
            die('function '.$func.' not exists.');
        }
        if($this->output) {
            $func($this->dst_img, $this->output);
        } else {
            header('Content-Type:image/'.$this->type);
            $func($this->dst_img);
        }
    }
    
}

/*********************** 使用方法 ***************************/

/*
$config = array(
    //生成规则：1为所有图都相同  2为第一张图独占一行，其他图根据line_num共享一行
    'rule'=>1,
    //每行的个数
    'line_num'=>5,
    //type选项: png gif jpeg
    'type'=>'png',
    //输出文件需要定义，输出到浏览器不需要定义
    //'output'=>'./file.png',
);

//小图列表
$img_list = array(
    '../logo/1.png',
    '../logo/2.png',
    '../logo/3.png',
    '../logo/4.png',
    '../logo/5.png',
    '../logo/6.png',
    '../logo/7.png',
    '../logo/8.png',
    '../logo/9.png',
    '../logo/10.png',
    '../logo/11.png',
);

Image_Copy::get_instance($config)->generate($img_list);
*/