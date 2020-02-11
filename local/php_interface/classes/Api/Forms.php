<?

namespace Api;

abstract class Forms
{
    /**
     * Rules for params
     * @var array
     */
    public $rules = [];

    /**
     * Form params
     * @var array
     */
    public $params = [];

    /**
     * Form id. Name of handler class is "Form"+$formId
     * @var string
     */
    public $formId = '';

    /**
     * Form name
     * @var string
     */
    public $formName = '';

    /**
     * Name of highloadlock for logging form's data
     * @var int
     */
    public $highloadblockName;

    /**
     * Send email or not
     * @var bool
     */
    public $isSendEmail = false;

    /**
     * Send email to
     * @var array
     */
    public $arSendTo = [];

    /**
     * Key types&names in params array
     * Used for email template
     * @var array
     */
    protected $arKeyName = [
        'short' => [
            'type' => 'bool',
            'name' => 'Короткая форма'
        ],
        'sex' => [
            'type' => 'string',
            'name' => 'Пол'
        ],
        'name' => [
            'type' => 'string',
            'name' => 'Имя'
        ],
        'last_name' => [
            'type' => 'string',
            'name' => 'Фамилия'
        ],
        'email' => [
            'type' => 'string',
            'name' => 'E-mail'
        ],
        'phone' => [
            'type' => 'string',
            'name' => 'Телефон'
        ],
        'date' => [
            'type' => 'string',
            'name' => 'Дата'
        ],
        'theme' => [
            'type' => 'theme_id',
            'name' => 'Тема'
        ],
        'disclaimer' => [
            'type' => 'bool',
            'name' => 'Согласие на обработку персональных данных'
        ],
        'feedback' => [
            'type' => 'bool',
            'name' => 'Согласие на получение рекламной информации'
        ],
        'source' => [
            'type' => 'string',
            'name' => 'Источник'
        ],
    ];

    public function __construct($params)
    {
        $params = \Helper::makeSafe($params);
        $this->params = $params;
    }

    /**
     * Function for handle request
     */
    abstract protected function handle();

    /**
     * Make value for email data
     * @param string $type type of form param value (set in $arKeyName)
     * @param array|string $param form param value
     * @param string $value
     * @return string value for email
     */
    public function makeEmailMessageValue($type, $param, &$value = '') {
        if (is_array($param)) {
            foreach ($param as $el) {
                self::makeEmailMessageValue($type, $el, $value);
            }
        } else {
            if ($type == 'string') {
                $value .= $value ? ', ' . $param : $param;
            } elseif ($type == 'bool') {
                $value .= $param ? "Да" : "Нет";
            } elseif ($type == 'theme_id') {
                $arTheme = \Helper::getThemesConsult();
                $value .= $value ? ', ' . $arTheme[$param]['name'] : $arTheme[$param]['name'];
            }
        }
        return $value;
    }

    /**
     * Send email about filling form
     */
    public function sendEmail() {
        $arSendTo = $this->getArSendTo();
        if (!empty($arSendTo) && is_array($arSendTo)) {
            $strMessage = 'Заполнена форма "' . ($this->formName ?: $this->formId) . '":<br><br>';
            foreach ($this->rules as $key => $el) {
                if (array_key_exists($key, $this->arKeyName)) {
                    $value = $this->makeEmailMessageValue($this->arKeyName[$key]['type'], $this->params[$key]);
                    $strMessage .= $this->arKeyName[$key]['name'] . ": " . $value . "<br>";
                }
            }

            $arData = [
                'EMAIL_TO' => '',
                'SUBJECT' => 'Заполнена форма "' . ($this->formName ?: $this->formId) . '"',
                'MESSAGE' => $strMessage,
            ];

            foreach ($arSendTo as $to) {
                $arData['EMAIL_TO'] = $to;
                \CEvent::Send('FORM_FILLING', 's1', $arData);
            }
        }
    }

    /**
     * Get array of send to
     * @return array
     */
    public function getArSendTo() {
        return $this->arSendTo;
    }
}