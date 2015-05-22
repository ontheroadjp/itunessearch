<?php if( $resultCount == '' ) { ?><h2>見つかりませんでした。<br>（<?php echo $page_title; ?>）</h2><?php exit(); } ?>


<h2><?= $resultCount?>アイテムが見つかりました。</h2>
<p>
<?php if( $phg_token <> '' ) { ?>
全ての HTML には PHG アフィリエイトトークン（<?= $phg_token ?>）が埋め込まれています。
<?php } else { ?>
HTML には PHG アフィリエイトトークンは埋め込まれていません。アフィリエイトリンクにするためには検索バー下の「アフィリエイトトークン」を入力して再度検索するかご自身のWEB サイトに <a href="https://autolinkmaker.itunes.apple.com/?uo=8" target="_blank">Auto Link Maker</a> を設置してください（<a href="http://dev.ontheroad.jp/archives/13267" target="_blank">ヘルプ</a>）。<br>
<br>
アフィリエイトリンクにしない場合は、生成された HTML はこのまま使えます。
<?php } ?>
</p>



<?php for( $n=0; $n<count($results); $n++ ) { ?>


	<h3>#<?=$n+1?></h3>
	

<!-- ------------------------------------------------------------------- -->
<div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px">
<img src="<?=$artworkUrl100[$n]?>"  style="float:left; width:80px;" />	

<div style="float:none; margin-left:100px;">

<div style="font-weight:bold;"><a style="margin: 0 10px 0 0" href="<?php //$ls_trackingUrl ?><?=$trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="_blank"><?= $trackName[$n] ?></a><a href="<?=$trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="itunes_store" style="display:inline-block;overflow:hidden;background:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.png) no-repeat;width:81px;height:15px;@media only screen{background-image:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.svg);}"></a>
</div>

<div style="font-size:0.9em">( v<?= $version[$n] ?> - <?= round( $fileSizeBytes[$n] / 1000000, 1 ); ?>MB )</div>
	<div style="font-size:0.9em;margin:5px 0;line-height:1.6em">
		カテゴリ:<?= $genres[$n][0] ?><br>
		<!-- リリース:<?= substr( $releaseDate[$n], 0, 10 ) ?><br> -->
		販売元:<?= $sellerName[$n] ?><br>
		<?php if( $price[$n] == 0 ) { ?>
			価格:無料
		<?php } else { ?>
			価格:<?= number_format($price[$n]) ?>円
		<?php } ?>
	</div>
</div>

</div>
	<!-- ------------------------------------------------------------------- -->

<br />

<!-- HTML タグコード -->
<textarea cols="82" rows="16" readonly onclick="this.select()">
<div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px"><img src="<?=$artworkUrl100[$n]?>"  style="float:left; width:80px;" /><div style="float:none; margin-left:100px;"><div style="font-weight:bold;"><a style="margin: 0 10px 0 0" href="<?=$trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="_blank"><?= $trackName[$n] ?></a><a href="<?=$trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="itunes_store" style="display:inline-block;overflow:hidden;background:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.png) no-repeat;width:81px;height:15px;@media only screen{background-image:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.svg);}"></a></div><div style="font-size:0.9em">( v<?= $version[$n] ?> - <?= round( $fileSizeBytes[$n] / 1000000, 1 ); ?>MB )</div><div style="font-size:0.9em;margin:5px 0;line-height:1.6em">カテゴリ:<?= $genres[$n][0] ?><br>販売元:<?= $sellerName[$n] ?><br><?php if( $price[$n] == 0 ) { ?>価格:無料<?php } else { ?>価格:<?= number_format($price[$n]) ?>円<?php } ?></div>
</div></div>
</textarea>
<!-- HTML タグコード -->


<br /><br /><br />



<!-- ------------------------------------------------------------------- -->
<div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px">
<img src="<?=$artworkUrl100[$n]?>"  style="float:left; width:40px;" />	

<div style="float:none; margin-left:60px;">
<div style="font-weight:bold;"><a style="margin: 0 10px 0 0" href="<?php //$ls_trackingUrl ?><?=$trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="_blank"><?= $trackName[$n] ?></a><a href="<?=$trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="itunes_store" style="display:inline-block;overflow:hidden;background:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.png) no-repeat;width:81px;height:15px;@media only screen{background-image:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.svg);}"></a>
</div>

<div style="font-size:0.9em">
		<?php if( $price[$n] == 0 ) { ?>
			価格:無料 - <?= $sellerName[$n] ?>
		<?php } else { ?>
			価格:<?= number_format($price[$n]) ?>円 - <?= $sellerName[$n] ?>
		<?php } ?>
</div>

</div>
</div>
<!-- ------------------------------------------------------------------- -->

<br />

<!-- HTML タグコード -->
<textarea cols="82" rows="16" readonly onclick="this.select()">
<div style="border:1px solid #DDDDDD; background:#F7F7F7; padding:10px"><img src="<?=$artworkUrl100[$n]?>"  style="float:left; width:40px;" /><div style="float:none; margin-left:60px;"><div style="font-weight:bold;"><a style="margin: 0 10px 0 0" href="<?=$trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="_blank"><?= $trackName[$n] ?></a><a href="<?=$trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="itunes_store" style="display:inline-block;overflow:hidden;background:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.png) no-repeat;width:81px;height:15px;@media only screen{background-image:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.svg);}"></a></div><div style="font-size:0.9em"><?php if( $price[$n] == 0 ) { ?>価格:無料 - <?= $sellerName[$n] ?><?php } else { ?>価格:<?= number_format($price[$n]) ?>円 - <?= $sellerName[$n] ?><?php } ?></div>
</div></div>
</textarea>
<!-- HTML タグコード -->








<p>テキストリンク：
<a href="<?php //$ls_trackingUrl ?><?= $trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="_blank"><?= $trackName[$n] ?></a> 
<br>

<!-- HTML タグコード -->
<textarea cols="82" rows="5" readonly onclick="this.select()">
&lt;a href="<?php //$ls_trackingUrl ?><?= $trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="_blank"><?= $trackName[$n] ?>&lt;/a>
</textarea><br />

<p>ボタンリンク：
<a href="<?=$trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="itunes_store" style="display:inline-block;overflow:hidden;background:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.png) no-repeat;width:81px;height:15px;@media only screen{background-image:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.svg);}"></a>
<br>
<textarea cols="82" rows="5" readonly onclick="this.select()">
&lt;a href="<?=$trackViewUrl[$n] ?>&at="<?= $phg_token ?>" target="itunes_store" style="display:inline-block;overflow:hidden;background:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.png) no-repeat;width:81px;height:15px;@media only screen{background-image:url(https://linkmaker.itunes.apple.com/htmlResources/assets//images/web/linkmaker/badge_macappstore-sm.svg);}">&lt;/a>
</textarea><br />

	<?php } // end of for ループ?>

<?php //} // end if ?>



<br />
