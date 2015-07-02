<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Controller404 extends ONETIMEFILE_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->printPage();
        set_status_header(404);
    }

    private function printPage()
    {
        $this->loadLang();
        $this->load->view('templates/top', ['title' => 'Page not found.']);
        $this->load->view('templates/404_view');
        $this->load->view('templates/bottom');
    }
}