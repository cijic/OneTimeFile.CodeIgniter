<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class ONETIMEFILE_Model
 *
 * Base class for models.
 */
class ONETIMEFILE_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ModelDatabase', 'database');
    }
}