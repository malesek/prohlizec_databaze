<?php


class ErrorPage extends BasePage
{
    private int $httpErrorCode;

    public function __construct($httpErrorCode = 500){
        $this->httpErrorCode = $httpErrorCode;
        $this->title = "Error $this->httpErrorCode";
        parent::__construct();
    }

    protected function setUp():void{
        parent::setUp();
        http_response_code($this->httpErrorCode);
    }

    protected function body():string{
        switch ($this->httpErrorCode){
            case 400:
                return "<h1>Error 400: Bad request</h1>";
            case 404:
                return "<h1>Error 404: Not Found</h1>";
            case 500:
                return "<h1>Internal server error</h1>";
            default:
                return "<h1>Error {$this->httpErrorCode} encountered</h1>";
        }
    }
}
/*$error = new ErrorPage();
$error->render();*/