<?php

namespace App\Http\Controllers;
class DeployController extends Controller {
    public function deploy() {
        flush();
        $commands = array(
            'cd /var/www/newapi_soult_usr/data/www/newapi.soultri.site && unset GIT_DIR && git pull origin master && exec git update-server-info && npm run build && php artisan migrate',
        );
        $output = "\n";
        foreach ($commands as $command) {
            $tmp = shell_exec("$command 2>&1");
            $output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
            $output .= htmlentities(trim($tmp)) . "\n";
            $output .= $tmp;
        }
        echo $output;
    }
}
