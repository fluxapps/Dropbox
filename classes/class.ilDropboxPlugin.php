<?php

include_once("./Modules/Cloud/classes/class.ilCloudHookPlugin.php");

/**
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 *
 * @ingroup ModulesCloud
 */
class ilDropboxPlugin extends ilCloudHookPlugin {

	/**
	 * @return string
	 */
	public function getPluginName() {
		return "Dropbox";
	}


}
