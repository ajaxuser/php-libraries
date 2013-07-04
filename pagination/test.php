<style type="text/css">
	.page_container .page {
		color:#333;
		padding:5px 10px;
		margin:0 3px;
		border:1px solid #ddd;
		color:#666;
        text-decoration:none;
	}

	.page_container a.page:hover {
		background:#666;
		color:#fff;
		text-decoration:none;
		cursor:pointer;
	}

	.page_container span.current_page {
		background:#666;
		color:#fff;
		font-weight:bold;
	}
</style>
<?php
include 'pagination.php';

$offset = $_GET['offset'];
$config = array(
    'base_url'=>'http://test.localhost/test.php?',
    'total_count'=>1000,
    'offset'=>$offset,
    'per_page'=>10
);
$pagination = Pagination::init($config)->create_links();

echo $pagination;

