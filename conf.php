<?php

$selfUrl	= $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];  
$rootUrl	= "http://dev.ontheroad.jp/tools/apple/itunessearch.php";
$rootUrl_shortan = "http://bit.ly/1uXLsk4";

$site_title = 'iOS, Mac OS X アプリ検索 & ブログ用タグ生成ツール（PHG 対応版）';
$target_name = array(
	'software'				=> 'iPhone アプリ'
	, 'iPadSoftware'	=> 'iPad アプリ'
	, 'macSoftware'		=> 'Mac アプリ'
);

$pagemeta = array(
	'HOME' => array(
		'title'					=> $site_title
		, 'description' => 'iPhone, iPad, Mac OS X用のアプリをブログで紹介するための HTML を自動生成します。'
	)
	, 'SEARCH' => array(
		'title'					=> $target_name[$entity]."「".$term."」の検索結果"
		, 'description' => $target_name[$entity]."「".$term."」をブログで紹介するための HTML を自動生成します。"
	)
	, 'ERROR' => array(
		'title'					=> 'エラー'
		, 'description' => 'エラー'
	)
);

?>

