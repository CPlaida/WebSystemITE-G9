<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\Response;
use Psr\Log\LoggerInterface;
use Config\Services;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;
    
    /**
     * Instance of Response object
     *
     * @var Response
     */
    protected $response;
    
    /**
     * Instance of logger
     *
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * User role from session
     *
     * @var string|null
     */
    protected $userRole;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
        
        $this->response = $response;
        $this->logger = $logger;
        
        // Get user role from session
        $this->userRole = session('user_role');
        
        // Set default timezone
        date_default_timezone_set('Asia/Manila');
    }
    
    /**
     * Check if user has required role
     *
     * @param string|array $roles Required role(s)
     * @return bool
     */
    protected function hasAccess($roles): bool
    {
        if (empty($roles)) {
            return true;
        }
        
        $userRole = $this->userRole;
        
        if (is_string($roles)) {
            return $userRole === $roles;
        }
        
        if (is_array($roles)) {
            return in_array($userRole, $roles, true);
        }
        
        return false;
    }
    
    /**
     * Show error page
     *
     * @param int $statusCode HTTP status code
     * @param string $message Error message
     * @return void
     */
    protected function showError(int $statusCode, string $message = ''): void
    {
        $errorHandler = new \App\Libraries\ErrorHandler(
            config('Exceptions'),
            $this->request,
            $this->response
        );
        
        $errorHandler->handle(
            new \Exception($message, $statusCode),
            $statusCode,
            $message
        );
    }
    
    /**
     * Check if request is AJAX
     *
     * @return bool
     */
    protected function isAjax(): bool
    {
        return $this->request->isAJAX();
    }
}
