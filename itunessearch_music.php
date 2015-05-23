<?php
	// 初期設定 ----------------------------------------------------------------------------
	if ( isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on' ) {
	    $protocol = 'https://';  
	} else {
	    $protocol = 'http://';  
	}
	$selfUrl	= $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];  
	$rootUrl	= "http://dev.ontheroad.jp/tools/apple/itunessearch_music.php";

	// パラメーターの取得 -----------------------------------------------------------------
	$kw		 		= $_GET['kw'];
	$term 			= str_replace( ' ', '+', $_GET['kw'] );
	$entity			= $_GET['entity'];

	$targetId		= $_GET['targetId'];
	$targetArtist	= $_GET['targetArtist'];

	$lstrackingUrl	= str_replace( ' ', '', $_GET['lstrackingUrl'] );		// LinkShareトラッキング URL

	$dev			= $_GET['dev'];
	$debug			= $_GET['debug'];

	if( $entity == 'album' ) {
		$target_name = 'アルバム';
	} else if( $entity == 'album_w_song' ) {
		$target_name = 'アルバム（楽曲リスト付）';
	} else if( $entity == 'song' ) {
		$target_name = '楽曲';
	}

	if( $term == '' && $entity == '' && $trackId == '') {
		$mode = "HOME";
		$title		= "iTunes ミュージック検索 & ブログ用タグ生成ツール（PHG 対応版）";
		$description = "iTunes で販売されているアルバム/楽曲をブログで紹介するための HTML を自動生成します。";
	} else if( $term <> '' && $entity <> '' && $trackId == '' ) {
		$mode = "SEARCH";
		$title		= $target_name."「".$term."」の検索結果 | iTunes ミュージック検索 & ブログ用タグ生成ツール";
		$description = $target_name."「".$term."」をブログで紹介するための HTML を自動生成します。";
	} else {
		$mode = "ERROR";
		$title		= "エラー：iTunes ミュージック検索 & ブログ用タグ生成ツール";
	}

	// Cookie の処理 ----------------------------------------------------------------------
	if ( $lstrackingUrl <> '' ) {
		setcookie( "lstrackingUrl", $lstrackingUrl, time()+60*60*24*365 );

	} else if( $lstrackingUrl == '' && $_COOKIE['lstrackingUrl'] ) {
		$lstrackingUrl = $_COOKIE['lstrackingUrl'];
	}


	// 関数（引数 $results を曲目リストに作り替えて返す） -------------------------------
	function getCollectionList( $results, $targetCollectionName, $targetArtistName ) {

		if( $debug == 1 ) {
			echo '$results = '.$results.'<br>';
			echo '$targetCollectionName = '.$targetCollectionName.'<br>';
			echo '$targetArtistName = '.$targetArtistName.'<br>';
			echo '<hr size="1">';
		}

		for( $n=0; $n<count( $results ); $n++ ) {
			if( $results[$n]['collectionName'] == $targetCollectionName && strpos( $results[$n]['artistName'], $targetArtistName ) !== false ) {
//			if( $results[$n]['collectionName'] == $targetCollectionName && $results[$n]['artistName'] == $targetArtistName ) {
//				$results[$n]['trackNumber'] = sprintf( '%02d', $results[$n]['trackNumber'] ); // 2桁でゼロ埋め
				$list[] = $results[$n];
			}
		}
		
		// 多次元配列のソート
		foreach($list as $key=>$row) {
			$discNumber[$key] = $row[ 'discNumber' ];
			$trackNumber[$key] = $row[ 'trackNumber' ];
		}
		array_multisort($list, SORT_NUMERIC, SORT_ASC, $discNumber, SORT_NUMERIC, SORT_ASC, $trackNumber );

		return $list;
	}

	// iTunes API REST パラメーター -------------------------------------------------------------
	$baseurl	= 'http://itunes.apple.com/search?';
	$lookupurl	= 'http://itunes.apple.com/lookup?';
	$country	= 'jp';
	$lang		= 'jp_JP';

	if( $entity == 'album_w_song' ) {	
		$url	= $baseurl.'term='.$term.'&country='.$country.'&entity=song&limit=200';
	} else {
		$url	= $baseurl.'term='.$term.'&country='.$country.'&entity='.$entity;
	}

	//データ取得
	$json = file_get_contents($url);
	$data = json_decode( $json , true );	//連想配列を返す

	$resultCount	= $data['resultCount'];
	$results		= $data['results'];

	if( $entity == 'album_w_song' ) {
		$results = getCollectionList($results, $kw, $targetArtist);

		if( $debug == 1 ) {
			echo '$results[$targetId][\'trackCount\'] = '.$results[$targetId]['trackCount'].'<br>';
			echo 'count( $results ) = '.count( $results ).'<br>';
		}

		if( $results[$targetId]['trackCount'] <> count( $results ) ) {

			$term	= str_replace( ' ', '', $targetArtist );
			$url		= $baseurl.'term='.$term.'&country='.$country.'&entity=song&limit=200';

			if( $debug == 1 ) {
				echo '$url = '.$url.'<br>';
			}

			$json		= file_get_contents($url);
			$data = json_decode($json);
			$results	= $data['results'];
			$results	= getCollectionList($data['results'], $kw, $targetArtist);
		}
	}

	for( $n=0; $n<count( $results ); $n++ ) {

		// 音楽関連（アルバム）
		$artworkUrl100[$n]		= $results[$n]['artworkUrl100'];		//商品画像		
		$sellerName[$n]			= $results[$n]['sellerName'];			//販売元
		$collectionName[$n]		= $results[$n]['collectionName'];		//アルバム名
		$artistName[$n]			= $results[$n]['artistName'];			//アーティスト名
		$artistViewUrl[$n]		= $results[$n]['artistViewUrl'];		//iTunes へのリンク
		$collectionViewUrl[$n]	= $results[$n]['collectionViewUrl'];	//アルバムページへのリンク
		$collectionPrice[$n]	= $results[$n]['collectionPrice'];		//価格（アルバム）
		$primaryGenreName[$n]	= $results[$n]['primaryGenreName'];		//カテゴリ
		$releaseDate[$n]		= $results[$n]['releaseDate'];			//リリース日
		$trackCount[$n]			= $results[$n]['trackCount'];			//収録楽曲数
		$copyright[$n]			= $results[$n]['copyright'];			//発売元

		// 音楽関連（楽曲）
		$artworkUrl100[$n]		= $results[$n]['artworkUrl100'];		//商品画像
		$sellerName[$n]			= $results[$n]['sellerName'];			//販売元
		$trackName[$n]			= $results[$n]['trackName'];			//楽曲名
		$artistName[$n]			= $results[$n]['artistName'];			//アーティスト名
		$collectionName[$n]		= $results[$n]['collectionName'];		//収録アルバム名
		$primaryGenreName[$n]	= $results[$n]['primaryGenreName'];		//カテゴリ
		$artistViewUrl[$n]		= $results[$n]['artistViewUrl'];		//iTunes へのリンク
		$collectionViewUrl[$n]	= $results[$n]['collectionViewUrl'];	//アルバムページへのリンク
		$trackViewUrl[$n]		= $results[$n]['trackViewUrl'];			//楽曲ページへのリンク名
		$previewUrl[$n]			= $results[$n]['previewUrl'];			//視聴リンク
		$trackPrice[$n]			= $results[$n]['trackPrice'];			//価格（楽曲）
		$collectionPrice[$n]	= $results[$n]['collectionPrice'];		//価格（アルバム）
		$releaseDate[$n]		= $results[$n]['releaseDate'];			//リリース日
		$trackTimeMillis[$n]	= $results[$n]['trackTimeMillis'];		//収録時間
		$trackNumber[$n]		= $results[$n]['trackNumber'];			//曲番
		$discCount[$n]			= $results[$n]['discCount'];			//ディスク枚数
		$discNumber[$n]			= $results[$n]['discNumber'];			//ディスク番号
		$trackTimeMillis[$n]	= $results[$n]['trackTimeMillis'];		//ディスク番号

		$artworkUrl200[$n]		= str_replace( '100x100', '200x200', $artworkUrl100[$n] ); 		//商品画像
		$trackTime_m[$n]		= floor(($trackTimeMillis[$n])/1000/60);						//時間（分）
		$trackTime_s[$n]		= sprintf( '%02d', floor(($trackTimeMillis[$n])/1000%60) );		//時間（秒）
	}
	
