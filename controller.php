<?php

defined('_JEXEC') or die('Restricted access');

class ZikulaAuthController extends JControllerLegacy
{
    private $originalCode = "test123";

    public function __construct($config)
    {
        $jinput = JFactory::getApplication()->input;
        $this->username = $jinput->get('username', '', 'STRING');
        $this->password = $jinput->get('password', '', 'STRING');
        $this->code = $jinput->get('code', '', 'STRING');
        $this->checkRequiredParameters();

        parent::__construct($config);
    }

    private function checkRequiredParameters()
    {
        if (!is_string($this->username) || !is_string($this->password) || !is_string($this->code) || $this->username == '' || $this->password == '' || !$this->secureCompare($this->code, $this->originalCode)) {
            header('HTTP/1.1 400 Bad Request', true, 400);
            jexit();
        }
    }

    public function execute()
    {
        jimport( 'joomla.user.authentication');
        $auth = & JAuthentication::getInstance();
        $credentials = array(
            'username' => $this->username,
            'password' => $this->password
        );
        $options = array();
        $response = $auth->authenticate($credentials, $options);

        if ($response->status != JAuthentication::STATUS_SUCCESS) {
            header('HTTP/1.1 401 Unauthorized', true, 401);
            echo "Invalid credentials";
        } else {
            header('Content-Type: application/json', true, 200);
            echo json_encode(array(
                'username' => $response->username,
                'email' => $response->email,
                'timezone' => $response->timezone,
                'fullname' => $response->fullname,
                'language' => $response->language
            ));
        }
        jexit();
    }

    private function secureCompare($a, $b)
    {
        $diff = strlen($a) ^ strlen($b);
        for($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
    }
}