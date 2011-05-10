<?php

if (! defined('LONCAT_VERSION'))
{
    define('LONCAT_VERSION', '0.9.1');
    define('LONCAT_NAME', 'Lonely Cat');
    define('LONCAT_DESCRIPTION', 'Add a single category to an entry through a simple dropdown.');
}

$config['name'] = LONCAT_NAME;
$config['version'] = LONCAT_VERSION;
$config['description'] = LONCAT_DESCRIPTION;
$config['nsm_addon_updater']['versions_xml'] = '';