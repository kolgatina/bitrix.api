<?

namespace Api;

class FormContact extends Forms
{
    public $rules = [
        'short' => ['bool'],
        'sex' => [['required_without' => ['short']], 'sex'],
        'name' => ['required'],
        'last_name' => [['required_without' => ['short']]],
        'phone' => ['required', 'phone'],
        'email' => [['required_without' => ['short']], 'email'],
        'disclaimer' => ['required', 'bool'],
        'feedback' => ['bool'],
        'source' => []
    ];

    public $formName = 'Свяжитесь со мной';

    public $highloadblockName = 'FormContact';

    public $isSendEmail = true;

    public $arSendTo = ['example@mail.ru'];

    public function handle() {
        if ($this->highloadblockName) {
            return \Helper::addDataToHighloadblock($this->params, $this->highloadblockName);
        }
    }
}