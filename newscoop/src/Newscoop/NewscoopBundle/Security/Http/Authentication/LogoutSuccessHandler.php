<?php
 
namespace Newscoop\NewscoopBundle\Security\Http\Authentication;
 
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
 
/**
 * Custom authentication success handler
 */
class LogoutSuccessHandler extends DefaultLogoutSuccessHandler
{
    protected $authAdapter;
 
    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     *
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request)
    {
        // Clear Zend auth
        $zendAuth = \Zend_Auth::getInstance();
        \Article::UnlockByUser((int) $zendAuth->getIdentity());
        $zendAuth->clearIdentity();

        setcookie('NO_CACHE', 'NO', time()-3600, '/', '.'.$this->extractDomain($_SERVER['HTTP_HOST']));

        return parent::onLogoutSuccess($request);
    }

    private function extractDomain($domain)
    {
        if(preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches))
        {
            return $matches['domain'];
        } else {
            return $domain;
        }
    }
}
