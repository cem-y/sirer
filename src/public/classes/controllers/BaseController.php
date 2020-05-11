<?php

namespace DareOne\controllers;
use DareOne\system\DareLogger;

class BaseController {

    protected $ci;
    protected $view;

    function __construct($ci)
    {
        $this->ci=$ci;
        $this->view=$ci['view'];
        $this->baseUrl=$ci["baseurl"];
        $this->logger=DareLogger::defaultLogger();
        $this->dbLogger=DareLogger::DBLogger();
    }

}