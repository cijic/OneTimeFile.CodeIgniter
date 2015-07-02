<?php

/**
 * Class for working with DB for uploading process.
 */
class ModelUpload extends ONETIMEFILE_Model
{
    protected $data;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hash specified string.
     * @param  str $str - String to be hashed.
     * @return str - Hashed string.
     */
    protected function hash($str)
    {
        return hash('sha256', hash('sha256', $str));
    }

    /**
     * Generate unique URL for access to uploaded file.
     * @param  array $data - Data with file data.
     * @return str - Unique URL for access to uploaded file.
     */
    protected function generateUrl($filePath, $fileName)
    {
        if (empty($filePath) || empty($fileName)) {
            throw new Exception("No needful data.", 1);
        }

        return $this->hash($filePath) . '/' . $this->hash($fileName);
    }

    /**
     * Generate short URL for file downloading.
     * @param  string $url - URL which must be unuque and shorted.
     * @return string - Shorted URL.
     */
    protected function generateShortUrl($url)
    {
        return $this->hash($url);
    }

    /**
     * Drop limit for specified IP.
     * @param  string $ip - IP address
     */
    protected function dropLimit($ip)
    {
        $dropSQL =
            'DELETE
             FROM ban
             WHERE ip = ?';
        $this->database->request($dropSQL, [$ip]);
    }

    /**
     * Check if banlist contains specified IP.
     * @param  string $ip - IP address.
     * @return boolean - True - if contains. False - if not.
     */
    public function banlistContains($ip)
    {
        $checkSQL =
            'SELECT ip, time
             FROM ban
             WHERE ip = ?';
        $result = $this->database->request($checkSQL, [$ip]);
        $data = $result->row_array();

        if (empty($data['ip'])) {
            return false;
        }

        $timestamp = intval($data['time']);
        $timePassed = time() - $timestamp;
        $timeLimit = 60 * 60;    // 1 hour

        if ($timePassed > $timeLimit) {
            $this->dropLimit($ip);
            return false;
        }

        return true;
    }

    /**
     * Add specified IP to ban list.
     * @param string $ip - IP address.
     */
    protected function addToBanlist($ip)
    {
        $addToBanlistSQL =
            "INSERT INTO ban
             VALUES ('', ?, ?)";
        $this->database->request($addToBanlistSQL, [$ip, time()]);
    }

    /**
     * Save specified data to DB.
     * @param string $uniqueUrl - Long unique URL for downloading file.
     * @param string $shortUniqueUrl - Short unique URL for downloading file.
     * @param string $password Password for accessing it.
     */
    protected function saveFileData($uniqueUrl, $shortUniqueUrl, $password = '')
    {
        $saveSQL =
            "INSERT INTO files
             VALUES
              ('',
                ?,
                ?,
                ?,
                ?,
                ?,
                ?)";
        $params = [];
        $params[] = $uniqueUrl;
        $params[] = $shortUniqueUrl;
        $params[] = $this->data['full_path'];
        $params[] = $this->data['client_name'];
        $params[] = time();
        $params[] = $password; // TODO: Add support for premium user set password.
        $this->database->request($saveSQL, $params);
    }

    /**
     * Get download path for uploaded file.
     * @return str - Get full path for website.
     */
    public function getPath()
    {
        $filePath = (empty($this->data['file_path'])) ? '' : $this->data['file_path'];
        $fileName = (empty($this->data['file_name'])) ? '' : $this->data['file_name'];
        $result = '';

        try {
            $uniqueUrl = $this->generateUrl($filePath, $fileName);
            $shortUniqueUrl = $this->generateShortUrl($uniqueUrl);
            $result = $uniqueUrl . ' ' . $shortUniqueUrl;
            $this->saveFileData($uniqueUrl, $shortUniqueUrl);
            $this->addToBanlist($this->input->server('REMOTE_ADDR'));
        } catch (Exception $e) {
            echo $e->getMessage();
            return '';
        }

        return $result;
    }

    /**
     * Save data of uploaded file.
     * @param array $data - Array with needful data of uploaded file.
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}