<?

namespace Api;

abstract class Api
{
    public $apiName = '';

    protected $method = ''; //GET|POST|PUT|DELETE

    public $requestUri = [];
    public $requestParams = [];
    public $requestPathParam = '';
    public $requestBody = [];

    protected $action = '';

    public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->requestParams = $_REQUEST;

        $request = file_get_contents('php://input');
        if ($request && strlen($request) > 0) {
            $this->requestBody = json_decode($request, true);
        }

        // GET parameters into URI array
        $this->requestUri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
    }

    public function run()
    {
        // the first elements of URI array should go in the following order: "api", apiName
        if (array_shift($this->requestUri) !== 'api' || array_shift($this->requestUri) !== $this->apiName) {
            throw new RuntimeException('Not Found', 404);
        }

        $this->action = $this->getAction();

        if (method_exists($this, $this->action)) {
            return $this->{$this->action}();
        } else {
            throw new RuntimeException('Invalid Method', 405);
        }
    }

    protected function response($data, $status = 500)
    {
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        if ($status == 200) {
            return json_encode([
                'payload' => $data,
                'status' => 1
            ]);
        } elseif ($status == 400) {
            return json_encode([
                'payload' => $data,
                'status' => 0
            ]);
        } else {
            die();
        }
    }

    private function requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            400 => 'Bad Request',
            404 => 'Not Found',
            401 => 'Unauthorized',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }

    protected function getAction()
    {
        $method = $this->method;
        switch ($method) {
            case 'GET':
                //return !empty($this->requestPathParam) ? 'viewAction' : 'indexAction';
                return 'indexAction';
                break;
            case 'POST':
                return 'createAction';
                break;
            case 'PUT':
                return 'updateAction';
                break;
            case 'DELETE':
                return 'deleteAction';
                break;
            default:
                return null;
        }
    }

    abstract protected function indexAction();

    abstract protected function viewAction();

    abstract protected function createAction();

    abstract protected function updateAction();

    abstract protected function deleteAction();
}