?>

<?php
	function getSelectValue( $cullent, $val, $label ) {
		if( $cullent == $val ) {
			return '<option value="'.$val.'" selected>'.$label.'</option>';
		} else {
			return '<option value="'.$val.'">'.$label.'</option>';
		}
	}
?>

<!-- ------------------ HTMLここから ------------------------ -->
<!DOCTYPE html>
<head>
<meta charset="UTF-8">
<title><?= $title ?></title>
<meta name="description" content="<?= $description ?>">

<!-- ----------------------- CSS ---------------------------- -->
<link rel="stylesheet" type="text/css" href="./style.css" />

<style type="text/css"><!--
	.accordion {
		width: 660px;
		border-bottom: solid 1px #c4c4c4;
	}
	.accordion h3 {
		background: #e9e7e7 url(./img/arrow-square.gif) no-repeat right -51px;
		padding: 7px 15px;
		margin: 0;
		font: bold 120%/100% Arial, Helvetica, sans-serif;
		font-size:14px;
		border: solid 1px #c4c4c4;
		border-bottom: none;
		cursor: pointer;
	}
	.accordion h3:hover {
		background-color: #e3e2e2;
	}
	.accordion h3.active {
		background-position: right 5px;
	}
	.accordion p {
		background: #f7f7f7;
		margin: 0;
		padding: 10px 15px 20px;
		border-left: solid 1px #c4c4c4;
		border-right: solid 1px #c4c4c4;
		display: none;
	}
--></style>

<!-- -------------------- JavaScript ------------------------ -->

<!-- jquery　本体-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js"></script>

<!-- jquery-UI　本体-->
<script type="text/javascript"  src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js"></script>

<!-- jquery UI のcss-->
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/blitzer/jquery-ui.css" rel="stylesheet" type="text/css"/>

<script type="text/javascript">
$(function(){
	
	$(".accordion h3").eq(1).addClass("active");
	$(".accordion table").eq(1).show();

	$(".accordion h3").click(function(){
		$(this).next("table").slideToggle("slow").siblings("table:visible").slideUp("slow");
		$(this).toggleClass("active");
		$(this).siblings("h3").removeClass("active");
	});

});
</script>
<!-- ------------------------- OGP ------------------------- -->
<meta property="og:title" content="<?= $title ?>" />
<meta property="og:type" content="blog" />
<meta property="og:url" content="<?= $rootUrl ?>" />
<meta property="og:image" content="http://dev.ontheroad.jp/tools/apple/img/ogp_logo_music.png" />
<meta property="og:site_name" content="MacBook Air と Wordpress でこうなった" />
<meta property="og:description" content="<?= $description ?>" />
<meta property="fb:app_id" content="171242856322655" />
<meta property="fb:admins" content="100002003889575" />

