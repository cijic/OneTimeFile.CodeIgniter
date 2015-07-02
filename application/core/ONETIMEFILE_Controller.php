<?php

/**
 * Class ONETIMEFILE_Controller
 *
 * Base class for controllers.
 */
class ONETIMEFILE_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Loading CI Language class.
     *
     * @param string $lang - Language in idiom form.
     */
    protected function loadLang($lang = '')
    {
        if (!empty($lang)) {
            $idiom = $lang;
        } else {
            $idiom = $this->detectLanguage();
        }

        if (empty($idiom)) {
            $idiom = $this->config->item('language');
        }

        $this->lang->load('views_lang', $idiom);
    }

    /**
     * Detect language by accept language.
     *
     * @return string Detected language.
     */
    private function detectLanguage()
    {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        switch (mb_strtolower($lang)) {
            case 'ru':
                return 'russian';

            default:
                return 'english';
        }
    }
}