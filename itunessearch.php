<?php

require_once( 'functions.php' );

// パラメーターの取得
$term			= get_post( str_replace( ' ', '+', $_POST['kw'] ) );		// 検索キーワード
$entity		= get_post( $_POST['entity'] );													// 対象のアプリ
$country	= get_post( $_POST['country'] );												// 対象の国

$phg_token	= get_post( str_replace( ' ', '', $_POST['phg_token'] ) );	// PHG アフィリエイトトークン
$is_phg_token_save	= get_post( $_POST['is_phg_token_save'] );					
$trackId		= get_post( $_POST['trackId'] );

$dev				= get_post( $_POST['dev'] );
$debug			= get_post( $_POST['debug'] );

$debug = $_GET['debug'];


if( $debug ==1 ) {
	echo '$term = '.$term.'<br>';
	echo '$entity = '.$entity.'<br>';
	echo '$country = '.$country.'<br>';
	echo '$phg_token = '.$phg_token.'<br>';
	echo '$is_phg_token_save = '.$is_phg_token_save.'<br>';
	echo '$debug = '.$debug.'<br>';
}


require_once( 'conf.php' );

if( $term == '' && $entity == '' && $trackId == '') {
	$mode = "HOME";

	// クッキーの処理
	//	if( isset($_COOKIE['phg_token']) ) {
	$phg_token = $_COOKIE['phg_token'];
	//}

} else if( $term <> '' && $entity <> '' && $trackId == '' ) {
	$mode = "SEARCH";

	// クッキーの更新
	if ( $is_phg_token_save == 1 ) {
		setcookie( "phg_token", $phg_token, time()+60*60*24*365 );
		if( $phg_token == '' && $_COOKIE['phg_token'] ) {
			$phg_token = $_COOKIE['phg_token'];
		}
	} else if( $is_phg_token_save == 0 ) {
		setcookie( "phg_token", $phg_token, time()-1800 );
	}

} else {
	$mode = "ERROR";
}


// 初期設定
if ( isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on' ) {
	$protocol = 'https://';  
} else {
	$protocol = 'http://';  
}

// iTunes API REST パラメーター
$searchurl	= 'http://itunes.apple.com/search?';
//$lookupurl	= 'http://itunes.apple.com/lookup?';
$url = $searchurl.'term='.$term.'&country='.$country.'&entity='.$entity;

// REST リクエストの発行
// require_once( 'HTTP/Request.php' );
// $request =new HTTP_Request( $url );
// $result = $request->sendRequest(); 
// 
// //レスポンスの本文を取得
// $json = $request->getResponseBody(); 

$json = file_get_contents($url);

//	$data = json_decode( $json );			//オブジェクトを返す
$data = json_decode( $json , true );	//連想配列を返す

//データ取得
$resultCount	= $data['resultCount'];
$results			= $data['results'];

for( $n=0; $n<count( $results ); $n++ ) {
	$artworkUrl100[$n]		= $results[$n]['artworkUrl100'];			//商品画像
	$collectionName[$n]		= $results[$n]['collectionName'];			//商品名
	$version[$n]					= $results[$n]['version'];						//バージョン
	$description[$n]			= $results[$n]['description'];				//概要
	$fileSizeBytes[$n]		= $results[$n]['fileSizeBytes'];			//ファイルサイズ
	$sellerName[$n]				= $results[$n]['sellerName'];					//販売元
	$artistName[$n]				= $results[$n]['artistName'];					//開発元
	$currency[$n]					= $results[$n]['currency'];						//通貨
	$price[$n]						= $results[$n]['price'];							//価格（アプリ）
	$collectionPrice[$n]	= $results[$n]['collectionPrice'];		//価格（ミュージック：アルバム）
	$trackPrice[$n]				= $results[$n]['trackPrice'];					//価格（ミュージック：1曲）
	$artistViewUrl[$n]		= $results[$n]['artistViewUrl'];			//iTunes へのリンク
	$trackViewUrl[$n]			= $results[$n]['trackViewUrl'];				//Apple ページへのリンク
	$trackId[$n]					= $results[$n]['trackId'];						//Apple ページへのリンク
	$genres[$n]						= $results[$n]['genres'];							//カテゴリ
}

?>

<!-- ------------------ HTMLここから ------------------------ -->
<!DOCTYPE html>
<head>
<meta charset="UTF-8">
<title><?= $pagemeta[$mode]['title'] ?></title>


