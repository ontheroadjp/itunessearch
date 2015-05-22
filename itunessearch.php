<?php
	function get_post( $val ) {
	
		// http, https, ftp を含む URL ははじく
		if( preg_match( "/(http|https|ftp):\/\/", $val ) ) {
		} else {
			return htmlspecialchars( $val, ENT_QUOTES, 'UTF-8' );
		}
	}
?>

<?php
	// パラメーターの取得
	$term 			= get_post( str_replace( ' ', '+', $_POST['kw'] ) );
	$entity 		= get_post( $_POST['entity'] );
	$country		= get_post( $_POST['country'] );


/*
	echo '$term = '.$term.'<br>';
	echo '$entity = '.$entity.'<br>';
	echo '$country = '.$country.'<br>';
*/


	$affID		= get_post( str_replace( ' ', '', $_POST['affID'] ) );		// PHG アフィリエイトトークン
	$is_id_save		= get_post( $_POST['is_id_save'] );
	
	$trackId	= get_post( $_POST['trackId'] );

	$dev			= get_post( $_POST['dev'] );
	$debug		= get_post( $_POST['debug'] );

	if( $entity == 'software' ) {
		$target_name = 'iPhone アプリ';
	} else if( $entity == 'iPadSoftware' ) {
		$target_name = 'iPad アプリ';
	} else if( $entity == 'macSoftware' ) {
		$target_name = 'Mac アプリ';
	}
	
	$site_title = 'iOS, Mac OS X アプリ検索 & ブログ用タグ生成ツール（PHG 対応版）';

	if( $term == '' && $entity == '' && $trackId == '') {
		$mode = "HOME";
		$page_title	= $site_title;
		$description = "iPhone, iPad, Mac OS X用のアプリをブログで紹介するための HTML を自動生成します。";

		// クッキーの処理
		if( $_COOKIE['affID'] ) {
			$affID = $_COOKIE['affID'];
		}

	} else if( $term <> '' && $entity <> '' && $trackId == '' ) {
		$mode = "SEARCH";
		$page_title		= $target_name."「".$term."」の検索結果";
		$description = $target_name."「".$term."」をブログで紹介するための HTML を自動生成します。";

		// クッキーの更新
		if ( $is_id_save == 1 ) {
			setcookie( "affID", $affID, time()+60*60*24*365 );
			if( $affID == '' && $_COOKIE['affID'] ) {
				$affID = $_COOKIE['affID'];
			}
		} else if( $is_id_save == 0 ) {
			setcookie( "affID", $affID, time()-1800 );
		}
		
	} else {
		$mode = "ERROR";
		$page_title		= "エラー";
	}

	// 初期設定
	if ( isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on' ) {
	    $protocol = 'https://';  
	} else {
	    $protocol = 'http://';  
	}
	$selfUrl	= $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];  
	$rootUrl	= "http://dev.ontheroad.jp/tools/apple/itunessearch.php";
	$rootUrl_shortan = "http://bit.ly/1uXLsk4";

	// iTunes API REST パラメーター
	$searchurl	= 'http://itunes.apple.com/search?';
	$lookupurl	= 'http://itunes.apple.com/lookup?';
//	$lang		= 'ja_jp';
	$lang		= 'en_us';

	$url = $searchurl.'term='.$term.'&country='.$country.'&entity='.$entity;

	// REST リクエストの発行
	require_once( 'HTTP/Request.php' );

	$request =new HTTP_Request( $url );
	$result = $request->sendRequest(); 

	//レスポンスの本文を取得
	$json = $request->getResponseBody(); 

