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
	return "<a href='https://www.czechultimate.cz/'><img class='header_logo' style='height: 68px;' src='cust/cauf/logo.svg'/></a>";
}
