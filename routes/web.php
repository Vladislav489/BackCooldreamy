<?php

use App\ModelAdmin\ImportExport\ExportFronDBCSV;
use App\Models\Import\CronImportUser;
use App\Services\NextCloud\NextCloud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/reset_password/{token}', function (Request $request, string $token) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:password_reset_tokens,email',
    ]);

    $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();
    $validToken = Hash::check($token, $reset?->token);

    return $validator->fails() || !$reset || !$validToken
        ? redirect("auth/forgot-password?expired=true")
        : redirect("reset-password/{$token}?email={$request->email}");
})->middleware('guest')->name('app.password.reset');

Route::get('/', function () {
    return redirect('/login');
});

Route::group(['prefix' => 'mail', 'middleware' => 'throttle:3,1'], function (){
    Route::get('preview/{type}/{token}', function ($type, $token) {
        if(!$user = \App\Models\User::where(['token' => $token])->first()) abort(404);

        // $sender = \App\Models\User::find(10005);

        return match ($type) {
            'verification' => new App\Mail\VerificationMail($user->token, $user, true),
            'verified' => new App\Mail\UserVerifiedMail($user, true),
            // 'message' => new App\Mail\ResetPasswordMail($user->token, $user), //TODO: Can be implemented stub
            // 'liked' => new App\Mail\LikeUserMail($user), //TODO: Can be implemented stub
            default => abort(404),

        };
    })->name('mail.preview');




    Route::get('verify/{token}', function ($token){
        if(!$user = \App\Models\User::where(['token' => $token])->first()) abort(419);
         $user->email_verified_at = now();
         $user->is_email_verified = true;
         $user->save();
         // Mail::to($user)->send(new App\Mail\UserVerifiedMail($user)); // Uncomment if needed to notify user
        // return new App\Mail\UserVerifiedMail($user, true);
        return redirect("https://cooldreamy.com/search?user_id={$user->id}&token={$token}");
    })->name('mail.verify');
});



Route::get('register', function () {
    abort(404);
});

Auth::routes(['register' => false]);

