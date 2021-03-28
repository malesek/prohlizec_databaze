<?php


abstract class BasePage
{
    protected MustacheRunner $m;
    protected string $title;
    protected array $extraHeaders = [];

    public function __construct(){
        $this->m = new MustacheRunner();
    }

    public function render(){
        try{
            $this->setUp();
            $html = $this->header();
            $html .= $this->body();
            $html .= $this->footer();
            echo $html;
            $this->wrapUp();
        }
        catch(RequestException $exception){
            $ePage = new ErrorPage($exception->getStatusCode());
            $ePage->render();
        }
        catch(Exception $exception){
            $ePage = new ErrorPage();
            $ePage->render();
        }
        exit;
    }

    protected function setUp() : void{

    }
    protected function header() : string{
        return $this->m->render("head", ["title" => $this->title, 'extraHeaders' => $this->extraHeaders]);
    }
    protected abstract function body() : string;

    protected function footer() : string{
        return $this->m->render("foot");
    }

    protected function wrapUp() : void{

    }
}