<!-- ------------------- Google Analitics ------------------ -->
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
	<a href="<?= $rootUrl ?>"><span><img src="img/logo_music.png" style="vertical-align:middle"/></span><?= $title ?></a>

	<div style="margin:10px 0;float:right;">
	
	<!-- ツイッターボタン -->
	<a href="https://twitter.com/share" class="twitter-share-button" data-via="ontheroad_jp" data-hashtags="iTunes">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>

	<!-- はてなボタン -->
	<a href="http://b.hatena.ne.jp/entry/<?= $rootUrl ?>" class="hatena-bookmark-button" data-hatena-bookmark-title="<?= $title ?>" data-hatena-bookmark-layout="standard" title="このエントリーをはてなブックマークに追加"><img src="http://b.st-hatena.com/images/entry-button/button-only.gif" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a><script type="text/javascript" src="http://b.st-hatena.com/js/bookmark_button.js" charset="utf-8" async="async"></script>

	<!-- いいねボタン -->
	<iframe src="//www.facebook.com/plugins/like.php?href=<?= $rootUrl ?>&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21&amp;appId=171242856322655" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px; height:21px;" allowTransparency="true"></iframe>

</h1>

<div style="clear:both"></div>


<!-- ----------------- メニューバー ----------------------- -->
<form method="get" action="itunessearch_music.php">
	<div style="background:#000">
		<select name="entity" class="searchinput">
			<?php //getSelectValue( $entity, 'software', 'iPhone アプリ' ); ?>
			<?php //getSelectValue( $entity, 'iPadSoftware', 'iPad アプリ' ); ?>
			<?php //getSelectValue( $entity, 'macSoftware', 'Mac アプリ' ); ?>
			<?php //getSelectValue( $entity, 'movie', 'ムービー' ); ?>
			<?php //echo getSelectValue( $entity, 'albumTerm', 'アルバム名' ); ?>
			<?= getSelectValue( $entity, 'album', 'アルバム検索' ); ?>
			<?= getSelectValue( $entity, 'song', '楽曲検索' ); ?>
			<?php //getSelectValue( $entity, 'musicArtist', 'アーティスト検索' ); ?>
			<?php //getSelectValue( $entity, 'podcast', 'ポッドキャスト' ); ?>
			<?php //getSelectValue( $entity, 'audiobook', 'オーディオブック' ); ?>
			<?php //getSelectValue( $entity, 'tvShow', 'TV 番組' ); ?>
			<?php //getSelectValue( $entity, 'shortFilm', 'ショートフィルム' ); ?>
			<?php //getSelectValue( $entity, 'ebook', 'eBook' ); ?>
			<?php //getSelectValue( $entity, 'all', 'すべて' ); ?>
		</select>
		<input type="text" name="kw" value="<?=$term?>" />
		<input type="submit" value="ミュージック検索"/>
	</div><br />

<!--
	<div style="background:#e6e6e6">
			<div style="font-size:0.7em;">
				<span style="margin:0 10px">LinkShare トラッキング URL:</span>
				<input type="text" size="130" name="lstrackingUrl" value="<?=$lstrackingUrl ?>">
			</div>
			<div style="clear:both"></div>
	</div>
-->

</form>
<br />

<!-- ----------------- コンテンツ ----------------------- -->
<div id="wrapper">
<div id="content">


