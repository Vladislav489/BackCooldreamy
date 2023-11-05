<?php

namespace App\Traits;

use App\Enum\Image\AnketOperatorFileType;
use App\Models\Anket\AnketFile;
use App\Models\Operator\OperatorDelay;
use App\Models\Operator\OperatorWork;
use App\Models\OperatorChatLimit;
use App\Models\OperatorLinkUsers;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait UserOperatorTrait
{

    /**
     * @return HasMany
     */
    public function ancets(): HasMany
    {
        return $this->hasMany(OperatorLinkUsers::class, 'operator_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function adminAncets(): HasMany
    {
        return $this->hasMany(OperatorLinkUsers::class, 'admin_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function operator(): HasOne
    {
        return $this->hasOne(OperatorLinkUsers::class, 'user_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function linkedOperator(): HasOne
    {
        return $this->hasOne(OperatorLinkUsers::class, 'operator_id','id');
    }

    /**
     * @return HasOne
     */
    public function isInWork(): HasOne
    {
        return $this->hasOne(OperatorWork::class, 'operator_id', 'id')->where('is_finished', false);
    }

    /**
     * @return HasMany
     */
    public function delays(): HasMany
    {
        return $this->hasMany(OperatorDelay::class, 'operator_id', 'id');
    }

    public function anketPhotos()
    {
        return $this->hasMany(AnketFile::class, 'anket_id', 'id')->where('type', AnketOperatorFileType::PHOTO);
    }

    public function anketVideos()
    {
        return $this->hasMany(AnketFile::class, 'anket_id', 'id')->where('type', AnketOperatorFileType::VIDEO);
    }

    public function operatorLimits()
    {
        return $this->hasMany(OperatorChatLimit::class, 'girl_id');
    }
}
