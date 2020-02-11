<?

namespace Api;

class ApiThemes extends Api
{
    public $apiName = 'themes';

    /**
     * Get themes
     * @return string
     */
    public function indexAction()
    {
        $result = [];
        if ($this->requestParams['type'] == 'consult') {
            $result = \Helper::getThemesConsult();
        }

        if (!empty($result)) {
            return $this->response($result, 200);
        }
        return $this->response('Data not found', 404);
    }

    public function viewAction()
    {
        return $this->response('Method ' . $this->method . ' Not Allowed', 405);
    }

    public function createAction()
    {
        return $this->response('Method ' . $this->method . ' Not Allowed', 405);
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