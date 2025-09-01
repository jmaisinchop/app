<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class UserAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('cookie');
        helper('helpdesk');
        $client = Services::client();
        $session = Services::session();
        $uri = service('uri');

        // Si el usuario está online...
        if ($client->isOnline()) {
            
            // Lógica para forzar cambio de contraseña
            $user = $client->getRow(['id' => $session->get('clientID')]);
            
            // Si la bandera en la BD es 1 Y no está ya en la página de cambio...
            if (isset($user->force_password_change) && $user->force_password_change == 1 && $uri->getPath() !== 'portal/forzar-cambio-clave') {
                // ...lo redirigimos a la página obligatoria de cambio de contraseña.
                return redirect()->to(site_url('portal/forzar-cambio-clave'));
            }

            // Lógica original del filtro para usuarios ya logueados
            if (isset($arguments) && $arguments[0] == 'visitor') {
                return redirect()->route('portal_dashboard');
            }
        } 
        // Si no está online y la página requiere ser usuario...
        elseif (isset($arguments) && $arguments[0] == 'user') {
            return redirect()->route('portal_login');
        }
    }

    //--------------------------------------------------------------------

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}