<!-- ----------------------- CSS ---------------------------- -->
<style type="text/css"><!--
	body {

	}
--></style>
<link rel="stylesheet" media="all" type="text/css" href="./style.css" />

<!-- -------------------- JavaScript ------------------------ -->
<!-- JQuery 本体 & JQuery-UI 本体 & JQuery UI のcss-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js"></script>
<script type="text/javascript"  src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js"></script>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/blitzer/jquery-ui.css" rel="stylesheet" type="text/css"/>

<!-- ------------------------- OGP ------------------------- -->
<meta property="og:title" content="<?= $site_title ?>" />
<meta property="og:type" content="blog" />
<meta property="og:url" content="<?= $rootUrl ?>" />
<meta property="og:image" content="http://dev.ontheroad.jp/tools/apple/img/ogp_logo.png" />
<meta property="og:site_name" content="MacBook Air と WordPress でこうなった" />
<meta property="og:description" content="<?= $pagemeta[$mode]['description'] ?>" />
<meta property="fb:app_id" content="171242856322655" />
<meta property="fb:admins" content="100002003889575" />

<!-- ------------------- Google Analytics ------------------ -->
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-29132526-1']);
_gaq.push(['_trackPageview']);
(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>

<!-- ------------------- HTML（Body） ---------------------- -->
</head><body>

<!-- -------------------- ヘッダ --------------------------- -->
<h1 style="margin:0; padding:0;font-size:18px">
	<a href="<?= $rootUrl ?>">
		<span><img src="img/logo_app.png" style="vertical-align:middle"/></span><?= $site_title ?>
	</a>

	<div style="margin:10px 0;float:right;">

	<!-- いいねボタン -->
	<iframe src="//www.facebook.com/plugins/like.php?href=<?= $rootUrl ?>&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21&amp;appId=171242856322655" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px; height:21px;" allowTransparency="true"></iframe>


	<!-- はてなボタン -->
	<a href="http://b.hatena.ne.jp/entry/<?= $rootUrl ?>" class="hatena-bookmark-button" data-hatena-bookmark-title="<?= $site_title ?>" data-hatena-bookmark-layout="standard" title="このエントリーをはてなブックマークに追加"><img src="http://b.st-hatena.com/images/entry-button/button-only.gif" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a><script type="text/javascript" src="http://b.st-hatena.com/js/bookmark_button.js" charset="utf-8" async="async"></script>


	<!-- ツイッターボタン -->
	<a href="https://twitter.com/share" class="twitter-share-button" data-url="<?= $rootUrl_shortan ?>" data-text="<?= $site_title ?>"></a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>


	</div>
</h1>

<div style="clear:both"></div>

<!-- ----------------- メニューバー ----------------------- -->
<form method="POST" action="itunessearch.php">
	<div style="background:#000; padding:5px 0 5px 2px;">
		<select name="country" class="searchinput">
			<?= getSelectValue( $country, 'jp', '日本' ); ?>
			<?= getSelectValue( $country, 'us', 'アメリカ' ); ?>
		</select>

		<select name="entity" class="searchinput">
			<?= getSelectValue( $entity, 'software', 'iPhone アプリ' ); ?>
			<?= getSelectValue( $entity, 'iPadSoftware', 'iPad アプリ' ); ?>
			<?= getSelectValue( $entity, 'macSoftware', 'Mac アプリ' ); ?>
			<?php //getSelectValue( $entity, 'movie', 'ムービー' ); ?>
			<?php //getSelectValue( $entity, 'musicTrack', 'ミュージック' ); ?>
			<?php //getSelectValue( $entity, 'musicVideo', 'ミュージックビデオ' ); ?>
			<?php //getSelectValue( $entity, 'podcast', 'ポッドキャスト' ); ?>
			<?php //getSelectValue( $entity, 'audiobook', 'オーディオブック' ); ?>
			<?php //getSelectValue( $entity, 'tvShow', 'TV 番組' ); ?>
			<?php //getSelectValue( $entity, 'shortFilm', 'ショートフィルム' ); ?>
			<?php //getSelectValue( $entity, 'ebook', 'eBook' ); ?>
			<?php //getSelectValue( $entity, 'all', 'すべて' ); ?>
		</select>
		<input type="text" name="kw" value="<?= htmlspecialchars( $term )?>" size="40" />
		<input style="margin-left: 30px;" type="submit" value="アプリ検索"/>
	</div>

	<div style="background:#e6e6e6; padding: 10px 10px; margin:0 0 20px 0; font-size:0.7em;">
			PHG アフィリエイトトークン（任意）:

			<?php if( $mode == 'HOME' || ( $phg_token <> '' && $is_phg_token_save == 1 ) ) { ?>
				<input id="phg_token" type="text" size="10" name="phg_token" value="<?= $phg_token ?>">
				<select id="is_phg_token_save" name="is_phg_token_save"><option value="1" selected>アフィリエイトトークンを保存する</option>
				<option value="0">アフィリエイトトークンを保存しない</option></select>
			<?php } else { ?>
				<input id="phg_token" type="text" size="10" name="phg_token" value="">
				<select id="is_phg_token_save" name="is_phg_token_save"><option value="1">アフィリエイトトークンを保存する</option>
				<option value="0" selected>アフィリエイトトークンを保存しない</option></select>
			<?php } ?>

	</div>

</form><br />

<!-- ----------------- コンテンツ ----------------------- -->
<div id="wrapper">
<div id="content">

<?php 

switch( $mode ) { 
case 'HOME': //----------------------------------------------------------
	include('view_appsearch_home.php');
	break;
case 'SEARCH': //----------------------------------------------------------

	include('view_appsearch_searchresult.php');
?>

<?php if( $debug == 1 ) { ?>
	<h3>$url = <a href="<?=$url?>" target="_blank"><?=$url?></a></h3>
	<h3>$ls_url = <a href="<?=$ls_url?>" target="_blank"><?=$ls_url?></a></h3>
	<h3>$ls_data = <a href="<?=$ls_data?>" target="_blank"><?=$ls_data?></a></h3>
	<p>参考：<a href="http://itunes.apple.com/jp/linkmaker" target="_blank">Apple リンクメーカー</a></p>
<?php } ?>

<br />

<?php if( $debug == 1 ) {
echo '<h3>$key & $val</h3>';
echo '<table>';
echo '<tr><th>$key</th><th>$val</th></tr>';
foreach( $data as $key => $val ) {
	echo '<tr><td>'.$key.'</td><td>'.$val.'</tr>';
}
echo '</table><br /><br />';

echo '<h3>$key2 & $val2</h3>';
echo '<table>';
echo '<tr><th>$key</th><th>$val</th></tr>';
foreach( $data as $key => $val ) {
	foreach( $val as $key2 => $val2 ) {
		echo '<tr><td>'.$key2.'</td><td>'.$val2.'</tr>';
	}
}
echo '</table><br /><br />';

echo '<h3>$key3 & $val3</h3>';
echo '<table>';
echo '<tr><th>$key</th><th>$val</th></tr>';
foreach( $data as $key => $val ) {
	foreach( $val as $key2 => $val2 ) {
		foreach( $val2 as $key3 => $val3 ) {
			echo '<tr><td>'.$key3.'</td><td>'.$val3.'</tr>';
		}
	}
}
echo '</table>';

echo '<h3>$key4 & $val4</h3>';
echo '<table>';
echo '<tr><th>$key</th><th>$val</th></tr>';
foreach( $data as $key => $val ) {
	foreach( $val as $key2 => $val2 ) {
		foreach( $val2 as $key3 => $val3 ) {
			foreach( $val3 as $key4 => $val4 ) {
				echo '<tr><td>'.$key3.':'.$key4.'</td><td>'.$val4.'</tr>';
			}
		}
	}
}
echo '</table>';
	}
?>


<?php
	break;
default: //----------------------------------------------------------

}// end of switch

?>


</div><!-- end of #content -->

<div id="sidebar">
<?php require_once( 'sidebar.php' ); ?>
</div><!-- end of #sidebar -->

</div><!-- end of #wrapper -->

<!-- iTunes Auto Link Maker（https://autolinkmaker.itunes.apple.com/jp/?at=10lKgz） -->
<script type='text/javascript'>var _merchantSettings=_merchantSettings || [];_merchantSettings.push(['AT', '10lKgz']);(function(){var autolink=document.createElement('script');autolink.type='text/javascript';autolink.async=true; autolink.src= ('https:' == document.location.protocol) ? 'https://autolinkmaker.itunes.apple.com/js/itunes_autolinkmaker.js' : 'http://autolinkmaker.itunes.apple.com/js/itunes_autolinkmaker.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(autolink, s);})();</script>

<br />

</body></html>


