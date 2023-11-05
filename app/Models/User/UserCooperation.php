<?php
namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class UserCooperation extends Model{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'subid',
        'af_id',
        'app_name',
    ];
}
/*
 *
 * https://usadating.site/NkMBJq?
 * utm_campaign
 * utm_source
 * utm_placement
 * campaign_id
 * adset_id4
 * ad_id
 * adset_name
 * pixel
 * ad_name
 * token
 * domain
 * app_name
 *
 */