Auth::routes();
Route::middleware(['role:admin'])->prefix('admin')->group(function () {
    Route::get('/clear/cache', function (){
        Artisan::call("route:clear");
        Artisan::call("cache:clear");
        Artisan::call("view:clear");
        return redirect()->back();
    });


    Route::get('script/sync/anket/prompts', function (){
        Artisan::call("sync-anket-prompts");
        return redirect()->back();
    })->name("app:stripe-get-payments");

    Route::get('script/stripe/get/payments', function (){
        Artisan::call("app:stripe-get-payments");
        return redirect()->back();
    })->name("stripe-get-payments");

    Route::get('script/get/resolved/ankets', function (){
        Artisan::call("app:get-resolved-ankets");
        return redirect()->back();
    })->name("get-resolved-ankets");

    Route::get('script/assign/ankets/to/test/operator', function (){
        Artisan::call("app:assign-ankets-to-test-operator");
        return redirect()->back();
    })->name("assign-ankets-to-test-operator");

    Route::get('script/assign/ankets/profile/f/files', function (){
        Artisan::call("app:assign-ankets-profile-f-files");
        return redirect()->back();
    })->name("assign-ankets-profile-f-files");

    Route::get('script/assign/ankets/new/all/files', function (){
        Artisan::call("app:assign-ankets-new-all-files");
        return redirect()->back();
    })->name("assign-ankets-new-all-files");

    Route::get('script/assign/ankets/files', function (){
        set_time_limit(2000);
        Artisan::call("app:assign-ankets-files");
        return redirect()->back();
    })->name("assign-ankets-files");

    Route::get('/', [App\Http\Controllers\AdminController::class, 'dashbord'])
        ->name('admin.dashbord');

    Route::get('/dashbord/user/list', [App\Http\Controllers\AdminController::class, 'getDataList'])
        ->name('admin.dashbord.user.list');

    Route::post('/dashbord/user/statistic', [App\Http\Controllers\AdminController::class, 'getCountStatistic'])
        ->name('admin.dashbord.user.statistic');
    Route::post('/dashbord/export/statistic', [App\Http\Controllers\AdminController::class, 'exportStatisticUser'])
        ->name('admin.dashbord.export.user.statistic');





    Route::post('/export-operators/csv', [App\Http\Controllers\AdminController::class, 'exportCsvOperator'])
        ->name('admin.export.csv.cperator');
    Route::post('/export-user/csv', [App\Http\Controllers\AdminController::class, 'exportCsvUser'])
        ->name('admin.export.csv.user');



    Route::get('/import', [App\Http\Controllers\AdminController::class, 'importPage']);
    Route::post('/save/user/one', [App\Http\Controllers\AdminController::class, 'seveUserImage'])
        ->name('admin.import.csv.user.one.image');
    Route::get('/import-aces', [App\Http\Controllers\AdminController::class, 'importAces']);
    Route::get('/import/operators/', [\App\Http\Controllers\AdminController::class, 'operatorsMultichat']);

    Route::post('/upload/fast', [App\Http\Controllers\AdminController::class, 'uploadFastUser'])
        ->name('admin.csv_users.upload.without.image');
    Route::post('/upload/image', [App\Http\Controllers\AdminController::class, 'uploadUseImage'])
        ->name('admin.csv_users.upload.with.image');


    Route::post('/csv-users/upload', [App\Http\Controllers\AdminController::class, 'upload'])->name('admin.csv_users.upload');
    Route::get('/csv-users/data', [App\Http\Controllers\AdminController::class, 'getData'])->name('admin.csv_users.data');
    Route::get('/operators/data', [\App\Http\Controllers\AdminController::class, 'getOperatorsData']);
    Route::post('/csv-operators/upload', [App\Http\Controllers\AdminController::class, 'uploadOperators'])->name('admin.csv_operators.upload');



    Route::post('/csv-operators-admin-ankets/upload', [\App\Http\Controllers\AdminController::class, 'uploadOperatorsAdminAnkets'])
        ->name('admin.csv_operator_admin_ankets.upload');
    Route::get('/csv-operators/data', [App\Http\Controllers\AdminController::class, 'getOperators'])->name('admin.csv_operators.data');

    Route::post('/ankets/timezone', [\App\Http\Controllers\AdminController::class, 'loadTimezone'])->name('admin.ankets.timezone');

    Route::post('/csv-administrators/upload', [App\Http\Controllers\AdminController::class, 'uploadAdministrators'])->name('admin.csv_administrators.upload');
    Route::get('/csv-administrators/data', [App\Http\Controllers\AdminController::class, 'getAdministrators'])->name('admin.csv_administrators.data');

    Route::post('/csv-aces/upload', [App\Http\Controllers\AdminController::class, 'uploadAces'])->name('admin.csv_aces.upload');
    Route::get('/csv-aces/data', [App\Http\Controllers\AdminController::class, 'getAces'])->name('admin.csv_aces.data');

    Route::get('/message-count', [App\Http\Controllers\StatisticsController::class, 'messageCount']);
    Route::get('/message-count-by-users', [App\Http\Controllers\StatisticsController::class, 'messageCountByUsers']);
    Route::get('/age-count-by-users', [App\Http\Controllers\StatisticsController::class, 'ageCountByUsers']);
    Route::get('/registration-users', [App\Http\Controllers\StatisticsController::class, 'getUsersList']);
    Route::get('/cities-list', [App\Http\Controllers\StatisticsController::class, 'getCityList']);
    Route::get('/countries-list', [App\Http\Controllers\StatisticsController::class, 'getCountriesList']);
    Route::post('/upload-countries', [\App\Http\Controllers\AdminController::class, 'uploadRegions'])->name('admin.upload_countries');

    Route::get('/pages', [\App\Http\Controllers\PageController::class, 'adminIndex']);
    Route::get('pages/data', [\App\Http\Controllers\PageController::class, 'adminData']);
    Route::get('/pages/{page}', [\App\Http\Controllers\PageController::class, 'adminShow']);
    Route::post('/pages/{page}', [\App\Http\Controllers\PageController::class, 'update'])->name('admin.pages.update');
    Route::post('/update', [\App\Http\Controllers\PageController::class, 'adminUpdate']);

    Route::get('/settings/', [\App\Http\Controllers\PageController::class, 'settings'])->name('admin.settings');
    Route::post('/settings/change', [\App\Http\Controllers\PageController::class, 'changeSetting'])->name('admin.change_settings');
    Route::get('/get/folder/nextcloud', function (){
        $ApiStoreg = new NextCloud('dmitry','Ag@19836373!','nc.cooldreamy.com');

        $folder = $ApiStoreg->getFolder("/media")->getResponse();
        array_shift($folder);
        foreach ($folder as $key => $item){
            $folder[$key]  = str_replace( ['/media','/'],'',urldecode($item));
        }
        $csv = new ExportFronDBCSV();
        $csv->setDataFrom($folder);
        $csv->setFileName("folder_nextCloud");
        $csvBody = $csv->run();
        return response()->streamDownload(function () use ($csvBody) {echo $csvBody;},$csv->getFileName());
    });

});

