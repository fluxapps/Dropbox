<?php
require_once('./Modules/Cloud/classes/class.ilCloudPluginConfigGUI.php');
/**
 * Cloud configuration user interface class
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ilDropboxConfigGUI extends ilCloudPluginConfigGUI {

	/**
	 * @return array
	 */
	public function getFields() {
		return array(
			"app_name"                   => array(
				"type"        => "ilTextInputGUI",
				"info"        => "config_info_app_name",
				"subelements" => null,
			),
			"app_key"                    => array(
				"type"        => "ilTextInputGUI",
				"info"        => "config_info_app_key",
				"subelements" => null,
			),
			"app_secret"                 => array(
				"type"        => "ilTextInputGUI",
				"info"        => "config_info_app_secret",
				"subelements" => null,
			),
			"config_default_online"      => array(
				"type"        => "ilCheckboxInputGUI",
				"info"        => "config_info_default_online",
				"subelements" => null,
			),
			"config_max_file_size"       => array(
				"type"        => "ilCheckboxInputGUI",
				"info"        => "config_info_config_max_upload_size",
				"subelements" => null,
			),
			"default_max_file_size"      => array(
				"type"        => "ilNumberInputGUI",
				"info"        => "config_info_default_max_upload_size",
				"subelements" => null,
			),
			"default_allow_public_links" => array(
				"type"        => "ilCheckboxInputGUI",
				"info"        => "default_info_config_allow_public_links",
				"subelements" => array(
					"config_allow_public_links" => array(
						"type"        => "ilCheckboxInputGUI",
						"info"        => "config_default_config_allow_public_links_info",
						"subelements" => null,
					),
				),
			),
		);
	}
}
