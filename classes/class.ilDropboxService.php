<?php

include_once("./Modules/Cloud/classes/class.ilCloudPluginService.php");
include_once('./Modules/Cloud/exceptions/class.ilCloudException.php');
include_once("./Modules/Cloud/classes/class.ilCloudUtil.php");

/**
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 *
 * @ingroup ModulesCloud
 */
class ilDropboxService extends ilCloudPluginService {

	/**
	 * @var \Dropbox\Config
	 */
	protected $config = null;
	/**
	 * @var \Dropbox\Client
	 */
	protected $service_object;


	public function __construct($service_name, $obj_id) {
		parent::__construct($service_name, $obj_id);
		require_once "./Customizing/global/plugins/Modules/Cloud/CloudHook/Dropbox/libs/Dropbox/lib/Dropbox/autoload.php";
	}


	/**
	 * @return \Dropbox\Config
	 */
	public function getAuth() {
		if (!$this->auth) {
			$app_info = new stdClass();
			$app_info->key = $this->getAdminConfigObject()->getAppKey();
			$app_info->secret = $this->getAdminConfigObject()->getAppSecret();
			$app_info->access_type = "AppFolder";
			$app_info->root = 'auto';
			$app_info = ilJsonUtil::encode($app_info);
			$info = \Dropbox\AppInfo::loadFromJson(json_decode($app_info, true));

			$client_identifier = $this->getAdminConfigObject()->getAppName();

			$token_store = new Dropbox\ArrayEntryStore($_SESSION, 'dropbox-auth-token');
			$redirect_uri = ILIAS_HTTP_PATH . '/Customizing/global/plugins/Modules/Cloud/CloudHook/Dropbox/redirect.php';
			$this->auth = new Dropbox\WebAuth($info, $client_identifier, $redirect_uri, $token_store);
		}
		return $this->auth;
	}


	/**
	 * @return \Dropbox\Client
	 */
	public function getServiceObject() {
		if (!$this->serviceObject) {
			$this->serviceObject = new \Dropbox\Client($this->getPluginObject()->getToken(), $this->getAdminConfigObject()->getAppName());
		}
		return $this->serviceObject;
	}


	/**
	 * @param string $callback_url
	 * @throws ilCloudPluginConfigException
	 */
	public function authService($callback_url) {
		try {
			$auth_url = $this->getAuth($callback_url)->start(htmlspecialchars_decode($callback_url));
			header("Location: $auth_url");
		} catch (Exception $e) {
			throw new ilCloudPluginConfigException(0, $e->getMessage());
		}
	}


	public function afterAuthService() {
		try {
			list($access_token, $user_id, $url_state) = $this->getAuth()->finish($_GET);
			$this->getPluginObject()->setToken($access_token);
			$this->getPluginObject()->doUpdate();
			$this->createFolder($this->getPluginObject()->getCloudModulObject()->getRootFolder());
			return true;
		} catch (Dropbox\WebAuthException_NotApproved $ex) {
			//User did not approve app.
			return false;
		} catch (Dropbox\WebAuthException_BadRequest $ex) {
			error_log("/dropbox-auth-finish: bad request: " . $ex->getMessage());
		} catch (Dropbox\WebAuthException_BadState $ex) {
			// Auth session expired.  Restart the auth process.
			header('Location: /dropbox-auth-start');
		} catch (Dropbox\WebAuthException_Csrf $ex) {
			error_log("/dropbox-auth-finish: CSRF mismatch: " . $ex->getMessage());
		} catch (Dropbox\WebAuthException_Provider $ex) {
			error_log("/dropbox-auth-finish: error redirect from Dropbox: " . $ex->getMessage());
		} catch (Dropbox\Exception $ex) {
			error_log("/dropbox-auth-finish: error communicating with Dropbox API: " . $ex->getMessage());
		}
	}


	public function getRootId($root_path) {

		$result = $this->getServiceObject()->getMetadata($root_path);
		if (!$result) {
			throw new ilCloudException(ilCloudException::FOLDER_NOT_EXISTING_ON_SERVICE, $root_path);
		}
		return "id_" . sha1("/");
	}


	public function addToFileTree(ilCloudFileTree &$file_tree, $rel_parent_folder = "/") {
		try {
			$parent_folder = ilCloudUtil::joinPaths($file_tree->getRootPath(), $rel_parent_folder);
			$folder = $this->getServiceObject()->getMetadataWithChildren($parent_folder);

			if (!($folder["is_dir"])) {
				throw new ilCloudException(ilCloudException::FOLDER_NOT_EXISTING_ON_SERVICE, $parent_folder);
			}
			foreach ($folder["contents"] as $item) {
				if ($item["path"] != null) {
					$rel_path = substr($item["path"], strlen($file_tree->getRootPath()));
					$id = "id_" . sha1($rel_path);
					$file_tree->addNode($rel_path, $id, $item["is_dir"], strtotime($item["modified"]), $item["size"]);
				}
			}
			$file_tree->setLoadingOfFolderComplete($rel_parent_folder);
		} catch (Exception $e) {
			$this->getPluginObject()->getCloudModulObject()->setAuthComplete(false);
			$this->getPluginObject()->getCloudModulObject()->update();
			throw $e;
		}
	}


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
			header('Content-Disposition: attachment; filename=' . str_replace(' ', '_', basename($path)));
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
	 * @return mixed
	 * @throws \Dropbox\Exception_BadResponseCode
	 * @throws \Dropbox\Exception_InvalidAccessToken
	 * @throws \Dropbox\Exception_RetryLater
	 * @throws \Dropbox\Exception_ServerError
	 */
	public function getLink($path = null, $file_tree = null) {
		$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
		list($url, $expires) = $this->getServiceObject()->createTemporaryDirectLink($path);
		return $url;
	}


	/**
	 * @param null $path
	 * @param ilCloudFileTree|null $file_tree
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
				return $this->getServiceObject()->createFolder($path);
			} else {
				return false;
			}
		} catch (Exception $e) {
			if ($e->getCode() == 403) {
				throw new ilCloudException(ilCloudException::FOLDER_ALREADY_EXISTING_ON_SERVICE, $path);
			}
			if (strpos($e->getMessage(), 'The following characters are not allowed') !== false) {
				throw new ilCloudException(ilCloudException::FOLDER_CREATION_FAILED, $this->getPluginHookObject()->txt("invalid_character"));
			} else {
				throw $e;
			}
		}
	}


	/**
	 * @param $file
	 * @param $name
	 * @param string $path
	 * @param null $file_tree
	 * @return mixed
	 */
	public function putFile($file, $name, $path = '', $file_tree = null) {
		$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
		return $this->getServiceObject()->uploadFile($path . "/" . $name, \Dropbox\WriteMode::add(), fopen($file, "rb"));
	}


	/**
	 * @param null $path
	 * @param ilCloudFileTree|null $file_tree
	 * @return mixed
	 * @throws \Dropbox\Exception_BadResponseCode
	 * @throws \Dropbox\Exception_InvalidAccessToken
	 * @throws \Dropbox\Exception_RetryLater
	 * @throws \Dropbox\Exception_ServerError
	 */
	public function deleteItem($path = null, ilCloudFileTree $file_tree = null) {
		$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
		return $this->getServiceObject()->delete($path);
	}
}

?>