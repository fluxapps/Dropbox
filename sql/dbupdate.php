<#1>
<?php
include_once("./Customizing/global/plugins/Modules/Cloud/CloudHook/Dropbox/classes/class.ilDropboxPlugin.php");

$plugin_object = new ilDropboxPlugin();

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 8,
        'notnull' => true
    ),
    'token' => array(
        'type'   => 'text',
        'length' => 256
    ),
    'public_link' => array(
        'type' => 'boolean',
    )
);
if(!$ilDB->tableExists($plugin_object->getPluginTableName())) {
    $ilDB->createTable($plugin_object->getPluginTableName(), $fields);
    $ilDB->addPrimaryKey($plugin_object->getPluginTableName(), array("id"));
}
?>
<#2>
<?php
include_once("./Modules/Cloud/classes/class.ilCloudPluginConfig.php");
$plugin_object = new ilDropboxPlugin();
$config_object = new ilCloudPluginConfig($plugin_object->getPluginConfigTableName());
$config_object->initDB();
$config_object->setAppName("Custom App Name");
$config_object->setAppKey("Custom App Key");
$config_object->setAppSecret("Custom App Secret");
$config_object->setConfAllowPublicLinks(false);
$config_object->setDefaultAllowPublicLinks(true);
$config_object->setDefaultMaxFileSize(30);
?>
<#3>
<?php
include_once("./Customizing/global/plugins/Modules/Cloud/CloudHook/Dropbox/classes/class.ilDropboxPlugin.php");
$plugin_object = new ilDropboxPlugin();
if(!$ilDB->tableColumnExists($plugin_object->getPluginTableName(),'max_file_size'))
{
    $ilDB->addTableColumn($plugin_object->getPluginTableName(),"max_file_size",array('type'   => 'text','length' => 256));
}
?>
