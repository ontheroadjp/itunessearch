<?php
switch( $entity ) {

case 'software':
/*			echo '<h3>新着 iOS App</h3>';
			$url = "http://itunes.apple.com/jp/rss/newapplications/limit=10/xml";
			echo get_item_list_html( $url, $entity );
 */
	echo '<h3 class="item_title">無料 iOS App トップ 10（総合）</h3>';
	$url = "http://itunes.apple.com/jp/rss/topfreeapplications/limit=10/xml";
	echo get_item_list_html( $url, $entity, $entity.'_top10_free', $is_phg_token_save );

	echo '<h3 class="item_title">有料 iOS App トップ 10（総合）</h3>';
	$url = "http://itunes.apple.com/jp/rss/toppaidapplications/limit=10/xml";
	echo get_item_list_html( $url, $entity, $entity.'_top10_paid', $is_phg_token_save );
	break;

case 'iPadSoftware':
	echo '<h3 class="item_title">無料 iPad App トップ 10（総合）</h3>';
	$url = "http://itunes.apple.com/jp/rss/topfreeipadapplications/limit=10/xml";
	echo get_item_list_html( $url, $entity, $entity.'_top10_free', $is_phg_token_save );

	echo '<h3 class="item_title">有料 iPad App トップ 10（総合）</h3>';
	$url = "http://itunes.apple.com/jp/rss/toppaidipadapplications/limit=10/xml";
	echo get_item_list_html( $url, $entity, $entity.'_top10_paid', $is_phg_token_save );
	break;

case 'macSoftware':
	echo '<h3 class="item_title">無料 Mac App トップ 10（総合）</h3>';
	$url = "http://itunes.apple.com/jp/rss/topfreemacapps/limit=10/xml";
	echo get_item_list_html( $url, $entity, $entity.'_top10_free', $is_phg_token_save );

	echo '<h3 class="item_title">有料 Mac App トップ 10（総合）</h3>';
	$url = "http://itunes.apple.com/jp/rss/toppaidmacapps/limit=10/xml";
	echo get_item_list_html( $url, $entity, $entity.'_top10_paid', $is_phg_token_save );
	break;
}
?>



<?php
function get_item_list_html( $url, $entity, $prefix, $is_phg_token_save ) {
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
		$output .= '<input type="hidden" id=phg_token_'.$prefix.'_'.$n.' name="phg_token" value="'.$phg_token.'">';
		$output .= '<input type="hidden" id=is_phg_token_save_'.$prefix.'_'.$n.' name="is_phg_token_save" value="'.$is_phg_token_save.'">';

		$output .= '</form>';

		$output .= '<div style="float:left; margin:0 0 25px 0;">';
		$output .= '<img src="'.$image.'" /></div>';

		$output .= '<div class="info">';
		$output .= '（'.$category.'）';
		$output .= '<br>'.$page_title;
		$output .= '<br>'.$price;

		//				$output .= ' - <a href="#" onClick="document.form_'.$prefix.'_'.$n.'.submit()">タグ生成</a>';
		$output .= ' - <a href="#" onClick="
			var target = document.getElementById(\'phg_token_'.$prefix.'_'.$n.'\');
		target.value = document.getElementById(\'phg_token\').value;
		var is_phg_token_save = document.getElementById(\'is_phg_token_save_'.$prefix.'_'.$n.'\');
		is_phg_token_save.value = document.getElementById(\'is_phg_token_save\').value;
		document.form_'.$prefix.'_'.$n.'.submit();

		">タグ生成</a>';

		$output .= '</div>'."\n";
		$n++;
	}
	$output .= '</div>'."\n";
	return $output;
}
?>