Route::get('/data/settings/geo/{id}', [\App\Http\Controllers\PageController::class, 'getSettinggeo']);
Route::get('/data/settings/{id}', [\App\Http\Controllers\PageController::class, 'getSetting']);


Route::get('test/send/email',function(){
   //$user = \App\Models\User::find(126961);
   // $user->email = "smit889721@gmail.com";
   // dd(\Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\VerificationMail(1234,$user)));

/**
 * Class to validate the email address
 *
 * @author CodexWorld.com <contact@codexworld.com>
 * @copyright Copyright (c) 2018, CodexWorld.com
 * @url https://www.codexworld.com
 */
class VerifyEmail {

    protected $stream = false;

    /**
     * SMTP port number
     * @var int
     */
    protected $port = 25;

    /**
     * Email address for request
     * @var string
     */
    protected $from = 'itcyberdynesystems@gmail.com';

    /**
     * The connection timeout, in seconds.
     * @var int
     */
    protected $max_connection_timeout = 30;

    /**
     * Timeout value on stream, in seconds.
     * @var int
     */
    protected $stream_timeout = 5;

    /**
     * Wait timeout on stream, in seconds.
     * * 0 - not wait
     * @var int
     */
    protected $stream_timeout_wait = 0;

    /**
     * Whether to throw exceptions for errors.
     * @type boolean
     * @access protected
     */
    protected $exceptions = false;

    /**
     * The number of errors encountered.
     * @type integer
     * @access protected
     */
    protected $error_count = 0;

    /**
     * class debug output mode.
     * @type boolean
     */
    public $Debug = false;

    /**
     * How to handle debug output.
     * Options:
     * * `echo` Output plain-text as-is, appropriate for CLI
     * * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
     * * `log` Output to error log as configured in php.ini
     * @type string
     */
    public $Debugoutput = 'echo';

    /**
     * SMTP RFC standard line ending.
     */
    const CRLF = "\r\n";

    /**
     * Holds the most recent error message.
     * @type string
     */
    public $ErrorInfo = '';

    /**
     * Constructor.
     * @param boolean $exceptions Should we throw external exceptions?
     */
    public function __construct($exceptions = false) {
        $this->exceptions = (boolean) $exceptions;
    }

    /**
     * Set email address for SMTP request
     * @param string $email Email address
     */
    public function setEmailFrom($email) {
        if (!self::validate($email)) {
            $this->set_error('Invalid address : ' . $email);
            $this->edebug($this->ErrorInfo);
            if ($this->exceptions) {
                throw new verifyEmailException($this->ErrorInfo);
            }
        }
        $this->from = $email;
    }

    /**
     * Set connection timeout, in seconds.
     * @param int $seconds
     */
    public function setConnectionTimeout($seconds) {
        if ($seconds > 0) {
            $this->max_connection_timeout = (int) $seconds;
        }
    }

    /**
     * Sets the timeout value on stream, expressed in the seconds
     * @param int $seconds
     */
    public function setStreamTimeout($seconds) {
        if ($seconds > 0) {
            $this->stream_timeout = (int) $seconds;
        }
    }

    public function setStreamTimeoutWait($seconds) {
        if ($seconds >= 0) {
            $this->stream_timeout_wait = (int) $seconds;
        }
    }

    /**
     * Validate email address.
     * @param string $email
     * @return boolean True if valid.
     */
    public static function validate($email) {
        return (boolean) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Get array of MX records for host. Sort by weight information.
     * @param string $hostname The Internet host name.
     * @return array Array of the MX records found.
     */
    public function getMXrecords($hostname) {
        $mxhosts = array();
        $mxweights = array();
        if (getmxrr($hostname, $mxhosts, $mxweights) === FALSE) {
            $this->set_error('MX records not found or an error occurred');
            $this->edebug($this->ErrorInfo);
        } else {
            array_multisort($mxweights, $mxhosts);
        }
        /**
         * Add A-record as last chance (e.g. if no MX record is there).
         * Thanks Nicht Lieb.
         * @link http://www.faqs.org/rfcs/rfc2821.html RFC 2821 - Simple Mail Transfer Protocol
         */
        if (empty($mxhosts)) {
            $mxhosts[] = $hostname;
        }
        return $mxhosts;
    }

    /**
     * Parses input string to array(0=>user, 1=>domain)
     * @param string $email
     * @param boolean $only_domain
     * @return string|array
     * @access private
     */
    public static function parse_email($email, $only_domain = TRUE) {
        sscanf($email, "%[^@]@%s", $user, $domain);
        return ($only_domain) ? $domain : array($user, $domain);
    }

    /**
     * Add an error message to the error container.
     * @access protected
     * @param string $msg
     * @return void
     */
    protected function set_error($msg) {
        $this->error_count++;
        $this->ErrorInfo = $msg;
    }

    /**
     * Check if an error occurred.
     * @access public
     * @return boolean True if an error did occur.
     */
    public function isError() {
        return ($this->error_count > 0);
    }

    /**
     * Output debugging info
     * Only generates output if debug output is enabled
     * @see verifyEmail::$Debugoutput
     * @see verifyEmail::$Debug
     * @param string $str
     */
    protected function edebug($str) {
        if (!$this->Debug) {
            return;
        }
        switch ($this->Debugoutput) {
            case 'log':
                //Don't output, just log
                error_log($str);
                break;
            case 'html':
                //Cleans up output a bit for a better looking, HTML-safe output
                echo htmlentities(
                        preg_replace('/[\r\n]+/', '', $str), ENT_QUOTES, 'UTF-8'
                    )
                    . "<br>\n";
                break;
            case 'echo':
            default:
                //Normalize line breaks
                $str = preg_replace('/(\r\n|\r|\n)/ms', "\n", $str);
                echo gmdate('Y-m-d H:i:s') . "\t" . str_replace(
                        "\n", "\n \t ", trim($str)
                    ) . "\n";
        }
    }

    /**
     * Validate email
     * @param string $email Email address
     * @return boolean True if the valid email also exist
     */
    public function check($email) {
        $result = FALSE;

        if (!self::validate($email)) {
            $this->set_error("{$email} incorrect e-mail");
            $this->edebug($this->ErrorInfo);
            if ($this->exceptions) {
                throw new verifyEmailException($this->ErrorInfo);
            }
            return FALSE;
        }
        $this->error_count = 0; // Reset errors
        $this->stream = FALSE;

        $mxs = $this->getMXrecords(self::parse_email($email));
        $timeout = ceil($this->max_connection_timeout / count($mxs));
        foreach ($mxs as $host) {
            /**
             * suppress error output from stream socket client...
             * Thanks Michael.
             */
            $this->stream = @stream_socket_client("tcp://" . $host . ":" . $this->port, $errno, $errstr, $timeout);
            if ($this->stream === FALSE) {
                if ($errno == 0) {
                    $this->set_error("Problem initializing the socket");
                    $this->edebug($this->ErrorInfo);
                    if ($this->exceptions) {
                        throw new verifyEmailException($this->ErrorInfo);
                    }
                    return FALSE;
                } else {
                    $this->edebug($host . ":" . $errstr);
                }
            } else {
                stream_set_timeout($this->stream, $this->stream_timeout);
                stream_set_blocking($this->stream, 1);

                if ($this->_streamCode($this->_streamResponse()) == '220') {
                    $this->edebug("Connection success {$host}");
                    break;
                } else {
                    fclose($this->stream);
                    $this->stream = FALSE;
                }
            }
        }

        if ($this->stream === FALSE) {
            $this->set_error("All connection fails");
            $this->edebug($this->ErrorInfo);
            if ($this->exceptions) {
                throw new verifyEmailException($this->ErrorInfo);
            }
            return FALSE;
        }

        $this->_streamQuery("HELO " . self::parse_email($this->from));
        $this->_streamResponse();
        $this->_streamQuery("MAIL FROM: <{$this->from}>");
        $this->_streamResponse();
        $this->_streamQuery("RCPT TO: <{$email}>");
        $code = $this->_streamCode($this->_streamResponse());
        $this->_streamResponse();
        $this->_streamQuery("RSET");
        $this->_streamResponse();
        $code2 = $this->_streamCode($this->_streamResponse());
        $this->_streamQuery("QUIT");
        fclose($this->stream);

        $code = !empty($code2)?$code2:$code;
        var_dump($code);
        switch ($code) {
            case '250':
                /**
                 * http://www.ietf.org/rfc/rfc0821.txt
                 * 250 Requested mail action okay, completed
                 * email address was accepted
                 */
            case '450':
            case '451':
            case '452':
                /**
                 * http://www.ietf.org/rfc/rfc0821.txt
                 * 450 Requested action not taken: the remote mail server
                 * does not want to accept mail from your server for
                 * some reason (IP address, blacklisting, etc..)
                 * Thanks Nicht Lieb.
                 * 451 Requested action aborted: local error in processing
                 * 452 Requested action not taken: insufficient system storage
                 * email address was greylisted (or some temporary error occured on the MTA)
                 * i believe that e-mail exists
                 */
                return TRUE;
            case '550':
                return FALSE;
            default :
                return FALSE;
        }
    }

    /**
     * writes the contents of string to the file stream pointed to by handle
     * If an error occurs, returns FALSE.
     * @access protected
     * @param string $string The string that is to be written
     * @return string Returns a result code, as an integer.
     */
    protected function _streamQuery($query) {
        $this->edebug($query);
        return stream_socket_sendto($this->stream, $query . self::CRLF);
    }

    /**
     * Reads all the line long the answer and analyze it.
     * If an error occurs, returns FALSE
     * @access protected
     * @return string Response
     */
    protected function _streamResponse($timed = 0) {
        $reply = stream_get_line($this->stream, 1);
        $status = stream_get_meta_data($this->stream);

        if (!empty($status['timed_out'])) {
            $this->edebug("Timed out while waiting for data! (timeout {$this->stream_timeout} seconds)");
        }

        if ($reply === FALSE && $status['timed_out'] && $timed < $this->stream_timeout_wait) {
            return $this->_streamResponse($timed + $this->stream_timeout);
        }


        if ($reply !== FALSE && $status['unread_bytes'] > 0) {
            $reply .= stream_get_line($this->stream, $status['unread_bytes'], self::CRLF);
        }
        $this->edebug($reply);
        return $reply;
    }

    /**
     * Get Response code from Response
     * @param string $str
     * @return string
     */
    protected function _streamCode($str) {
        preg_match('/^(?<code>[0-9]{3})(\s|-)(.*)$/ims', $str, $matches);
        $code = isset($matches['code']) ? $matches['code'] : false;
        return $code;
    }

}

/**
 * verifyEmail exception handler
 */
class verifyEmailException extends Exception {

    /**
     * Prettify error message output
     * @return string
     */
    public function errorMessage() {
        $errorMsg = $this->getMessage();
        return $errorMsg;
    }

}

$o = new VerifyEmail();
    var_dump($o->check("smit88972@gmail.com"));
    var_dump($o->check("lidici7544@undewp.com"));
    var_dump($o->check("Tundepaul@yahoo.com"));
    var_dump($o->check("jamiehughesm7@outlook.com"));
});

Route::get('test/send/image',function(){
    $ApiStoreg = new NextCloud('dmitry','Ag@19836373!','nc.cooldreamy.com');
    $dir = "/media";



     dd( $ApiStoreg->createFolder($dir));
    //$file = storage_path("/app/public/s-l1600.jpg");
    //$content =  file_get_contents( $file);
    //$ApiStoreg->upoadFile($dir,$content,"test.jpg");
});
Route::get('/test1', [\App\Http\Controllers\PaymentController::class, 'testWebHook']);


Route::get('/test', function (){
   $userList = \App\Models\User\UserCooperation::query()->get()->toArray();
   foreach ($userList as $item){
       $curl = curl_init();
       curl_setopt_array($curl, array(
           CURLOPT_URL => "http://95.179.250.121/e39dfb1/postback?subid={$item['subid']}&status=lead",
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => '',
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 0,
           CURLOPT_FOLLOWLOCATION => true,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => 'GET',
       ));
       $response = curl_exec($curl);
       var_dump("Log User {$item['subid']} Conversion  ". json_encode($response));
       curl_close($curl);
   }
});



