<?php

include_once("./Modules/Cloud/classes/class.ilCloudPluginSettingsGUI.php");

/**
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilDropboxSettingsGUI: ilObjCloudGUI
 * @ingroup ModulesCloud
 */
class ilDropboxSettingsGUI extends ilCloudPluginSettingsGUI {

	/**
	 * @return bool
	 */
	public function getMakeOwnPluginSection() {
		/**
		 * if($this->getAdminConfigObject()->getConfigAllowPublicLinks() || $this->getAdminConfigObject()->getConfigMaxFileSize())
		 * {
		 * return true;
		 * }**/
		return false;
	}


	public function initPluginSettings() {
		if ($this->getAdminConfigObject()->getDefaultAllowPublicLinksConfigAllowPublicLinks()
			&& $this->getAdminConfigObject()->getDefaultAllowPublicLinks()
		) {
			$public_links = new ilCheckboxInputGUI($this->txt("activate_public_links"), "activate_public_links");
			$public_links->setInfo($this->txt("info_activate_public_links"));
			$this->form->addItem($public_links);
		}

		if ($this->getAdminConfigObject()->getConfigMaxFileSize()) {
			$max_file_size = new ilNumberInputGUI($this->txt("max_file_size"), "max_file_size");
			$max_file_size->setInfo($this->txt("info_max_file_size"));
			$max_file_size->setMaxLength(10);
			$max_file_size->setSize(10);
			$this->form->addItem($max_file_size);
		}
	}


	public function updatePluginSettings() {
		$this->getPluginObject()->setAllowPublicLinks($this->form->getInput("activate_public_links"));
		$this->getPluginObject()->setMaxFileSize($this->form->getInput("max_file_size"));
		$this->getPluginObject()->doUpdate();
	}


	/**
	 * @param $values
	 */
	public function getPluginSettingsValues(&$values) {
		$values["max_file_size"] = $this->getPluginObject()->getMaxFileSize();
		$values["activate_public_links"] = $this->getPluginObject()->getAllowPublicLinks();
	}
}

?>