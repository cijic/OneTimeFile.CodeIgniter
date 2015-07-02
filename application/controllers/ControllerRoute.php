<?php

/**
 * Class for routing downloadable files.
 */
class ControllerRoute extends ONETIMEFILE_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ModelRoute', 'model');
    }

    /**
     * Routing downloadable files.
     * @param  string $url - URL for download.
     */
    public function route($url)
    {
        $data = $this->model->getData($url);
        $this->uploadToUser($data);
    }

    /**
     * Upload requested file if was found.
     * @param  array $data - Needful data.
     */
    protected function uploadToUser($data)
    {
        if (empty($data)) {
            $this->load->helper('url');
            redirect('/404');
        }

        $filename = $data['filename'];
        $localPath = $data['local_path'];

        if (file_exists($localPath)) {
            $this->forceDownload($localPath, $filename);
            $this->model->delete();
        }
    }

    /**
     * Method for force upload file to user.
     * @param  string $localPath Local path of file.
     * @param  string $filename  Filename, which will be saved on user side.
     */
    protected function forceDownload($localPath, $filename)
    {
        if (file_exists($localPath)) {
            header('Content-Description: localPath Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($filename));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($localPath));
            readfile($localPath);
        }
    }

    /**
     * Method for CLI mode for remove file, which are limited: old, and corresponding row in DB.
     */
    public function cliRemoveLimitated()
    {
        if (!$this->input->is_cli_request()) {
            die('Not CLI request.');
        }

        $timeLimit = 60 * 60 * 12; // 12 hour
        $selectSQL =
            'SELECT *
             FROM files';
        $result = $this->database->request($selectSQL);

        while ($data = $result->unbuffered_row()) {
            $timeUpload = $data->time;
            $timePassed = time() - intval($timeUpload);

            if ($timePassed > $timeLimit) {
                $this->model->setLocalPath($data->local_path);
                $this->model->delete();
                echo 'Deleted: ' . $data->local_path;
            }
        }
    }
}