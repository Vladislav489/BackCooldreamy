<?php
namespace App\Models\StatisticSite;

use App\Models\User;
use http\Env\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutingUser extends Model {
    use HasFactory;

    protected $fillable = [
        'url',
        'url_from',
        'os',
        'langBr',
        'typeBr',
        'typeDivece',
        'user_id',
        'tag'
    ];

    private $listUTM = ['source','campaign','creative'];


    public function getUTMlist(){
        return $this->listUTM;
    }



    public static function saveUrl($dataUrl,$user_id = null){
        $dataUrl = parse_url($dataUrl['url']);
        $dataSave = $dataUrl;
        if(!is_null($user_id))
            $dataSave['user_id'] = $user_id;

        if(isset($dataUrl['query'])){
            $arratag = [];
            parse_str( parse_url( $dataUrl['url'], PHP_URL_QUERY), $arratag );
            foreach ($arratag as  $key => $item){
                if(in_array($key,(new RoutingUser())->getUTMlist())){
                    $dataSave['tag'][] = $item;
                }
            }
            //host/reg/?tag1=fdf&dsd
            $dataSave['tag'] = json_encode($dataSave['tag']);
        }

        $url = new RoutingUser();
        $url->setRawAttributes($dataSave);
        $url->save();

       return $url->id;
    }
}
