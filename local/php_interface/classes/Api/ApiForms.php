<?

namespace Api;

class ApiForms extends Api
{
    public $apiName = 'forms';

    public $formIdField = 'form_id';

    /**
     * Get all dealers
     * @return string
     */
    public function indexAction()
    {
        return $this->response('Method ' . $this->method . ' Not Allowed', 405);
    }

    public function viewAction()
    {
        return $this->response('Method ' . $this->method . ' Not Allowed', 405);
    }

    public function createAction()
    {
        $formId = preg_replace('/[^A-z0-9]/', '', $this->requestParams[$this->formIdField]);
        $this->requestParams[$this->formIdField] = $formId;
        $formClass = __NAMESPACE__ . '\\' . 'Form' . $formId;

        $test = !empty($this->requestParams['test']) ? 1 : 0;
        $logStart = 'FORM ' . ($test ? 'TEST ' : '');

        $params = $this->requestBody;
        $params = array_change_key_case($params);

        $params['source'] = $_SERVER['HTTP_REFERER'] ?: '';

        \FileLog::add($logStart . 'request' . ($formId ? ' (' . $formId . '):' : ':'), $params);

        if (!class_exists($formClass)) {
            $errors = [$this->formIdField => 'Wrong Parameter'];
            \FileLog::add($logStart . 'response error' . ($formId ? ' (' . $formId . '):' : ':'), $errors);
            return $this->response($errors, 400);
        }

        $form = new $formClass($params);

        $errors = \Helper::validate($form->params, $form->rules);

        if (!empty($errors)) {
            \FileLog::add($logStart . 'response error' . ($formId ? ' (' . $formId . '):' : ':'), $errors);
            return $this->response($errors, 400);
        } else {
            $form->formId = $formId;

            if ($test) {
                \FileLog::add($logStart . 'response ok' . ($formId ? ' (' . $formId . ')' : ''));
                return $this->response([], 200);
            }

            if ($form->isSendEmail) {
                $form->sendEmail();
            }

            try {
                if ($result = $form->handle()) {
                    \FileLog::add($logStart . 'response ok' . ($formId ? ' (' . $formId . '):' : ':'), $result);
                    return $this->response([], 200);
                }
            } catch (\Exception $e) {
                \FileLog::add($logStart . 'response error' . ($formId ? ' (' . $formId . '):' : ':'), $e->getMessage(), false);
                return $this->response([], 500);
            }
        }

        return $this->response([], 500);
    }

    public function updateAction()
    {
        return $this->response('Method ' . $this->method . ' Not Allowed', 405);
    }

    public function deleteAction()
    {
        return $this->response('Method ' . $this->method . ' Not Allowed', 405);
    }
}