<?php if( $term == '' && $entity == '' ) { ?>
	<h2>使い方</h2>
	<ul style="line-height:1.5em">
		<li>Apple の <a href="http://www.apple.com/jp/itunes/whats-on/" target="_blank">iTunes ストア</a>から 音楽アルバムまたは楽曲を検索できます。</li>
		<li>アルバム・楽曲検索をするとブログなどで簡単に紹介するための HTML が自動で生成されます。</li>
		<li>生成された HTML をブログなどの掲載したい場所にコピー&ペーストしてください。</li>
		<li>アルバム・楽曲検索ともに、アーティスト名、アルバム名、楽曲名などで検索できます。<br>
			<br>アルバム検索はこんな感じ。<br>
<div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px"><img src="http://a712.phobos.apple.com/us/r2000/016/Music/41/e2/8b/mzi.xhhfghxa.100x100-75.jpg"  style="float:left; width:80px; margin:0 30px 10px 0;" /><div style="float:left; width:77%;"><div style="font-weight:bold;"><a href="https://itunes.apple.com/jp/album/garuzutoku/id403228318?uo=4" target="_blank">ガールズトーク</a></div><div style="line-height:1.6em;font-size:0.85em;">アーティストKARA<br>カテゴリ:Pop(全10曲)<br>リリース:2010-11-24<br>レーベル:℗ 2010 UNIVERSAL SIGMA, a division of UNIVERSAL MUSIC LLC<br>価格2,000円<br></div><a href="https://itunes.apple.com/jp/album/garuzutoku/id403228318?uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="margin: 10px 0 0 0;" /></a><br></div><div style="clear:both"></div></div><br />

			<br>楽曲検索はこんな感じ。（視聴もできます）<br>
		<script type="text/javascript" src="http://mediaplayer.yahoo.com/js"></script><div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px"><img src="http://a210.phobos.apple.com/us/r2000/003/Music/66/46/0e/mzi.qqkceqti.100x100-75.jpg"  style="float:left; width:80px; margin:0 30px 10px 0;" /><div style="float:left; width:77%;"><div style="font-weight:bold;"><a href="https://itunes.apple.com/jp/album/go-go-sama!/id444097378?i=444097522&uo=4" target="_blank">GO GO サマー!</a></div><div style="line-height:1.6em;font-size:0.85em;">アーティスト:KARA<br>リリース:2011-06-22(Pop)<br>価格:250円 - (<a href="http://a1573.phobos.apple.com/us/r2000/011/Music/v4/70/de/3d/70de3d9f-c27c-577c-2bd5-86240310705c/mzaf_5258366334882249459.aac.m4a" title="DISC 1 ( 1曲目 ) - GO GO サマー!"><img src="http://a210.phobos.apple.com/us/r2000/003/Music/66/46/0e/mzi.qqkceqti.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)<br>(アルバム：<a href="https://itunes.apple.com/jp/album/go-go-sama!/id444097378?i=444097522&uo=4" target="_blank">GO GO サマー! - Single</a>の1曲目に収録されています)<br></div><a href="https://itunes.apple.com/jp/album/go-go-sama!/id444097378?i=444097522&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="margin: 10px 0 0 0;" /></a><br></div><div style="clear:both"></div></div>


		</li>

		<li>収録楽曲リスト付きのアルバム検索もできます。（まれにリストを取得できない場合があります。）</li>
		<li>アルバム検索をした後、「収録楽曲リストをつける」ボタンをクリックしてください。</li>
		<li>そうすると収録楽曲リスト付の HTML が自動で生成されます。</li>
		<li>生成された HTML をブログなどの掲載したい場所にコピー&ペーストしてください。<br>
			<br>
			こんな感じ。（視聴もできます）<br>


<script type="text/javascript" src="http://mediaplayer.yahoo.com/js"></script><div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.100x100-75.jpg"  style="float:left; width:80px; margin:0 30px 10px 0;" /><div style="float:left; width:77%;"><div style="font-weight:bold;"><a href="https://itunes.apple.com/jp/album/mr./id415428105?i=415428117&uo=4" target="_blank">KARA BEST 2007-2010</a></div><div style="line-height:1.6em;font-size:0.8em">アーティスト:KARA<br>カテゴリ:Pop ( 全12曲 )<br>リリース:2011-01-26<br>価格1,500円<br><a href="" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="margin: 10px 0 0 0;" /></a></div></div><div class="clear"></div><hr size="1"><table style="font-size:0.8em; width:100%;"><tr style="background:#E6E6E6"><th colspan="5">DISC 1</th></tr><tr><th width="10%">曲順</th><th width="">曲名</th><th width="12%">収録時間</th><th width="40%" colspan="2">購入（iTunes Store）</th></tr><tr><td>1曲目</td><td>Mr.</td><td>3分12秒</td><td>¥200 - (<a href="http://a1.phobos.apple.com/us/r2000/013/Music/v4/6a/4b/af/6a4bafa1-224f-2ab7-7496-910ded0fcf3a/mzaf_2879680749399289053.aac.m4a" title="DISC 1 ( 1曲目 ) - Mr."><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/mr./id415428105?i=415428117&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>2曲目</td><td>LUPIN</td><td>3分11秒</td><td>¥200 - (<a href="http://a814.phobos.apple.com/us/r2000/014/Music/v4/f3/82/62/f382628a-c1db-dfcf-0089-123645f095b9/mzaf_293507642106952820.aac.m4a" title="DISC 1 ( 2曲目 ) - LUPIN"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/lupin/id415428105?i=415428118&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>3曲目</td><td>Honey</td><td>3分13秒</td><td>¥200 - (<a href="http://a1574.phobos.apple.com/us/r2000/006/Music/v4/de/78/c6/de78c65c-7a54-328b-d541-5539d4edfb9a/mzaf_3727780456485437884.aac.m4a" title="DISC 1 ( 3曲目 ) - Honey"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/honey/id415428105?i=415428119&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>4曲目</td><td>Pretty Girl</td><td>3分27秒</td><td>¥200 - (<a href="http://a1197.phobos.apple.com/us/r2000/007/Music/v4/7d/a3/81/7da38131-923f-1d8e-0f3a-96db096fc9ba/mzaf_4519858752959030259.aac.m4a" title="DISC 1 ( 4曲目 ) - Pretty Girl"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/pretty-girl/id415428105?i=415428120&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>5曲目</td><td>Wanna</td><td>3分05秒</td><td>¥200 - (<a href="http://a799.phobos.apple.com/us/r2000/002/Music/v4/18/8e/c5/188ec5d4-ed64-c617-fd4f-30c3543c6fbf/mzaf_6047755769935791714.aac.m4a" title="DISC 1 ( 5曲目 ) - Wanna"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/wanna/id415428105?i=415428121&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>6曲目</td><td>Rock U</td><td>3分30秒</td><td>¥200 - (<a href="http://a1737.phobos.apple.com/us/r2000/020/Music/v4/00/fa/59/00fa5928-2aa1-d4ab-a5c2-b1958be8941a/mzaf_5966019303116524274.aac.m4a" title="DISC 1 ( 6曲目 ) - Rock U"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/rock-u/id415428105?i=415428122&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>7曲目</td><td>Umbrella</td><td>3分25秒</td><td>¥200 - (<a href="http://a1315.phobos.apple.com/us/r2000/002/Music/v4/84/ec/56/84ec564c-a03a-9381-6d60-4a1e8280b8ba/mzaf_1871121068460186878.aac.m4a" title="DISC 1 ( 7曲目 ) - Umbrella"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/umbrella/id415428105?i=415428123&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>8曲目</td><td>私は・・・(ing)</td><td>3分41秒</td><td>¥200 - (<a href="http://a1670.phobos.apple.com/us/r2000/018/Music/v4/0b/2c/64/0b2c640f-c2cc-db69-8312-068fe98940c3/mzaf_7157389587960403830.aac.m4a" title="DISC 1 ( 8曲目 ) - 私は・・・(ing)"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/siha-ing/id415428105?i=415428124&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>9曲目</td><td>Tasty Love</td><td>3分05秒</td><td>¥200 - (<a href="http://a1441.phobos.apple.com/us/r2000/016/Music/v4/75/de/29/75de290b-d498-4560-8c22-5c76fe6d859b/mzaf_6047310525388842934.aac.m4a" title="DISC 1 ( 9曲目 ) - Tasty Love"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/tasty-love/id415428105?i=415428125&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>10曲目</td><td>AHA</td><td>3分18秒</td><td>¥200 - (<a href="http://a305.phobos.apple.com/us/r2000/006/Music/v4/f0/c4/e3/f0c4e3a6-b373-c713-3343-bf1d1d1311be/mzaf_3302672689602256364.aac.m4a" title="DISC 1 ( 10曲目 ) - AHA"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/aha/id415428105?i=415428126&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>11曲目</td><td>Break It</td><td>3分15秒</td><td>¥200 - (<a href="http://a229.phobos.apple.com/us/r2000/010/Music/v4/c3/4a/6c/c34a6c9c-f1e2-6d56-c2d8-984b9af8cc17/mzaf_5724580218835652755.aac.m4a" title="DISC 1 ( 11曲目 ) - Break It"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/break-it/id415428105?i=415428127&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><tr><td>12曲目</td><td>Good Day</td><td>3分13秒</td><td>¥200 - (<a href="http://a1954.phobos.apple.com/us/r2000/008/Music/v4/81/e8/fe/81e8fe13-2aec-e3d6-76ee-5aadc280bca4/mzaf_8825154969491375557.aac.m4a" title="DISC 1 ( 12曲目 ) - Good Day"><img src="http://a1387.phobos.apple.com/us/r2000/014/Music/3c/ef/32/mzi.puxuvcgc.200x200-75.jpg" alt="" style="display:none" />視聴する</a>)</td><td><a href="https://itunes.apple.com/jp/album/good-day/id415428105?i=415428128&uo=4" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr></table></div>

		</li>

		<li>iTunes へのリンクをアフィリエイトにする場合は、<a href="https://www.apple.com/jp/itunes/affiliates/" target="_blank">iTunes アフィリエイトプログラム</a> への申し込みが必要です。</li>
		<li>アフィリエイト参加が承認されたら Auto Link の Javascript を設置すると自動的に全てのリンクがアフィリエイトとなります。</li>

	</ul>

	<h2>更新履歴</h2>
	<ul style="line-height:1.5em">
		<li> 2014年9月5日　PHG のアフィリエイトに対応しました。</li>
		<li> 2012年5月5日　LinkShare のアフィリエイトに対応しました。</li>
		<li> 2012年5月2日　収録楽曲付の HTML を生成できるようになりました。</li>
		<li> 2012年4月23日　公開しました。</li>
	</ul>

	<h2>その他</h2>
	<ul style="line-height:1.5em">
		<li><a href="http://dev.ontheroad.jp/tools/apple/itunessearch.php">iOS, Mac OS X アプリ検索 & ブログ用タグ生成ツール（PHG 対応版）</a>もよろしく。</li>
		<li>開発ブログ「<a href="http://dev.ontheroad.jp/">MacBook Air と Wordpress でこうなった</a>」</li>
	</ul>

<?php } else if( $resultCount == '' ) { ?>
	<h2>見つかりませんでした。</h2>


<?php } else { ?>

	<?php if($entity == 'album') { ?>
	<h2><?= $resultCount?>アイテムが見つかりました。</h2>
	<?php for( $n=0; $n<count($results); $n++ ) { ?>
		#<?= $n+1 ?><br>

		<div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px">
			<img src="<?= $artworkUrl100[$n] ?>"  style="float:left; width:80px; margin:0 30px 10px 0;" />	

			<div style="float:left; width:77%;">
				<div style="font-weight:bold;"><a href="<?php //$ls_trackingUrl ?><?= $collectionViewUrl[$n] ?>" target="_blank"><?= $collectionName[$n] ?></a></div>

				<div style="line-height:1.6em;font-size:0.85em;">
					アーティスト:<?= $artistName[$n] ?><br>
					カテゴリ:<?= $primaryGenreName[$n] ?><?php //echo '('.$discCount[$n].'枚組 - '?>(全<?= $trackCount[$n] ?>曲)<br>
					リリース:<?= substr( $releaseDate[$n], 0, 10 ) ?><br>
					レーベル:<?= $copyright[$n] ?><br>

					<?php if( $collectionPrice[$n] == 0 ) { ?>
						価格:無料<br>
					<?php } else { ?>
						価格<?= number_format($collectionPrice[$n]) ?>円<br>
					<?php } ?>
				</div>

				<a href="<?php // $ls_trackingUrl ?><?=$collectionViewUrl[$n]?>" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="margin: 10px 0 0 0;" /></a><br>
			</div>
			<div style="clear:both"></div>
		</div><br />

<textarea cols="85" rows="13" readonly onclick="this.select()">
<div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px"><img src="<?=$artworkUrl100[$n]?>"  style="float:left; width:80px; margin:0 30px 10px 0;" /><div style="float:left; width:77%;"><div style="font-weight:bold;"><a href="<?php //$ls_trackingUrl ?><?=$collectionViewUrl[$n]?>" target="_blank"><?= $collectionName[$n] ?></a></div><div style="line-height:1.6em;font-size:0.85em;"><?php echo 'アーティスト'.$artistName[$n].'<br>'?>カテゴリ:<?= $primaryGenreName[$n] ?>(全<?= $trackCount[$n] ?>曲)<br>リリース:<?= substr( $releaseDate[$n], 0, 10 ) ?><br>レーベル:<?= $copyright[$n] ?><br><?php if( $collectionPrice[$n] == 0 ) { ?>価格:無料<br><?php } else { ?>価格<?= number_format($collectionPrice[$n]) ?>円<br><?php } ?></div><a href="<?php // $ls_trackingUrl ?><?=$collectionViewUrl[$n]?>" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="margin: 10px 0 0 0;" /></a><br></div><div style="clear:both"></div></div><br />
</textarea><br />

		<?php if( $artistName[$n] <> 'Various Artists' && $artistName[$n] <> 'VARIOUS' ) { ?>
		<p>
		<form method="get" action="itunessearch_music.php">
			<input type="hidden" name="entity" value="album_w_song"/>
			<input type="hidden" name="kw" value="<?= $collectionName[$n] ?>"/>
			<input type="hidden" name="targetId" value="<?= $n ?>"/>
			<input type="hidden" name="targetArtist" value="<?= $artistName[$n] ?>"/>
			<input type="submit" value="収録楽曲リストをつける"/>
		</form>
		</p><br />
		<?php } ?>

	<?php } // end of for loop ?>

<?php } else if($entity == 'song') { ?>


<h2><?= $resultCount?>アイテムが見つかりました。</h2>
	<?php for( $n=0; $n<count($results); $n++ ) { ?>
		#<?= $n+1 ?>

		<script type="text/javascript" src="http://mediaplayer.yahoo.com/js"></script>
		<div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px">
			<img src="<?= $artworkUrl100[$n] ?>"  style="float:left; width:80px; margin:0 30px 10px 0;" />	

			<div style="float:left; width:77%;">
				<div style="font-weight:bold;"><a href="<?php // $ls_trackingUrl ?><?= $trackViewUrl[$n] ?>" target="_blank"><?= $trackName[$n] ?></a></div>
				<div style="line-height:1.6em;font-size:0.85em;">
					アーティスト:<?= $artistName[$n] ?><br>
					リリース:<?= substr( $releaseDate[$n], 0, 10 ) ?>(<?= $primaryGenreName[$n] ?>)<br>


					<?php if( $trackPrice[$n] == 0 ) { ?>
						価格:無料 - (<a href="<?= $previewUrl[$n] ?>" title="DISC <?= $discNumber[$n] ?> ( <?= $trackNumber[$n] ?>曲目 ) - <?= $trackName[$n] ?>"><img src="<?= $artworkUrl200[$n] ?>" alt="" style="display:none" />視聴する</a>)<br>
					<?php } else { ?>
						価格:<?= number_format($trackPrice[$n]) ?>円 - (<a href="<?= $previewUrl[$n] ?>" title="DISC <?= $discNumber[$n] ?> ( <?= $trackNumber[$n] ?>曲目 ) - <?= $trackName[$n] ?>"><img src="<?= $artworkUrl200[$n] ?>" alt="" style="display:none" />視聴する</a>)<br>
					<?php } ?>

					(アルバム：<a href="<?php // $ls_trackingUrl ?><?= $collectionViewUrl[$n] ?>" target="_blank"><?= $collectionName[$n] ?></a>の<?= $trackNumber[$n]?>曲目に収録されています)<br>

				</div>

				<a href="<?php // $ls_trackingUrl ?><?= $trackViewUrl[$n] ?>" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="margin: 10px 0 0 0;" /></a><br>
			</div>
			<div style="clear:both"></div>
		</div><br />

<textarea cols="85" rows="16" readonly onclick="this.select()">
<script type="text/javascript" src="http://mediaplayer.yahoo.com/js"></script><div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px"><img src="<?= $artworkUrl100[$n] ?>"  style="float:left; width:80px; margin:0 30px 10px 0;" /><div style="float:left; width:77%;"><div style="font-weight:bold;"><a href="<?php // $ls_trackingUrl ?><?= $trackViewUrl[$n] ?>" target="_blank"><?= $trackName[$n] ?></a></div><div style="line-height:1.6em;font-size:0.85em;">アーティスト:<?= $artistName[$n] ?><br>リリース:<?= substr( $releaseDate[$n], 0, 10 ) ?>(<?= $primaryGenreName[$n] ?>)<br><?php if( $trackPrice[$n] == 0 ) { ?>価格:無料 - (<a href="<?= $previewUrl[$n] ?>" title="DISC <?= $discNumber[$n] ?> ( <?= $trackNumber[$n] ?>曲目 ) - <?= $trackName[$n] ?>"><img src="<?= $artworkUrl200[$n] ?>" alt="" style="display:none" />視聴する</a>)<br><?php } else { ?>価格:<?= number_format($trackPrice[$n]) ?>円 - (<a href="<?= $previewUrl[$n] ?>" title="DISC <?= $discNumber[$n] ?> ( <?= $trackNumber[$n] ?>曲目 ) - <?= $trackName[$n] ?>"><img src="<?= $artworkUrl200[$n] ?>" alt="" style="display:none" />視聴する</a>)<br><?php } ?>(アルバム：<a href="<?php // $ls_trackingUrl ?><?= $collectionViewUrl[$n] ?>" target="_blank"><?= $collectionName[$n] ?></a>の<?= $trackNumber[$n]?>曲目に収録されています)<br></div><a href="<?php // $ls_trackingUrl ?><?= $trackViewUrl[$n] ?>" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="margin: 10px 0 0 0;" /></a><br></div><div style="clear:both"></div></div>
</textarea><br /><br />

	<?php } // end of for ループ?>

<?php } //end of if ?>

<?php if($entity == 'album_w_song') { ?>

	<?php if( count( $results ) == 0 ) { ?>
		<h2>楽曲リストが取得できませんでした。</h2>
	<?php exit(); } ?>

	<script type="text/javascript" src="http://mediaplayer.yahoo.com/js"></script>
	<div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px">
		<img src="<?= $artworkUrl100[0] ?>"  style="float:left; width:80px; margin:0 30px 10px 0;" /> 

		<div style="float:left; width:77%;">
			<div style="font-weight:bold;"><a href="<?php // $ls_trackingUrl ?><?= $collectionViewUrl[0] ?>" target="_blank"><?= $collectionName[0] ?></a></div>
			<div style="line-height:1.6em;font-size:0.8em">
				アーティスト:<?= $artistName[0] ?><br>

				<?php if( $discCount[0] == 1 ) { ?>
					カテゴリ:<?= $primaryGenreName[0] ?> ( 全<?= count( $results ) ?>曲 )<br>
				<?php } else { ?>
					カテゴリ:<?= $primaryGenreName[0] ?><?php echo ' ( '.$discCount[0].'枚組 - '?> 全<?= count( $results ) ?>曲 )<br>
				<?php } ?>

				リリース:<?= substr( $releaseDate[0], 0, 10 ) ?><br>

				<?php if( $collectionPrice[0] == 0 ) { ?>
					価格:無料<br>
				<?php } else { ?>
					価格<?= number_format($collectionPrice[0]) ?>円<br>
				<?php } ?>

				<a href="<?php // $ls_trackingUrl ?><?=$collectionViewUrl[$n]?>" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="margin: 10px 0 0 0;" /></a>
			</div>
		</div>
		<div class="clear"></div>

		<hr size="1">
	
		<table style="font-size:0.8em; width:100%;">
		<?php 
			$count = 1; 
			for( $n=0; $n<count($results); $n++ ) { 
		?>

			<?php if( $trackNumber[$n] == 1 ) { ?>
				<tr style="background:#E6E6E6"><th colspan="5">DISC <?= $count++ ?></th></tr>

				<tr>
					<th width="10%">曲順</th>
					<th width="">曲名</th>
					<th width="12%">収録時間</th>
					<th width="40%" colspan="2">購入（iTunes Store）</th>
				</tr>

			<?php  }  ?>


			<tr>
				<td><?= $trackNumber[$n] ?>曲目</td>
				<td><?= $trackName[$n] ?></td>

				<td><?= $trackTime_m[$n] ?>分<?= $trackTime_s[$n] ?>秒</td>

				<?php if( $trackPrice[$n] == 0 ) { ?>
					<td>無料 - (<a href="<?= $previewUrl[$n] ?>" title="DISC <?= $discNumber[$n] ?> ( <?= $trackNumber[$n] ?>曲目 ) - <?= $trackName[$n] ?>"><img src="<?= $artworkUrl200[$n] ?>" alt="" style="display:none" />視聴する</a>)</td>
				<?php } else if(	 $trackPrice[$n] == -1 ) { ?>
					<td>アルバムのみ - (<a href="<?= $previewUrl[$n] ?>" title="DISC <?= $discNumber[$n] ?> ( <?= $trackNumber[$n] ?>曲目 ) - <?= $trackName[$n] ?>"><img src="<?= $artworkUrl200[$n] ?>" alt="" style="display:none" />視聴する</a>)</td>
				<?php } else { ?>
					<td>¥<?= number_format($trackPrice[$n]) ?> - (<a href="<?= $previewUrl[$n] ?>" title="DISC <?= $discNumber[$n] ?> ( <?= $trackNumber[$n] ?>曲目 ) - <?= $trackName[$n] ?>"><img src="<?= $artworkUrl200[$n] ?>" alt="" style="display:none" />視聴する</a>)</td>
				<?php } ?>

				<td><a href="<?php // $ls_trackingUrl ?><?=$trackViewUrl[$n]?>" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px;"/></a></td>

			</tr>
				
		<?php } // end for ?>
		</table>
	</div><br />

<textarea cols="85" rows="100" readonly onclick="this.select()">
<script type="text/javascript" src="http://mediaplayer.yahoo.com/js"></script><div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px"><img src="<?= $artworkUrl100[0] ?>"  style="float:left; width:80px; margin:0 30px 10px 0;" /><div style="float:left; width:77%;"><div style="font-weight:bold;"><a href="<?php // $ls_trackingUrl ?><?= $collectionViewUrl[0] ?>" target="_blank"><?= $collectionName[0] ?></a></div><div style="line-height:1.6em;font-size:0.8em">アーティスト:<?= $artistName[0] ?><br><?php if( $discCount[0] == 1 ) { ?>カテゴリ:<?= $primaryGenreName[0] ?> ( 全<?= count( $results ) ?>曲 )<br><?php } else { ?>カテゴリ:<?= $primaryGenreName[0] ?><?php echo ' ( '.$discCount[0].'枚組 - '?> 全<?= count( $results ) ?>曲 )<br><?php } ?>リリース:<?= substr( $releaseDate[0], 0, 10 ) ?><br><?php if( $collectionPrice[0] == 0 ) { ?>価格:無料<br><?php } else { ?>価格<?= number_format($collectionPrice[0]) ?>円<br><?php } ?><a href="<?php // $ls_trackingUrl ?><?=$collectionViewUrl[$n]?>" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="margin: 10px 0 0 0;" /></a></div></div><div class="clear"></div><hr size="1"><table style="font-size:0.8em; width:100%;"><?php $count = 1; for( $n=0; $n<count($results); $n++ ) { ?><?php if( $trackNumber[$n] == 1 ) { ?><tr style="background:#E6E6E6"><th colspan="5">DISC <?= $count++ ?></th></tr><tr><th width="10%">曲順</th><th width="">曲名</th><th width="12%">収録時間</th><th width="40%" colspan="2">購入（iTunes Store）</th></tr><?php  }  ?><tr><td><?= $trackNumber[$n] ?>曲目</td><td><?= $trackName[$n] ?></td><td><?= $trackTime_m[$n] ?>分<?= $trackTime_s[$n] ?>秒</td><?php if( $trackPrice[$n] == 0 ) { ?><td>無料 - (<a href="<?= $previewUrl[$n] ?>" title="DISC <?= $discNumber[$n] ?> ( <?= $trackNumber[$n] ?>曲目 ) - <?= $trackName[$n] ?>"><img src="<?= $artworkUrl200[$n] ?>" alt="" style="display:none" />視聴する</a>)</td><?php } else if( $trackPrice[$n] == -1 ) { ?><td>アルバムのみ - (<a href="<?= $previewUrl[$n] ?>" title="DISC <?= $discNumber[$n] ?> ( <?= $trackNumber[$n] ?>曲目 ) - <?= $trackName[$n] ?>"><img src="<?= $artworkUrl200[$n] ?>" alt="" style="display:none" />視聴する</a>)</td><?php } else { ?><td>¥<?= number_format($trackPrice[$n]) ?> - (<a href="<?= $previewUrl[$n] ?>" title="DISC <?= $discNumber[$n] ?> ( <?= $trackNumber[$n] ?>曲目 ) - <?= $trackName[$n] ?>"><img src="<?= $artworkUrl200[$n] ?>" alt="" style="display:none" />視聴する</a>)</td><?php } ?><td><a href="<?php // $ls_trackingUrl ?><?=$trackViewUrl[$n]?>" target="_blank"><img src="http://dev.ontheroad.jp/tools/apple/img/viewinitunes_jp.png" style="width:70px" /></a></td></tr><?php } // end for ?></table></div>
</textarea>

<?php } // end if?>	


