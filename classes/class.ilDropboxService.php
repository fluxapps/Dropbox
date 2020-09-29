<?php

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Models\FolderMetadata;
use Kunnu\Dropbox\Models\FileMetadata;
use Kunnu\Dropbox\Models\DeletedMetadata;
use Kunnu\Dropbox\Exceptions\DropboxClientException;

require_once('./Customizing/global/plugins/Modules/Cloud/CloudHook/Dropbox/vendor/autoload.php');

/**
 *
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 *
 * @ingroup ModulesCloud
 */
class ilDropboxService extends ilCloudPluginService {

	const REDIRECT_PHP = '/Customizing/global/plugins/Modules/Cloud/CloudHook/Dropbox/redirect.php';
	/**
	 * @var Dropbox
	 */
	protected $serviceObject;
	/**
	 * @var DropboxApp
	 */
	protected $auth;


	public function __construct($service_name, $obj_id) {
		parent::__construct($service_name, $obj_id);
	}


	/**
	 * @return DropboxApp
	 */
	public function getAuth() {
		if (!$this->auth) {
			$clientId = trim($this->getAdminConfigObject()->getAppKey());
			$clientSecret = trim($this->getAdminConfigObject()->getAppSecret());
			$accessToken = $this->getPluginObject()->getToken();
			$this->auth = new DropboxApp($clientId, $clientSecret, $accessToken);
		}

		return $this->auth;
	}


	/**
	 * @return Dropbox
	 */
	public function getServiceObject() {
		if (!$this->serviceObject) {
			$this->serviceObject = new Dropbox($this->getAuth());
		}

		return $this->serviceObject;
	}


	/**
	 * @param string $callback_url
	 *
	 * @throws ilCloudPluginConfigException
	 */
	public function authService($callback_url = "") {
		try {
			$authHelper = $this->getServiceObject()->getAuthHelper();
			$redirect_uri = $this->getRedirectURI();
			$OAuth2Client = $authHelper->getOAuth2Client();
			$auth_url = $OAuth2Client->getAuthorizationUrl($redirect_uri, urlencode($callback_url));

			header("Location: $auth_url");
		} catch (Exception $e) {
			throw new ilCloudPluginConfigException(0, $e->getMessage());
		}
	}


	public function afterAuthService() {
		try {
			$data = $this->getServiceObject()->getOAuth2Client()->getAccessToken($_GET["code"], $this->getRedirectURI());
			$this->getPluginObject()->setToken($data["access_token"]);
			$this->getPluginObject()->doUpdate();
			$this->getServiceObject()->setAccessToken($data['access_token']);
			$this->createFolder($this->getPluginObject()->getCloudModulObject()->getRootFolder());

			return true;
		} catch (DropboxClientException $ex) {
			return false;
		}
	}


	/**
	 * @param $root_path
	 *
	 * @return string
	 * @throws ilCloudException
	 */
	public function getRootId($root_path) {
		$result = $this->getServiceObject()->getMetadata($root_path);
		if (!$result) {
			throw new ilCloudException(ilCloudException::FOLDER_NOT_EXISTING_ON_SERVICE, $root_path);
		}

		return "id_" . sha1("/");
	}


	/**
	 * @param ilCloudFileTree $file_tree
	 * @param string          $rel_parent_folder
	 * @throws Exception
	 */
	public function addToFileTree(ilCloudFileTree $file_tree, $rel_parent_folder = "/") {
		try {
			$dropbox = $this->getServiceObject();
			$parent_folder = ilCloudUtil::joinPaths($file_tree->getRootPath(), $rel_parent_folder);
			$folder = $dropbox->listFolder($parent_folder);
			/**
			 * @var $item FileMetadata|FolderMetadata
			 */
			foreach ($folder->getItems() as $item) {
				switch (true) {
					case ($item instanceof FolderMetadata):
						/**
						 * @var $item FolderMetadata
						 */
						$rel_path = substr($item->getPathLower(), strlen($file_tree->getRootPath()));
						$id = "id_" . sha1($rel_path);
						$file_tree->addNode($rel_path, $id, true);
						break;
					case ($item instanceof FileMetadata):
						/**
						 * @var $item FileMetadata
						 */
						$rel_path = substr($item->getPathLower(), strlen($file_tree->getRootPath()));
						$id = "id_" . sha1($rel_path);
						$file_tree->addNode($rel_path, $id, false, strtotime($item->getClientModified()), $item->getSize());
						break;
				}
			}
			$file_tree->setLoadingOfFolderComplete($rel_parent_folder);
		} catch (Exception $e) {
			$this->getPluginObject()->getCloudModulObject()->setAuthComplete(false);
			$this->getPluginObject()->getCloudModulObject()->update();
			throw $e;
		}
	}

    /**
     * @param null                 $path
     * @param ilCloudFileTree|null $file_tree
     * @throws DropboxClientException
     */
	public function getFile($path = null, ilCloudFileTree $file_tree = null) {
		if ($this->getPluginObject()->getAllowPublicLinks()) {
			$link = $this->getLink($path, $file_tree);
			header('Location:' . $link);
		} else {
			$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
			$meta = $this->getServiceObject()->download($path);
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename='
			       . str_replace(' ', '_', basename($path)));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . $meta->getMetadata()->getSize());
			echo $meta->getContents();
			exit;
		}
	}


	/**
	 * @param null $path
	 * @param null $file_tree
	 *
	 * @return string
	 */
	public function getLink($path = null, $file_tree = null) {
		$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
		$link = $this->getServiceObject()->getTemporaryLink($path);

		return $link->getLink();
	}


	/**
	 * @param null                 $path
	 * @param ilCloudFileTree|null $file_tree
	 *
	 * @return array|bool|null
	 * @throws Exception
	 * @throws ilCloudException
	 */
	public function createFolder($path = null, ilCloudFileTree $file_tree = null) {
		if ($file_tree) {
			$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
		}
		try {
			if ($path && $path != "/") {
				$this->getServiceObject()->createFolder($path);

				return true;
			} else {
				return false;
			}
		} catch (Exception $e) {
			if ($e->getCode() == 403) {
				throw new ilCloudException(ilCloudException::FOLDER_ALREADY_EXISTING_ON_SERVICE, $path);
			}
			if (strpos($e->getMessage(), 'The following characters are not allowed') !== false) {
				throw new ilCloudException(ilCloudException::FOLDER_CREATION_FAILED, $this->getPluginHookObject()
				                                                                          ->txt("invalid_character"));
			} else {
				throw $e;
			}
		}
	}


	/**
	 * @param                  $file
	 * @param                  $name
	 * @param string           $path
	 * @param ilCloudFileTree  $file_tree
	 * @return mixed
	 */
	public function putFile($file, $name, $path = '', $file_tree = null) {
		$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
		$path = (($path != '' && $path != '/') ? $path . "/" : $path);

		$DropboxFile = $this->getServiceObject()->makeDropboxFile($file);
		try {
			$this->getServiceObject()->upload($DropboxFile, $path . $name);
		} catch (Exception $e) {
			return false;
		}

		return true;
	}


	/**
	 * @param null                 $path
	 * @param ilCloudFileTree|null $file_tree
	 * @return DeletedMetadata|FileMetadata|FolderMetadata
	 */
	public function deleteItem($path = null, ilCloudFileTree $file_tree = null) {
		$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);

		return $this->getServiceObject()->delete($path);
	}


	public function isCaseSensitive() {
		return true;
	}


	/**
	 * @return string
	 */
	protected function getRedirectURI() {
		return ILIAS_HTTP_PATH . self::REDIRECT_PHP;
	}
}
