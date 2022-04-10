<?php

namespace App\Model;

use Ublaboo\DataGrid\Localization\SimpleTranslator;

class Translator
{

    public function __construct()
    {
        $this->translate();
    }

    public function translate()
    {
        $filename = __DIR__.'/../lang/cz.neon';
        $file = fopen($filename,'r');
        $content = fread($file, filesize($filename));
        $content = explode(PHP_EOL, $content);
        $language = array();
        foreach ($content as $line)
        {
            $langkey = substr($line, 0, strpos($line,':'));
            $language[$langkey] = substr($line, strpos($line,':')+1, strlen($line) - strpos($line,':'));
        }
        $translator = new SimpleTranslator($language);
        return $translator;
    }
}