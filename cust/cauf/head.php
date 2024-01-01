<?php
function logo()
{
	/*global $styles_prefix;
	global $include_prefix;
	if (!isset($styles_prefix)) {
		$styles_prefix = $include_prefix;
	}
	return "<img class='header_logo' src='" . $styles_prefix . "cust/cauf/logo.png' alt='" . _("LOGO EXAMPLE") . "'/>";*/
}

function pageHeader()
{
	return "<a href='https://www.caufrisbee.cz'><img class='header_logo' src='cust/cauf/logo.png'/></a>";
}