//	$data = json_decode( $json );			//オブジェクトを返す
	$data = json_decode( $json , true );	//連想配列を返す

	//データ取得
	$resultCount	= $data['resultCount'];
	$results		= $data['results'];
	for( $n=0; $n<count( $results ); $n++ ) {
		$artworkUrl100[$n]		= $results[$n]['artworkUrl100'];		//商品画像
		$collectionName[$n]		= $results[$n]['collectionName'];		//商品名
		$version[$n]			= $results[$n]['version'];				//バージョン
		$description[$n]		= $results[$n]['description'];			//概要
		$trackName[$n]			= $results[$n]['trackName'];			//曲名
		$releaseDate[$n]		= $results[$n]['releaseDate'];			//リリース日
		$fileSizeBytes[$n]		= $results[$n]['fileSizeBytes'];		//ファイルサイズ
		$sellerName[$n]			= $results[$n]['sellerName'];			//販売元
		$artistName[$n]			= $results[$n]['artistName'];			//開発元
		$currency[$n]			= $results[$n]['currency'];				//通貨
		$price[$n]				= $results[$n]['price'];				//価格（アプリ）
		$collectionPrice[$n]	= $results[$n]['collectionPrice'];		//価格（ミュージック：アルバム）
		$trackPrice[$n]			= $results[$n]['trackPrice'];			//価格（ミュージック：1曲）
		$artistViewUrl[$n]		= $results[$n]['artistViewUrl'];		//iTunes へのリンク
		$trackViewUrl[$n]		= $results[$n]['trackViewUrl'];			//Apple ページへのリンク
		$trackId[$n]			= $results[$n]['trackId'];				//Apple ページへのリンク
		$genres[$n]				= $results[$n]['genres'];				//カテゴリ
	}


/*
	// LinkShare トラッキング URL の設定
	if( $affID <> '' ) {
//		if( preg_match( "/(http|ftp):\/\/.+/", $affID ) ) {
		if( preg_match( "/^http:\/\/click\.linksynergy\.com\/fs-bin\/stat\?id=.+&RD_PARM1=$/", $affID ) ) {
			$ls_trackingUrl = $affID;
		} else {
			$affID = "入力が正しくありません。生成された HTML はアフィリリンクではありませんがそのまま使えます。";
			$ls_trackingUrl = "http://click.linksynergy.com/fs-bin/stat?id=cSxHJqdiAjg&offerid=94348&type=3&subid=0&tmpid=2192&RD_PARM1=";
		}
	} else {
		$ls_trackingUrl	= "http://click.linksynergy.com/fs-bin/stat?id=cSxHJqdiAjg&offerid=94348&type=3&subid=0&tmpid=2192&RD_PARM1=";
	}
*/
?>

<?php
	function getSelectValue( $entity, $val, $label ) {
		if( $entity == $val ) {
			return '<option value="'.$val.'" selected>'.$label.'</option>';
		} else {
			return '<option value="'.$val.'">'.$label.'</option>';
		}
	}
?>

<!-- ------------------ HTMLここから ------------------------ -->
<!DOCTYPE html><head><meta charset="UTF-8"><title><?= $page_title ?></title>

<!-- ----------------------- CSS ---------------------------- -->
<style type="text/css"><!--
	body {

	}
--></style>

