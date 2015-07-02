<?php

/**
 * Model for working with requested files.
 */
class ModelRoute extends ONETIMEFILE_Model
{
    protected $localPath;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('download');
    }

    /**
     * Get abs path for requested file.
     * @param  string $url - Requested URL.
     * @return array - Array of needful data.
     */
    public function getData($url)
    {
        $getAbsPath =
            'SELECT filename, local_path
             FROM files
             WHERE short_url = ? OR url = ?';
        $params = [];
        $params[] = $url;
        $params[] = $url;
        $result = $this->database->request($getAbsPath, $params);
        $data = $result->row_array();

        if (empty($data['filename']) ||
            empty($data['local_path'])) {
            return null;
        }

        $this->localPath = $data['local_path'];
        return $data;
    }

    /**
     * Set local path of current file.
     * @param string $localPath - Local abs path to file.
     */
    public function setLocalPath($localPath)
    {
        $this->localPath = $localPath;
    }

    /**
     * Delete data of requested file from DB.
     */
    public function deleteFromDB()
    {
        $deleteSQL =
            "DELETE
             FROM files
             WHERE local_path = ?";
        $this->database->request($deleteSQL, [$this->localPath]);
    }

    /**
     * Delete local file.
     */
    public function deleteLocal()
    {
        unlink($this->localPath);
    }

    /**
     * Delete data in DB and corresponding file.
     */
    public function delete()
    {
        $this->deleteFromDB();
        $this->deleteLocal();
    }
}