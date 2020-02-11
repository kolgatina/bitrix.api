<?

namespace Api;

class FormConsult extends Forms
{
    public $rules = [
        'name' => ['required'],
        'last_name' => ['required'],
        'phone' => ['required', 'phone'],
        'date' => ['required', \Helper::DATE_FORMAT],
        'theme' => ['multiple', \Helper::THEME_CONSULT],
        'source' => []
    ];

    public $formName = 'Запись на консультацию';

    public $highloadblockName = 'FormConsult';

    public $isSendEmail = true;

    public $arSendTo = ['example@mail.ru'];

    public function handle() {
        if ($this->highloadblockName) {
            return \Helper::addDataToHighloadblock($this->params, $this->highloadblockName);
        }
    }
}