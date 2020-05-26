<?php

/*
 *  Copyright (C) 2019 Universität zu Köln
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

/**
 * @brief Middleware class for site authentication
 * @author Rafael Nájera <rafael.najera@uni-koeln.de>
 *
 * Short term user authentication is implemented by storing the user id
 * in the PHP session.
 * Long term user authentication ('Remember me' option) is done by storing a
 * cookie in the user's browser with the user id information, a random token and
 * an encrypted hash of the used id and the token. The hash is generated using
 * a randomly chosen secret key.
 */

namespace DareOne\auth;

//use APM\System\ApmContainerKey;
use DareOne\models\user\User;
use DareOne\models\user\UserToken;
use DareOne\system\DareLogger;
use DateInterval;
use DateTime;

use Exception;
//use Monolog\Logger;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Dflydev\FigCookies\FigRequestCookies;
use \Dflydev\FigCookies\SetCookie;
use \Dflydev\FigCookies\FigResponseCookies;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Middleware class for site authentication
 *
 */
class Authenticator
{


    const LOGIN_PAGE_SIGNATURE = 'Login-8gRSSm23HPdStrEid5Wi';
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Logger
     */
    private $logger, $apiLogger, $siteLogger;

    /**
     * @var RouteParser
     */
    protected $router;


    private $cookieName = 'rme';
    private $secret = '1256106427895916503';
    private $debugMode = false;

    //Constructor

    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var Twig
     */
    private $view;

    /**
     * Authenticator constructor.
     * @param $ci
     *
     */
    public function __construct($ci)
    {
        $this->ci = $ci;
        $this->view = $ci['view'];
        $this->baseUrl=$ci["baseurl"];
    }



    /**
     * @return string
     * @throws Exception
     */
    private function generateRandomToken()
    {
        return bin2hex(random_bytes(20));
    }

    private function generateLongTermCookieValue($token, $userId)
    {
        $v = $userId . ':' . $token;
        return $v . ':' . $this->generateMac($v);
    }

    private function generateMac($v)
    {
        return hash_hmac('sha256', $v, $this->secret);
    }


    public function authenticate(Request $request, Response $response, $next)
    {
        session_start();
        $logger=DareLogger::defaultLogger();
        $logger->info(print_r('Starting authenticator middleware', true));

        $success = false;
        if (!isset($_SESSION['userid'])) {
            // Check for long term cookie
            $logger->warning(print_r('SITE : No session', true));
            //$userId = $this->getUserIdFromLongTermCookie($request);
            /*if ($userId !== false) {
                $success = true;
            }*/
        } else {
            $userId = $_SESSION['userid'];
            $logger->info(print_r('SITE : Session is set, user id = ' . $userId, true));

            if (User::find($userId)) {
                $logger->info(print_r('User id exists!', true));
                $success = true;
            } else {
                $logger->warning(print_r('User id does not exist!', true));
            }

        }
        if ($success) {
            $logger->info(print_r('SITE: Success, go ahead!', true));
            $_SESSION['userid'] = $userId;
            //$ui = $this->userManager->getUserInfoByUserId($userId);
            /*if ($this->userManager->isUserAllowedTo($userId, 'manageUsers')) {
                $ui['manageUsers'] = 1;
            }*/

            //$this->container->set('user_info', $ui);
            $response=$next($request, $response);
            return $response;
        } else {
            $logger->warning("SITE : Authentication fail, logging out "
                . "and redirecting to login");
            session_unset();
            session_destroy();
            //$response = new \Slim\Psr7\Response();
            //$response = FigResponseCookies::expire($response,
                //$this->cookieName);



            return $response->withRedirect($this->baseUrl.'login', 301);
        }
    }


    public function login($userName, $pwd, $userAgent, $ipAddress)
    {
        session_start();
        $logger=DareLogger::defaultLogger();
        $msg = '';


        if (isset($userName) && isset($pwd)) {
            $logger->info('Got data for login');
            $userName = filter_var($userName, FILTER_SANITIZE_STRING);
            $pwd = filter_var($pwd, FILTER_SANITIZE_STRING);

                //isset($data['rememberme']) ? $data['rememberme'] : '';
            $logger->info('Trying to log in user ' . $userName);
            if (User::verifyUserPassword($userName, $pwd)) {

                // Success!
                $user = User::getUserByUserName($userName);
                $userId = $user["id"];
                $_SESSION['userid'] = $userId;
                $logger->info('Generating token cookie');
                $token = $this->generateRandomToken();
                error_log("------------login");
                UserToken::create([
                    "user_id" => $userId,
                    "user_agent" => $userAgent,
                    "ip_address" => $ipAddress,
                    "token" => $token
                ]);

                $cookieValue = $this->generateLongTermCookieValue($token,
                    $userId);
                /*if ($rememberme === 'on'){
                    error_log('User wants to be remembered');
                    $now = new DateTime();
                    $cookie = SetCookie::create($this->cookieName)
                        ->withValue($cookieValue)
                        ->withExpires($now->add(
                            new DateInterval('P14D')));
                } else {
                    $cookie = SetCookie::create($this->cookieName)
                        ->withValue($cookieValue);
                }*/

                return TRUE;
            } else {
                session_unset();
                session_destroy();
                return false;
            }
        }
        session_unset();
        session_destroy();
        return false;


    }

    public function logout(Request $request, Response $response)
    {
        $logger=DareLogger::defaultLogger();
        $logger->info('Logout request');
        session_start();
        if (!isset($_SESSION['userid'])) {
            $logger->warning("Logout attempt without a valid session");
            return $response->withRedirect($this->baseUrl.'no-login', 301);
        }
        $userId = $_SESSION['userid'];
        $userName = UserManager::getUserInfoById($userId)["username"];
        //$userName = $this->userManager->getUsernameFromUserId($userId);
        if ($userName === false) {
            $logger->warning("Can't get username from user Id at "
                . "logout attempt", ['userId' => $userId]);
        }
        $logger->info('Logout '.$userName);
        session_unset();
        session_destroy();
        //$response = FigResponseCookies::expire($response, $this->cookieName);
        return $response->withRedirect($this->baseUrl.'manager', 301);
    }


    public function authenticateApiRequest(Request $request, RequestHandlerInterface $handler)
    {
        $userId = $this->getUserIdFromLongTermCookie($request);
        if ($userId === false) {
            $this->apiLogger->notice("Authentication fail");
            $response = new \Slim\Psr7\Response();
            return $response->withStatus(401);
        }
        error_log('API : Success, go ahead!');
        $this->container->set('apiUserId', $userId);
        return $handler->handle($request);
    }

    private function getUserIdFromLongTermCookie(Request $request)
    {
        error_log('Checking long term cookie');
        $longTermCookie = FigRequestCookies::get($request, $this->cookieName);
        if ($longTermCookie !== NULL and $longTermCookie->getValue()) {
            $cookieValue = $longTermCookie->getValue();
            list($userId, $token, $mac) = explode(':', $cookieValue);
            if (hash_equals($this->generateMac($userId . ':' . $token), $mac)) {
                $userToken = $this->userManager->getUserToken(
                    $userId,
                    $request->getHeader('User-Agent')[0],
                    $request->getServerParams()['REMOTE_ADDR']
                );
                if (hash_equals($userToken, $token)) {
                    error_log('Cookie looks good, user = ' . $userId);
                    return $userId;
                }
                error_log('User tokens do not match -> ' . $userToken .
                    ' vs ' . $token);
                return false;
            }
            error_log('Macs do not match!');
            return false;
        }
        error_log('... there is no cookie. Fail!');
        return false;
    }

}