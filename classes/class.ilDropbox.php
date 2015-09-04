<?php

include_once("./Modules/Cloud/classes/class.ilCloudPlugin.php");

/**
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 *
 * @ingroup ModulesCloud
 */
class ilDropbox extends ilCloudPlugin
{
    /**
     * @var string
     */
    protected $token = "";
    /**
     * @var bool
     */
    protected $allow_public_links = false;

    /**
     * @var int
     */
    protected $max_file_size = 0;

    /**
     * @param string $value
     */
    public function setToken($value)
    {
        $this->token = $value;
    }

    /**
     * @return string token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param bool $value
     */
    public function setAllowPublicLinks($value)
    {
        $this->allow_public_links = $value;
    }

    /**
     * @return bool
     */
    public function getAllowPublicLinks()
    {
        if($this->getAdminConfigObject()->getDefaultAllowPublicLinks())
        {
            if (!$this->getAdminConfigObject()->getDefaultAllowPublicLinksConfigAllowPublicLinks())
            {
                return true;
            }
            return $this->allow_public_links;
        }
        return false;
    }

    /**
     * @param int $max_file_size
     */
    public function setMaxFileSize($max_file_size)
    {
        $this->max_file_size = $max_file_size;
    }

    /**
     * @return int
     */
    public function getMaxFileSize()
    {
        $size = 0;
        if (!$this->getAdminConfigObject()->getConfigMaxFileSize() || !$this->max_file_size > 0)
        {
            $size = $this->getAdminConfigObject()->getDefaultMaxFileSize();
        }
        else
        {
            $size = $this->max_file_size;
        }
        if($size > 150)
        {
            return 150;
        }
        else
        {
            return $size;
        }

    }

    /**
     * Create object
     */
    function create()
    {
        global $ilDB, $ilUser;

        $ilDB->manipulate("INSERT INTO ". $this->getTableName(). "
        		(id, public_link, token, max_file_size)
        		VALUES (" . $ilDB->quote($this->getObjId(), "integer") . "
        		," . $ilDB->quote($this->getAllowPublicLinks(), "boolean") . "
        		," . $ilDB->quote($this->getToken(), "text") . "
        		," . $ilDB->quote($this->getMaxFileSize(), "text") . "
        		)");
    }

    /**
     * Read data from db
     */
    function read()
    {
        global $ilDB;

        $set = $ilDB->query("SELECT * FROM " . $this->getTableName() . " WHERE id = " . $ilDB->quote($this->getObjId(), "integer"));
        $rec = $ilDB->fetchAssoc($set);
        if ($rec == null)
        {
            return false;
        } else
        {
            $this->setAllowPublicLinks($rec["public_link"]);
            $this->setToken($rec["token"]);
            $this->setMaxFileSize($rec["max_file_size"]);
        }
        return true;
    }

    /**
     * Update data
     */
    function doUpdate()
    {
        global $ilDB;

        $ilDB->manipulate("UPDATE " . $this->getTableName() . " SET
        		id = " . $ilDB->quote($this->getObjId(), "integer") . "," . "
        		public_link = " . $ilDB->quote($this->getAllowPublicLinks(), "boolean") . "," . "
        		token = " . $ilDB->quote($this->getToken(), "text") . "," . "
        		max_file_size = " . $ilDB->quote($this->getMaxFileSize(), "text") .
            " WHERE id = " . $ilDB->quote($this->getObjId(), "integer")
        );
    }

    /**
     * Delete
     */
    function doDelete()
    {
        global $ilDB;

        $ilDB->manipulate("DELETE FROM cld_cldh_cdpx_props WHERE " . " id = " . $ilDB->quote($this->getObjId(), "integer") );

    }
}
?>