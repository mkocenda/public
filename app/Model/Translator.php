<?php

namespace App\Model;

use Nette\Database\Context;
use Ublaboo\DataGrid\Localization\SimpleTranslator;

class Translator
{

    private $language;
    private $db;
    private $lang;

    public function __construct(Context $db)
    {
        $this->db = $db;
        $this->setLang('cz');
        $this->loadLanguge();
    }

    /**
     * @param string $lang
     * @return void
     */
    public function setLang(string $lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return void
     */
    public function loadLanguge()
    {
        $this->language = $this->db->table('translate')
            ->where('lang', $this->lang)
            ->fetchPairs('lang_key', 'translate');
    }

    /**
     * @param string $key
     * @param string $translate
     * @return void
     */
    public function addLanguage(string $key, string $translate)
    {
        if (is_null($key) || ($key == '')) {
            return;
        }
        $data = array('lang_key' => $key, 'translate' => $translate, 'lang' => $this->lang);
        $this->db->table('translate')->insert($data);
    }

    /**
     * @param string|null $key
     * @return SimpleTranslator
     */
    public function translator(string $key = null)
    {
        $language = array();
        foreach ($this->language as $lang_key => $translate) {
            if ($key) {
                if ($key === $lang_key) {
                    $language[$lang_key] = $translate;
                } else {
                    $this->addLanguage($key, '');
                }
            } else {
                $language[$lang_key] = $translate;
            }
        }
        return new SimpleTranslator($language);
    }

    /**
     * @param string $key
     * @return mixed|string
     */
    public function translate(string $key)
    {
        foreach ($this->language as $lang_key => $translate) {
            if ($key) {
                if ($key === $lang_key) {
                    return $translate;
                }
            }
        }
        $this->addLanguage($key, '');
        return '';
    }
}