<?php


namespace App\Console\Commands\Ace;



namespace App\Console\Commands;

use App\ModelAdmin\CoreEngine\LogicModels\Ace\AceCronLogic;
use Illuminate\Console\Command;

class AceSendUser extends Command{
    protected $signature = 'ace:ace-send-user';
    protected $description = 'System send user message auto from fake ancet';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(){
        try {
            logger('ace:ace-send-user');
            set_time_limit(0);
            AceCronLogic::runCronAce();
        }catch (\Throwable $e ){
            logger($e->getMessage());
        }
    }
}

