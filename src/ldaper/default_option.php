<?php
/**
 * Default module settings.
 */

$ldaper_default_option = array(
    'host'                 => 'localhost',
    'port'	               => 389,
    'protocol'	           => '',

    // Bind anonymously by default.
    'binddn'	           => '',
    'bindpw'	           => '',

    'basedn'               => '',

    'login_attribute'      => 'uid',
    'mail_attribute'       => 'mail',
    'mail_attribute_index' => null,
);
