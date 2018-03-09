<?php
namespace App\Http\Controllers;

use App\Http\Requests;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Auth;
use App\FilePath;

class DownloadVideoController extends Controller {
    public function get()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
        header('Access-Control-Allow-Credentials: true');

        if (request('file') == '' || is_null(request('file'))) {
            return abort(404);
        }
        $filename = request('file');
        $path = storage_path('app' . $filename);
        $file = FilePath::where('path', $path)->firstOrFail();
        if (! empty(array_intersect(array_values(['anonymouse']), $file->roles())) ||
            in_array(pathinfo($file->path, PATHINFO_EXTENSION), ['ts', 'm3u8'])) {
            return response()->download($path, 'online', ['Content-Type' => 'application/vnd.apple.mpegurl']);
        }

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
            CURLOPT_URL => 'https://movies_theme.dev/services/session/token',
        ));
        $csrf_token = curl_exec($curl_get);
        curl_close($curl_get);
        $csrf_header = 'X-CSRF-Token: ' . $csrf_token;

        // REST Server URL
        $request_url = 'https://movies_theme.dev/api/users/login';

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
        }
        else {
            // Get error msg
            $http_message = curl_error($curl);
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Not authorized';
            exit;
        }

        if (empty(array_intersect(array_values((array)$logged_user->user->roles), $file->roles()))) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Not permitted';
            exit;
        }

        return response()->download($path);
    }

    public function authenticate() {
        header('WWW-Authenticate: Basic realm="Test Authentication System"');
        header('HTTP/1.0 401 Unauthorized');
        echo "You must enter a valid login ID and password to access this resource\n";
        exit;
    }

 }
