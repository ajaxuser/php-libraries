<?php 
/** 
 * @author ajaxuser
 * @email 666zhen@163.com
 * @desc 分页类
 * @version 1.0
 * @usage
 *  $config = array(
 *		'base_url'=>$base_url,
 *		'total_count'=>$total_count,
 *		'offset'=>$offset,
 *		'per_page'=>$per_page
 *	);
 *	$pagination = Pagination::init($config)->create_links();
 */

class Pagination {
	
	private static $first_page_label = '首页';
	private static $last_page_label = '末页';
	private static $prev_page_label = '上一页';
	private static $next_page_label = '下一页';
	//最大显示页码个数
	private static $max_show_page = 8;
	//第一页
	private static $first_page;
	//最后一页
	private static $last_page;
	//上一页
	private static $prev_page;
	//下一页
	private static $next_page;
	//记录总个数
	private static $total_count;
	//url地址	
	private static $base_url;
	//偏移量
	private static $offset;
	//每页显示记录个数
	private static $per_page;
	//当前页
	private static $current_page;
	
    private function __construct() {
    
    }
    
    public static function init($config=array()) {
        self::$total_count = intval($config['total_count']);
		self::$base_url = $config['base_url'];
		self::$per_page = intval($config['per_page']);
		self::$offset = intval($config['offset']);
		
		//根据offset计算当前页
		if (self::$offset) { 
			self::$current_page = (self::$offset/self::$per_page) + 1;			
		}else {
			self::$current_page = 1;
		}
		
		//第一页
		self::$first_page = 1;
		//最后一页
		self::$last_page = ceil(self::$total_count/self::$per_page);
		//上一页
		self::$prev_page = self::$current_page - 1;
		//下一页
		self::$next_page = self::$current_page + 1;
        
        $cls = __CLASS__;
        
        return new $cls;
    }
    
    //显示页面
    public function create_links() {
        $pagination = '';
        
        if (self::$total_count > self::$per_page) {
            $page_prefix = '<div class="page_container">';
            
            //首页和上一页
            if (self::$current_page == 1) {
                $page_str = '<span class="page">'.self::$first_page_label.'</span>';
                $page_str .= '<span class="page">'.self::$prev_page_label.'</span>';
            } else {
                //偏移量
                $prev_start = (self::$current_page-2) * self::$per_page;
                $page_str = '<a class="page" href="'.self::$base_url.'">'.self::$first_page_label.'</a>';
                $page_str .= '<a class="page" href="'.self::$base_url.'offset='.$prev_start.'">'.self::$prev_page_label.'</a>';
            }
            
            //页码设置
            for($page=1; $page<=self::$last_page; $page++) {
                //偏移量
                $start = ($page-1) * self::$per_page;
                
                if ($page == self::$current_page) {
                    //当前页面
                    $page_str .= '<span class="page current_page">'.$page.'</span>';
                } else {
                    //页码在max_show_page之内的
                    if (self::$current_page < self::$max_show_page && $page <= self::$max_show_page) {
                        $page_str .= '<a class="page" href="'.self::$base_url.'offset='.$start.'">'.$page.'</a>';
                    }
                    //中间页码
                    elseif (self::$current_page >= self::$max_show_page && 
                            ($page - self::$current_page) >= -2 &&   //page start
                            ($page - self::$current_page) < self::$max_show_page-2   //page end
                           ) 
                    {
                        $page_str .= '<a class="page" href="'.self::$base_url.'offset='.$start.'">'.$page.'</a>';
                    }
                    //最后页码不足max_show_page时
                    elseif (self::$current_page >= self::$max_show_page &&
                            (self::$last_page - self::$current_page) < self::$max_show_page-2 && 
                            (self::$last_page - $page) < self::$max_show_page
                           ) 
                    {
                        $page_str .= '<a class="page" href="'.self::$base_url.'offset='.$start.'">'.$page.'</a>';
                    }
                }							
            }
            
            //下一页和末页
            if (self::$current_page == self::$last_page) {
                $page_str .= '<span class="page">'.self::$next_page_label.'</span>';
                $page_str .= '<span class="page">'.self::$last_page_label.'</span>';
            }else {
                //偏移量
                $next_start = self::$current_page * self::$per_page;
                $last_start = (self::$last_page-1) * self::$per_page;
                $page_str .= '<a class="page" href="'.self::$base_url.'offset='.$next_start.'">'.self::$next_page_label.'</a>';
                $page_str .= '<a class="page" href="'.self::$base_url.'offset='.$last_start.'">'.self::$last_page_label.'</a>';
            }

            $page_suffix = '</div>';
            $pagination = $page_prefix.$page_str.$page_suffix;
        }
        return $pagination;
    }
}

