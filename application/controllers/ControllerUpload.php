<?php

class ControllerUpload extends ONETIMEFILE_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helpers(['form', 'url', 'date']);
        $this->load->model('ModelUpload', 'model');
    }

    public function index()
    {
        $this->printPage();
    }

    /**
     * Print page.
     */
    private function printPage()
    {
        $this->load->view('templates/top', ['title' => 'Upload']);

        if ($this->model->banlistContains($this->input->server('REMOTE_ADDR'))) {
            $this->load->view('templates/error_view', ['errorMessage' => 'IP limits for uploading. Please, return after 1 hour.']);
        } else {
            $this->load->view('templates/upload');
        }
        $this->load->view('templates/bottom');
    }

    /**
     * Upload file to the server.
     */
    public function uploadFile()
    {
        if ($this->model->banlistContains($this->input->server('REMOTE_ADDR'))) {
            return $this->jsonResponse(['result' => '']);
        }

        $this->uploadingHandler();
    }

    /**
     * Make JSON response with specified data.
     *
     * @param array $response : Response as array.
     */
    protected function jsonResponse(array $response)
    {
        $this->output
            ->set_content_type('appplication/json')
            ->set_output(json_encode($response));
    }

    /**
     * Checking is user is banned.
     * @return boolean - True - is banned. False - if not.
     */
    public function checkBanlistContains()
    {
        $this->model->banlistContains($this->input->server('REMOTE_ADDR')) ?
            $this->jsonResponse(['result' => 'true']) :
            $this->jsonResponse(['result' => 'false']);
    }

    /**
     * Initialize upload settings.
     */
    private function initUploadSettings()
    {
        $config['allowed_types'] = '*';
        $config['upload_path'] = $this->processPath();
        $config['encrypt_name'] = true;
        $config['max_size'] = 1024 * 300;
        $config['max_filename'] = 255;
        $this->load->library('upload', $config);
    }

    /**
     * Handler path specified operations.
     *
     * @return string File path.
     * @throws Exception
     */
    private function processPath()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/../';
        $path .= '/uploads/' . date('Ymd') . '/';

        if (!file_exists($path) && !mkdir($path)) {
            throw new Exception('Error on creating new directory.');
        }

        return $path;
    }

    /**
     * Handler for file uploading.
     */
    private function uploadingHandler()
    {
        $this->initUploadSettings();
        $this->upload->do_upload();
        $this->model->setData($this->upload->data());
        $this->jsonResponse(['path' => $this->model->getPath()]);
    }
}