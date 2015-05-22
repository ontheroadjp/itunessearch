<?php
function get_post( $val ) {

	// http, https, ftp を含む URL ははじく
	if( preg_match( "/(http|https|ftp):\/\/", $val ) ) {
	} else {
		return htmlspecialchars( $val, ENT_QUOTES, 'UTF-8' );
	}
}

function getSelectValue( $entity, $val, $label ) {
	if( $entity == $val ) {
		return '<option value="'.$val.'" selected>'.$label.'</option>';
	} else {
		return '<option value="'.$val.'">'.$label.'</option>';
	}
}
?>
