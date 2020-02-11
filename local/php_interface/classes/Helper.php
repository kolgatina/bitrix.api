<?

class Helper
{
    /**
     * Values of constants have no practical meaning, constants used for communication between classes
     */
    const DATE_FORMAT = 'date_format';
    const THEME_CONSULT = 'theme_consult';

    /**
     * ID of user property with enum of consult themes
     */
    const FIELD_ID_OF_CONSULT_THEME = 1;

    /**
     * Regular expression for check params
     * @var array
     */
    public static $regex = [
        'bool' => '/^1$|^0$/',
        'string' => '/^[-\'a-zA-ZА-яЁё\s]+$/u',
        'number' => '/^[0-9]+$/u',
        'email' => '/.+@.+\..+/',
        'phone' => '/^((\+8|8|\+7|7)[\- ]?)(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/',
        'sex' => '/^М$|^Ж$/'
    ];

    /**
     * Recursively make argument safe
     * @param $val
     * @return array|string
     */
    public function makeSafe($val) {
        if (!is_array($val))
            return htmlspecialchars(trim($val));

        foreach ($val as $k => $v) {
            $val[$k] = self::MakeSafe($v);
        }

        return $val;
    }

    /**
     * Validate params by rules. Rules defined in child class
     * @param array $params
     * @param array $rules
     * @return array
     */
    public function validate($params, $rules) {
        $error = [];
        foreach ($rules as $code => $rules) {
            if (in_array('required', $rules) && !$params[$code]) {
                $error[$code] = 'Empty Parameter';
            } elseif (in_array('multiple', $rules) && !is_array($params[$code])) {
                $error[$code] = 'Expecting Multiple Parameter';
            } elseif (is_array($params[$code]) && !in_array('multiple', $rules)) {
                $error[$code] = 'Not Multiple Parameter';
            } else {
                foreach ($rules as $el) {
                    if (is_array($el) && count($el) == 1) {
                        $key = key($el);
                        if ($key == 'required_with') {
                            foreach ($el[$key] as $field) {
                                if ($params[$field] && !$params[$code]) {
                                    $error[$code] = 'Empty Parameter';
                                }
                            }
                        }
                        if ($key == 'required_with_all') {
                            $count = 0;
                            foreach ($el[$key] as $field) {
                                if ($params[$field] && !$params[$code]) {
                                    ++$count;
                                }
                            }
                            if ($count == count($el[$key])) {
                                $error[$code] = 'Empty Parameter';
                            }
                        }
                        if ($key == 'required_without') {
                            foreach ($el[$key] as $field) {
                                if (!$params[$field] && !$params[$code]) {
                                    $error[$code] = 'Empty Parameter';
                                }
                            }
                        }
                        if ($key == 'required_without_all') {
                            $count = 0;
                            foreach ($el[$key] as $field) {
                                if (!$params[$field] && !$params[$code]) {
                                    ++$count;
                                }
                            }
                            if ($count == count($el[$key])) {
                                $error[$code] = 'Empty Parameter';
                            }
                        }
                    }
                    if ($params[$code]) {
                        if (self::$regex[$el] && !empty(self::checkValueByRegex(self::$regex[$el], $params[$code]))) {
                            $error[$code] = 'Wrong Parameter';
                        }
                        if ($el == self::DATE_FORMAT && !empty(self::checkDateFormat($params[$code]))) {
                            $error[$code] = 'Wrong Parameter';
                        }
                        if ($el == self::THEME_CONSULT && !empty(self::checkThemeConsult($params[$code]))) {
                            $error[$code] = 'Wrong Parameter';
                        }
                    }
                }
            }
        }

        return $error;
    }

    /**
     * Recursively check value by regular expression
     * @param string $regex regular expression
     * @param $value
     * @param array $error
     * @return array of wrong value
     */
    public function checkValueByRegex($regex, $value, &$error = []) {
        if (is_array($value)) {
            foreach ($value as $el) {
                self::checkValueByRegex($regex, $el, $error);
            }
        } else {
            if (!preg_match($regex, $value)) {
                $error[] = $value;
            }
        }
        return $error;
    }

    /**
     * Recursively check date format
     * @param $value
     * @param string $format
     * @param array $error
     * @return array of wrong value
     */
    public function checkDateFormat($value, $format = 'd.m.Y', &$error = []) {
        if (is_array($value)) {
            foreach ($value as $el) {
                self::checkDateFormat($el, $format, $error);
            }
        } else {
            try {
                \Carbon\Carbon::createFromFormat($format, $value);
            } catch (\Exception $e) {
                $error[] = $value;
            }
        }
        return $error;
    }


    /**
     * Recursively check theme for consult
     * @param $value
     * @param array $error
     * @return array of wrong value
     */
    public function checkThemeConsult($value, &$error = []) {
        if (is_array($value)) {
            foreach ($value as $el) {
                self::checkThemeConsult($el, $error);
            }
        } else {
            if (!array_key_exists($value, self::getThemesConsult())) {
                $error[] = $value;
            }
        }
        return $error;
    }

    public function getThemesConsult() {
        $cache_time = 604800;
        $cache_id = 'theme_consult';
        $cache_path = 'theme';
        $cache = new CPHPCache();
        if ($cache->InitCache($cache_time, $cache_id, $cache_path)) {
            $result = $cache->GetVars();
        } else {
            $result = [];
            $db = CUserFieldEnum::GetList([], ['USER_FIELD_ID' => self::FIELD_ID_OF_CONSULT_THEME]);
            while ($el = $db->Fetch()) {
                $result[$el['ID']] = [
                    'id' => $el['ID'],
                    'name' => $el['VALUE'],
                ];
            }
            $cache->StartDataCache($cache_time, $cache_id, $cache_path);
            $cache->EndDataCache($result);
        }
        return $result;
    }

    /**
     * Add new record to highloadblock by it's name
     * @param $data
     * @param $highloadblockId
     * @return array
     */
    public function addDataToHighloadblock($data, $highloadblockName) {
        $hlData = [];
        array_map(function ($key, $value) use (&$hlData) {
            $hlData['UF_' . strtoupper($key)] = $value;
        }, array_keys($data), $data);

        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(
            ['filter' => ['NAME' => $highloadblockName]]
        )->fetch();

        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();

        $hlResult = $entity_data_class::add($hlData);

        if($hlResult->isSuccess()) {
            return true;
        }

        return false;
    }
}