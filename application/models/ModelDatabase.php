<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ModelDatabase extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Request to database;
     *
     * @param string $sql - SQL query.
     * @param array $params - Parameters.
     * @throws Exception - Exception on SQL error.
     * @return query result.
     */
    public function request($sql, $params = [])
    {
        $query = $this->db->query($sql, $params);

        if ($query) {
            return $query;
        }

        throw new Exception("SQL error: " . print_r($query->error()));
    }

}