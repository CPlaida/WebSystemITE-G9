<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

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

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }

    /**
     * Check if the current user has any of the allowed roles.
     *
     * @param array $allowedRoles
     * @return bool
     */
    protected function hasRole(array $allowedRoles): bool
    {
        $userRole = session()->get('role');
        return in_array($userRole, $allowedRoles, true);
    }

    /**
     * Enforce role-based access. If the user does not have an allowed role,
     * it redirects to login or returns an unauthorized JSON response.
     *
     * @param array $allowedRoles
     * @param string $redirectUrl
     * @return void|ResponseInterface
     */
    protected function requireRole(array $allowedRoles, string $redirectUrl = 'login')
    {
        if (!$this->hasRole($allowedRoles)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access'])->setStatusCode(401);
            }
            return redirect()->to($redirectUrl)->with('error', 'Unauthorized access');
        }
    }

    /**
     * Get the role-specific view path.
     * Assumes views are structured like 'Roles/{role}/{feature}/{viewName}'.
     *
     * @param string $viewName The base view name (e.g., 'appointments/Appointmentlist')
     * @return string The full view path
     */
    protected function getRoleViewPath(string $viewName): string
    {
        $role = session()->get('role');
        // Default to 'admin' if role is not set or unknown, or if a specific role view doesn't exist
        $basePath = 'Roles/' . ($role ?: 'admin') . '/';
        $fullPath = $basePath . $viewName;

        // Check if the specific role view exists, otherwise fallback to admin view
        if (!file_exists(APPPATH . 'Views/' . $fullPath . '.php')) {
            $fullPath = 'Roles/admin/' . $viewName;
        }
        return $fullPath;
    }
}
