<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Cloud/classes/class.ilCloudPluginInfoScreenGUI.php");

/**
 * Class ilDropboxInfoScreenGUI
 *
 * GUI Class to display Information.
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 * @extends ilCloudPluginGUI
 * @ingroup ModulesCloud
 */
class ilDropboxInfoScreenGUI extends ilCloudPluginInfoScreenGUI {

	/**
	 * show information screen
	 */
	public function getPluginInfo() {
		global $lng;
		$this->info->addSection("Dropbox");
		$this->info->addProperty($lng->txt("info"), $this->txt("create_info1"));
	}
}

?>
