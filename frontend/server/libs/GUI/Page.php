<?php


interface Page{
	
	
	function addJs( $url );

	function addCss( $url );

	function addHeader( $html );

	function addMenu( $html );

	function addContent( $html );

	function addFooter( $html );

	function render();

}