<link rel="stylesheet" type="text/css" href="./style.css" />


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
<meta property="og:description" content="<?= $description ?>" />
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

			<?php if( $mode == 'HOME' || ( $affID <> '' && $is_id_save == 1 ) ) { ?>
				<input id="affID" type="text" size="10" name="affID" value="<?= $affID ?>">
				<select id="is_id_save" name="is_id_save"><option value="1" selected>アフィリエイトトークンを保存する</option>
				<option value="0">アフィリエイトトークンを保存しない</option></select>
			<?php } else { ?>
				<input id="affID" type="text" size="10" name="affID" value="">
				<select id="is_id_save" name="is_id_save"><option value="1">アフィリエイトトークンを保存する</option>
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

<?php
	switch( $entity ) {

		case 'software':
/*			echo '<h3>新着 iOS App</h3>';
			$url = "http://itunes.apple.com/jp/rss/newapplications/limit=10/xml";
			echo get_item_list_html( $url, $entity );
*/
			echo '<h3 class="item_title">無料 iOS App トップ 10（総合）</h3>';
			$url = "http://itunes.apple.com/jp/rss/topfreeapplications/limit=10/xml";
			echo get_item_list_html( $url, $entity, $entity.'_top10_free', $is_id_save );

			echo '<h3 class="item_title">有料 iOS App トップ 10（総合）</h3>';
			$url = "http://itunes.apple.com/jp/rss/toppaidapplications/limit=10/xml";
			echo get_item_list_html( $url, $entity, $entity.'_top10_paid', $is_id_save );
		break;

		case 'iPadSoftware':
			echo '<h3 class="item_title">無料 iPad App トップ 10（総合）</h3>';
			$url = "http://itunes.apple.com/jp/rss/topfreeipadapplications/limit=10/xml";
			echo get_item_list_html( $url, $entity, $entity.'_top10_free', $is_id_save );

			echo '<h3 class="item_title">有料 iPad App トップ 10（総合）</h3>';
			$url = "http://itunes.apple.com/jp/rss/toppaidipadapplications/limit=10/xml";
			echo get_item_list_html( $url, $entity, $entity.'_top10_paid', $is_id_save );
		break;

		case 'macSoftware':
			echo '<h3 class="item_title">無料 Mac App トップ 10（総合）</h3>';
			$url = "http://itunes.apple.com/jp/rss/topfreemacapps/limit=10/xml";
			echo get_item_list_html( $url, $entity, $entity.'_top10_free', $is_id_save );

			echo '<h3 class="item_title">有料 Mac App トップ 10（総合）</h3>';
			$url = "http://itunes.apple.com/jp/rss/toppaidmacapps/limit=10/xml";
			echo get_item_list_html( $url, $entity, $entity.'_top10_paid', $is_id_save );
		break;
	}
?>

</div><!-- end of #sidebar -->
</div><!-- end of #wrapper -->

<!-- iTunes Auto Link Maker（https://autolinkmaker.itunes.apple.com/jp/?at=10lKgz） -->
<script type='text/javascript'>var _merchantSettings=_merchantSettings || [];_merchantSettings.push(['AT', '10lKgz']);(function(){var autolink=document.createElement('script');autolink.type='text/javascript';autolink.async=true; autolink.src= ('https:' == document.location.protocol) ? 'https://autolinkmaker.itunes.apple.com/js/itunes_autolinkmaker.js' : 'http://autolinkmaker.itunes.apple.com/js/itunes_autolinkmaker.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(autolink, s);})();</script>

<br />

</body></html>


<?php
	function get_item_list_html( $url, $entity, $prefix, $is_id_save ) {
		$xmlstr = file_get_contents( $url );
		$rss = simplexml_load_string( $xmlstr );
		$feed = $rss->feed;
		$last_updated = $rss->updated;

//		$output .= '<h4>（更新日：'.$last_update.'）</h4>';
		$output .= '<div class="item_list">'."\n";
			$n = 0;
			foreach ($rss->entry as $entry) {

				$page_title = $entry->title;
				$summary = $entry->summary;
				$price = $entry->children( "im", true )->price;
				$image = $entry->children( "im", true )->image;
				$release_date = $entry->children( "im", true )->releaseDate;
				$category = $entry->category['label'];

				$output .= '<form name="form_'.$prefix.'_'.$n.'" method="POST" action="itunessearch.php">';
				$output .= '<input type="hidden" name="country" value="jp">';
				$output .= '<input type="hidden" name="entity" value="'.$entity.'">';
				$output .= '<input type="hidden" name="kw" value="'.str_replace('"', '',$page_title).'">';
				$output .= '<input type="hidden" id=affID_'.$prefix.'_'.$n.' name="affID" value="'.$affID.'">';
				$output .= '<input type="hidden" id=is_id_save_'.$prefix.'_'.$n.' name="is_id_save" value="'.$is_id_save.'">';
					
				$output .= '</form>';

				$output .= '<div style="float:left; margin:0 0 25px 0;">';
				$output .= '<img src="'.$image.'" /></div>';

				$output .= '<div class="info">';
				$output .= '（'.$category.'）';
				$output .= '<br>'.$page_title;
				$output .= '<br>'.$price;

//				$output .= ' - <a href="#" onClick="document.form_'.$prefix.'_'.$n.'.submit()">タグ生成</a>';
				$output .= ' - <a href="#" onClick="
															var target = document.getElementById(\'affID_'.$prefix.'_'.$n.'\');
															 target.value = document.getElementById(\'affID\').value;
															var is_id_save = document.getElementById(\'is_id_save_'.$prefix.'_'.$n.'\');
															 is_id_save.value = document.getElementById(\'is_id_save\').value;
															 document.form_'.$prefix.'_'.$n.'.submit();
															 
														">タグ生成</a>';
				
				$output .= '</div>'."\n";
				$n++;
			}
		$output .= '</div>'."\n";
		return $output;
	}
?>
