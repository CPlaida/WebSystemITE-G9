<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please log in to continue.');
        }

        if ($arguments) {
            $allowedRoles = is_array($arguments) ? $arguments : explode(',', $arguments);
            $userRole = session()->get('role');
            
            if (!in_array($userRole, $allowedRoles)) {
                return redirect()->to('/login')->with('error', 'Access denied.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}