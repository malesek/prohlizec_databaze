<?php


class MustacheRunner
{
    private $engine;

    public function __construct(){
        $this->engine = new Mustache_Engine(["entity_flags" => ENT_QUOTES]);
    }

    public function render($templateName, $context = []){
        return $this->engine->render($this->LoadTemplate($templateName), $context);
    }

    private function LoadTemplate($templateName){
        return file_get_contents(__DIR__ . "/../" . Config::TEMPLATESDIR . "/{$templateName}.mustache");
    }
}