<?php

namespace App\Filters;

<<<<<<< HEAD
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
=======
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;
>>>>>>> 57646d5 (Initial commit)

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
<<<<<<< HEAD
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        if ($arguments) {
            $allowedRoles = is_array($arguments) ? $arguments : explode(',', $arguments);
            $userRole = session()->get('role');
            
            if (!in_array($userRole, $allowedRoles)) {
                return redirect()->to('/login')->with('error', 'Access denied.');
            }
=======
        $session = session();
        
        // Check if user is logged in
        if (!$session->has('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        // If no roles specified, just check if logged in
        if (empty($arguments)) {
            return;
        }
        
        // Check if user has required role
        $userRole = $session->get('role');
        if (!in_array($userRole, $arguments)) {
            // Redirect to dashboard or show 403
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to access this page.');
>>>>>>> 57646d5 (Initial commit)
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
<<<<<<< HEAD
        // Do nothing
    }
}
=======
        // No action needed after the request
    }
}
>>>>>>> 57646d5 (Initial commit)
