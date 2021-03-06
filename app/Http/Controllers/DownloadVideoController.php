<?php
namespace App\Http\Controllers;

use App\Http\Requests;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Auth;
use App\FilePath;

use \DomainException;
use \InvalidArgumentException;
use \UnexpectedValueException;
use \DateTime;
use \Firebase\JWT\JWT;

class DownloadVideoController extends Controller {
    public function get()
    {
        if (request('file') == '' || is_null(request('file'))) {
            echo 'Not found';
            exit;
        } else {
            $filename = request('file');
            $path = storage_path('app' . $filename);
            $file = FilePath::where('path', $path)->firstOrFail();
            if (! empty(array_intersect(array_values(['anonymouse']), $file->roles()))) {
                // Content type
                if (in_array(pathinfo($file->path, PATHINFO_EXTENSION), ['ts', 'm3u8'])) {
                    return response()->download($path, pathinfo($file->path)['basename'], ['Content-Type' => 'application/vnd.apple.mpegurl']);
                } else {
                    return response()->download($path);
                }
            }

            // Public directory
            if (strpos($path, 'storage/app/public')) {
                return response()->download($path);
            }
        }

        if (request('token')) {
            try {
                $jwt = JWT::decode(request('token'), env('JWT_SECRET'), array('HS512'));
                $sub = $jwt->sub;

                // Retrieve CSRF token
                $curl_get = curl_init();
                curl_setopt($curl_get, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl_get, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt_array($curl_get, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => env('WEB_URL') . '/services/session/token',
                ));
                $csrf_token = curl_exec($curl_get);
                curl_close($curl_get);
                $csrf_header = 'X-CSRF-Token: ' . $csrf_token;

                // REST Server URL
                $request_url = env('WEB_URL') . '/api/users/login';

                // User data
                $user_data = array(
                  'username' => env('ADMIN_USER', 'remote_admin'),
                  'password' => env('ADMIN_PASSWORD', '123456'),
                );

                // cURL
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_URL, $request_url);
                curl_setopt($curl, CURLOPT_POST, 1); // Do a regular HTTP POST
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $csrf_header));
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($user_data)); // Set POST data
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

                $response = curl_exec($curl);
                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                // REST Server URL
                $request_url = env('WEB_URL') . '/api2/users/' . $sub;

                // cURL
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_URL, $request_url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $csrf_header));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

                $response = json_decode($response);
                curl_setopt($curl, CURLOPT_COOKIE, $response->session_name . '=' . $response->sessid);

                $response = curl_exec($curl);
                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                $logged_user = json_decode($response);

                // Anonymouse user
                if (is_null($logged_user)) {
                    echo 'Not permitted';
                    exit;
                }
            } catch (\Exception $e) {
                echo 'Not permitted';
                exit;
            }
        } else {
            // Authorize token
            header('Access-Control-Allow-Origin: ' . env('WEB_URL'));
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');

             if (!isset($_SERVER['PHP_AUTH_USER'])) {
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Not authorized';
                exit;
            }

            // Retrieve CSRF token
            $curl_get = curl_init();
            curl_setopt($curl_get, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl_get, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt_array($curl_get, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => env('WEB_URL') . '/services/session/token',
            ));
            $csrf_token = curl_exec($curl_get);
            curl_close($curl_get);
            $csrf_header = 'X-CSRF-Token: ' . $csrf_token;

            // REST Server URL
            $request_url = env('WEB_URL') . '/api/users/login';

            // User data
            $user_data = array(
              'username' => $_SERVER['PHP_AUTH_USER'],
              'password' => $_SERVER['PHP_AUTH_PW'],
            );

            // cURL
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_URL, $request_url);
            curl_setopt($curl, CURLOPT_POST, 1); // Do a regular HTTP POST
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $csrf_header));
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($user_data)); // Set POST data
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            // Check if login was successful
            if ($http_code == 200) {
                // Convert json response as array
                $logged_user = json_decode($response);
                $logged_user = $logged_user->user;
            }
            else {
                // Get error msg
                $http_message = curl_error($curl);
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Not authorized';
                exit;
            }
            header('Access-Control-Allow-Credentials: true');
        }

        if (empty(array_intersect(array_values((array)$logged_user->roles), $file->roles()))) {
            if (request('token')) {
                echo 'Not permitted - Not enough role (TOKEN)';
                exit;
            } else {
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Not permitted - Not enough role (BASIC_AUTH)';
                exit;
            }
        }

        return response()->download($path);
    }

    public function authenticate() {
        header('WWW-Authenticate: Basic realm="Test Authentication System"');
        header('HTTP/1.0 401 Unauthorized');
        echo "You must enter a valid login ID and password to access this resource\n";
        exit;
    }

    public function size()
    {
        if (request('file') == '' || is_null(request('file'))) {
            echo '0.0';
        } else {
            $filename = request('file');
            $path = storage_path('app' . $filename);
            return ['size' => $this->formatBytes(\Storage::size($filename))];
        }
    }

    /**
     * Format bytes to kb, mb, gb, tb
     *
     * @param  integer $size
     * @param  integer $precision
     * @return integer
     */
    public static function formatBytes($size, $precision = 2)
    {
        if ($size > 0) {
            $size = (int) $size;
            $base = log($size) / log(1024);
            $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');

            return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
        } else {
            return $size;
        }
    }
 }
