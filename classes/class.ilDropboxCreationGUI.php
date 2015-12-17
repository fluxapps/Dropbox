<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("./Modules/Cloud/classes/class.ilCloudPluginCreationGUI.php");

/**
 * Class ilCloudPluginSettingsGUI
 *
 * Base class for the settings that need to be set during creation (like base folder). Needs to be overwritten if the plugin needs custom settings.
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesCloud
 */

class ilDropboxCreationGUI extends ilCloudPluginCreationGUI
{

    public function initPluginCreationFormSection(ilRadioOption $option)
    {
        $option->setInfo($this->txt("create_info1")."</br>". $this->txt("create_info2").$this->getAdminConfigObject()->getAppName().$this->txt("create_info3"));
        $sub_selection = new ilRadioGroupInputGUI($this->txt("base_folder"), "dropbox_base_folder");
        $sub_selection->setRequired(true);


        $option_default = new ilRadioOption($this->txt("default_base_folder"), "dropbox_default_base_folder");

        $option_custom = new ilRadioOption($this->txt("custom_base_folder"), "dropbox_custom_folder_selection");
        $custom_base_folder_input = new ilTextInputGUI($this->txt("custom_base_folder_input"), "dropbox_custom_base_folder_input");
        $custom_base_folder_input->setRequired(true);
        $custom_base_folder_input->setInfo($this->txt("custom_base_folder_input_info"));
        $option_custom->addSubItem($custom_base_folder_input);

        $sub_selection->addOption($option_default);
        $sub_selection->addOption($option_custom);

        $sub_selection->setValue("dropbox_default_base_folder");

        $option->addSubItem($sub_selection);
		
		$sub_selection2 = new ilCheckboxInputGUI($this->txt("online"), "online");
		
		if($this->getAdminConfigObject()->getValue('config_default_online')){
			$sub_selection2->setChecked(true);
		}
        $option->addSubItem($sub_selection2);
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param ilObjCloud $obj
     */
    function afterSavePluginCreation(ilObjCloud &$obj, ilPropertyFormGUI $form)
    {
        if($form->getInput("base_folder") == "dropbox_default_base_folder")
        {
            $obj->setRootFolder($obj->getTitle());
        }
        else
        {
            $obj->setRootFolder($form->getInput("dropbox_custom_base_folder_input"));
        }
		if($form->getInput("online")=="1"){
			$obj->setOnline(true);
			$obj->doUpdate();
		}
    }
}

?>