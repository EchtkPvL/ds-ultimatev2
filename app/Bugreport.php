<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bugreport extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'title',
        'priority',
        'description',
        'url',
        'status',
    ];

    public function comments(){

        return $this->hasMany('App\BugreportComment');

    }

    public function firstSeenUser()
    {
        return $this->hasOne('App\User', 'id', 'firstSeenUser_id');
    }

    /**
     * @return string
     */
    public function getPriority(){
        switch ($this->priority){
            case 0:
                return __('user.bugreport.prioritySelect.low');
            case 1:
                return __('user.bugreport.prioritySelect.normal');
            case 2:
                return __('user.bugreport.prioritySelect.high');
            case 3:
                return __('user.bugreport.prioritySelect.critical');
        }
    }

    /**
     * Erstellt den Badge mit der dazugehörigen Eigenschaften.
     *
     * @return string
     */
    public function getPriorityBadge(){
        switch ($this->priority){
            case 0:
                return '<span class="badge badge-info">'.$this->getPriority().'</span>';
            case 1:
                return '<span class="badge badge-primary">'.$this->getPriority().'</span>';
            case 2:
                return '<span class="badge badge-warning">'.$this->getPriority().'</span>';
            case 3:
                return '<span class="badge badge-danger">'.$this->getPriority().'</span>';
        }
    }

    /**
     * @return string
     */
    public function getStatus(){
        switch ($this->status){
            case 0:
                return __('cruds.bugreport.statusSelect.open');
            case 1:
                return __('cruds.bugreport.statusSelect.inprogress');
            case 2:
                return __('cruds.bugreport.statusSelect.resolved');
            case 3:
                return __('cruds.bugreport.statusSelect.close');
        }
    }

    /**
     * Erstellt den Badge mit der dazugehörigen Eigenschaften.
     *
     * @return string
     */
    public function getStatusBadge(){
        switch ($this->status){
            case 0:
                return '<span class="badge badge-dark">'.$this->getStatus().'</span>';
            case 1:
                return '<span class="badge badge-primary">'.$this->getStatus().'</span>';
            case 2:
                return '<span class="badge badge-light">'.$this->getStatus().'</span>';
            case 3:
                return '<span class="badge badge-success">'.$this->getStatus().'</span>';
        }
    }

    /**
     * @return int
     */
    public static function countNew(){
        return Bugreport::where('firstSeen', null)->get()->count();
    }
}
