<?php
/**
 * Utilities for the update process
 */
class OUpdate {
	private $colors          = null;
	private $base_dir        = null;
	//private $repo_url        = 'https://raw.githubusercontent.com/igorosabel/Osumi-Framework/master/';
	private $repo_url        = 'https://osumi.es/';
	private $version_file    = null;
	private $current_version = null;
	private $repo_version    = null;
	private $version_check   = null;
	private $new_updates     = [];

	/**
	 * Loads on start up current version and repo version and checks both
	 *
	 * @return void
	 */
	function __construct() {
		global $core;
		$this->colors          = new OColors();
		$this->base_dir        = $core->config->getDir('base');
		$this->current_version = trim( OTools::getVersion() );
		$this->repo_version    = $this->getVersion();
		$this->version_check   = version_compare($this->current_version, $this->repo_version);
	}

	/**
	 * Get currently installed version
	 *
	 * @return string Current version number
	 */
	public function getCurrentVersion() {
		return $this->current_version;
	}

	/**
	 * Get repository version
	 *
	 * @return string Repository version number
	 */
	public function getRepoVersion() {
		return $this->repo_version;
	}

	/**
	 * Get version check
	 *
	 * @return integer Get version check (-1 to be updated 0 current 1 newer)
	 */
	public function getVersionCheck() {
		return $this->version_check;
	}

	/**
	 * Get file of version updates
	 *
	 * @return array Available updates list array
	 */
	private function getVersionFile() {
		if (is_null($this->version_file)) {
			$this->version_file = json_decode( file_get_contents($this->repo_url.'ofw/base/version.json'), true );
		}
		return $this->version_file;
	}

	/**
	 * Get current version from the repository
	 *
	 * @return string Current version number (eg 5.0.0)
	 */
	private function getVersion() {
		$version = $this->getVersionFile();
		return $version['version'];
	}

	/**
	 * Perform the update check
	 *
	 * @return array Array of "to be updated" versions. Includes version number, message and array of files with their status
	 */
	public function doUpdateCheck() {
		$version = $this->getVersionFile();
		$updates = $version['updates'];

		$to_be_updated = [];
		foreach ($updates as $update_version => $update) {
			if (version_compare($this->current_version, $update_version)==-1) {
				$to_be_updated[$update_version] = [
					'message' => $update['message'],
					'files' => []
				];
			}
		}
		asort($to_be_updated);

		foreach (array_keys($to_be_updated) as $version) {
			if (array_key_exists('deletes', $updates[$version])) {
				foreach ($updates[$version]['deletes'] as $delete) {
					$local_delete = $this->base_dir.$delete;
					$status = 2; // delete
					if (!file_exists($local_delete)) {
						$status = 3; // delete not found
					}
					array_push($to_be_updated[$version]['files'], ['file' => $local_delete, 'status' => $status]);
				}
			}
			if (array_key_exists('files', $updates[$version])) {
				foreach ($updates[$version]['files'] as $file) {
					$local_file = $this->base_dir.$file;
					$status = 0; // new
					if (file_exists($local_file)) {
						$status = 1; // update
					}
					array_push($to_be_updated[$version]['files'], ['file' => $local_file, 'status' => $status]);
				}
			}
		}

		$this->new_updates = $to_be_updated;
		return $this->new_updates;
	}

	/**
	 * Show information about available updates
	 *
	 * @return string Prints information about updates
	 */
	public function showUpdates() {
		$to_be_updated = $this->doUpdateCheck();
		echo "\n";

		foreach ($to_be_updated as $version => $update) {
			echo str_pad("==[ ".$update['message']." ]", 110, "=")."\n\n";
			foreach ($update['files'] as $file) {
				echo "    ";
				switch ($file['status']){
					case 0: {
						echo "[".$this->colors->getColoredString("NEW   ", "light_green")."]";
					}
					break;
					case 1: {
						echo "[".$this->colors->getColoredString("UPDATE", "light_blue")."]";
					}
					break;
					case 2: {
						echo "[".$this->colors->getColoredString("DELETE", "light_red")."]";
					}
					break;
					case 3: {
						echo "[".$this->colors->getColoredString("DELETE (NOT FOUND)", "light_purple")."]";
					}
					break;
				}
				echo " - ".str_ireplace($this->base_dir, '', $file['file'])."\n";
			}
			echo "\n".str_pad('', 109, '=')."\n\n";
		}
	}
}