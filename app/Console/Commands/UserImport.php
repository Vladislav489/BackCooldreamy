<?php


namespace App\Console\Commands;

use App\Models\Import\CronImportUser;
use Illuminate\Console\Command;

class UserImport extends Command
{

    protected $signature = 'import:user';

    protected $description = 'run Import User';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        set_time_limit(0);
        CronImportUser::runCron();
    }
}
