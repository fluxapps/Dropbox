<?php

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

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
	 * @var \Kunnu\Dropbox\Dropbox
	 */
	protected $serviceObject;
	/**
	 * @var \Kunnu\Dropbox\DropboxApp
	 */
	protected $auth;


	public function __construct($service_name, $obj_id) {
		parent::__construct($service_name, $obj_id);
	}


	/**
	 * @return \Kunnu\Dropbox\DropboxApp
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
	 * @return \Kunnu\Dropbox\Dropbox
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
		global $DIC;
		try {
			$data = $this->getServiceObject()->getOAuth2Client()->getAccessToken($DIC->http()
			                                                                         ->request()
			                                                                         ->getQueryParams()["code"], $this->getRedirectURI());
			$this->getPluginObject()->setToken($data["access_token"]);
			$this->getPluginObject()->doUpdate();
			$this->createFolder($this->getPluginObject()->getCloudModulObject()->getRootFolder());

			return true;
		} catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $ex) {
			return false;
		}
	}


	/**
	 * @param $root_path
	 *
	 * @return string
	 * @throws \ilCloudException
	 */
	public function getRootId($root_path) {
		$result = $this->getServiceObject()->getMetadata($root_path);
		if (!$result) {
			throw new ilCloudException(ilCloudException::FOLDER_NOT_EXISTING_ON_SERVICE, $root_path);
		}

		return "id_" . sha1("/");
	}


	/**
	 * @param \ilCloudFileTree $file_tree
	 * @param string           $rel_parent_folder
	 *
	 * @throws \Exception
	 */
	public function addToFileTree(ilCloudFileTree $file_tree, $rel_parent_folder = "/") {
		try {
			$dropbox = $this->getServiceObject();
			$parent_folder = ilCloudUtil::joinPaths($file_tree->getRootPath(), $rel_parent_folder);
			$folder = $dropbox->listFolder($parent_folder);
			/**
			 * @var $item \Kunnu\Dropbox\Models\FileMetadata|\Kunnu\Dropbox\Models\FolderMetadata
			 */
			foreach ($folder->getItems() as $item) {
				switch (true) {
					case ($item instanceof \Kunnu\Dropbox\Models\FolderMetadata):
						/**
						 * @var $item \Kunnu\Dropbox\Models\FolderMetadata
						 */
						$rel_path = substr($item->getPathLower(), strlen($file_tree->getRootPath()));
						$id = "id_" . sha1($rel_path);
						$file_tree->addNode($rel_path, $id, true);
						break;
					case ($item instanceof \Kunnu\Dropbox\Models\FileMetadata):
						/**
						 * @var $item \Kunnu\Dropbox\Models\FileMetadata
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
	 * @param null                  $path
	 * @param \ilCloudFileTree|null $file_tree
	 */
	public function getFile($path = null, ilCloudFileTree $file_tree = null) {
		if ($this->getPluginObject()->getAllowPublicLinks()) {
			$link = $this->getLink($path, $file_tree);
			header('Location:' . $link);
		} else {
			$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
			$temp = tmpfile();
			$meta = $this->getServiceObject()->getFile($path, $temp);
			header("Content-type: " . $meta["mime_type"]);
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename='
			       . str_replace(' ', '_', basename($path)));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . $meta["bytes"]);
			ob_clean();
			flush();
			fseek($temp, 0);
			echo fread($temp, $meta["bytes"]);
			fclose($temp);
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
	 * @param \ilCloudFileTree $file_tree
	 *
	 * @return mixed
	 */
	public function putFile($file, $name, $path = '', $file_tree = null) {
		$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
		$path = ($path != '' ? $path . "/" : $path);

		$DropboxFile = $this->getServiceObject()->makeDropboxFile($file);
		try {
			$this->getServiceObject()->upload($DropboxFile, $path . $name);
		} catch (Exception $e) {
			return false;
		}

		return true;
	}


	/**
	 * @param null                  $path
	 * @param \ilCloudFileTree|null $file_tree
	 *
	 * @return \Kunnu\Dropbox\Models\DeletedMetadata|\Kunnu\Dropbox\Models\FileMetadata|\Kunnu\Dropbox\Models\FolderMetadata
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
