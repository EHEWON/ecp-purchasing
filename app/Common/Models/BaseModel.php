<?php

namespace App\Common\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model {

    protected $lang = 'zh';
    public $timestamps = false;

    public function __construct(array $attributes = array()) {
        $this->lang = app('translator')->getLocale();
        parent::__construct($attributes);
    }

    /**
     * 格式化时间
     * */
    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Perform any actions required before the model boots.
     *
     * @return void
     */
    protected static function booting() {
        //
    }

}