<?php } // end if ?>

<br />

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

</div><!-- end of #content -->

<div id="sidebar">

<?php
	echo '<h3>iTunes トップアルバム ベスト 30</h3>';
	$url = "https://itunes.apple.com/jp/rss/topalbums/limit=30/xml";			// 全てのジャンル
	$url = "https://itunes.apple.com/jp/rss/topalbums/limit=30/genre=27/xml";	// J-POP
	echo get_item_list_html( $url, $entity );
?>

</div><!-- end of #sidebar -->
</div><!-- end of #wrapper -->

</body></html>

<?php
	function get_item_list_html( $url, $entity ) {
		$xmlstr = file_get_contents( $url );
		$rss = simplexml_load_string( $xmlstr );
		$feed = $rss->feed;
		$last_updated = $rss->updated;

//		$output .= '<h4>（更新日：'.$last_update.'）</h4>';
		$output .= '<div class="item_list">'."\n";
			foreach ($rss->entry as $entry) {

				$title = $entry->title;
				$summary = $entry->summary;
				$price = $entry->children( "im", true )->price;
				$image = $entry->children( "im", true )->image;
				$release_date = $entry->children( "im", true )->releaseDate;
				$category = $entry->category['label'];

				$output .= '<div style="float:left; margin:0 0 25px 0;">';
				$output .= '<img src="'.$image.'" style="margin:0 10px 0 0" /></div>';

				$output .= '<div class="info">';
				$output .= '（'.$category.'）';
				$output .= '<br>'.$title;
				$output .= '<br>'.$price;
				$output .= ' - <a href="http://dev.ontheroad.jp/tools/apple/itunessearch_music.php?country=jp&entity=album&kw='.str_replace('"', '',$title).'&lstrackingUrl=">タグ生成</a>';
				$output .= '</div>'."\n";
				$output .= '<div style="clear:both"></div>';
			}
		$output .= '</div>'."\n";
		return $output;
	}
?>
